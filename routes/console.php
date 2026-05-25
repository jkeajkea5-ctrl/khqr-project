<?php

use App\Models\Order;
use App\Services\BakongApiService;
use App\Support\KhqrPayload;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('bakong:health {--amount=1.00}', function () {
    try {
        $accountId = trim((string) config('services.bakong.account_id', ''));
        $merchantName = trim((string) config('services.bakong.merchant_name', ''));
        $merchantCity = trim((string) config('services.bakong.merchant_city', 'Phnom Penh'));
        $currency = strtoupper((string) config('services.bakong.currency', 'USD'));
        $storeLabel = mb_substr(trim((string) config('services.bakong.store_label', 'KHQR Shop')), 0, 25);
        $terminalLabel = mb_substr(trim((string) config('services.bakong.terminal_label', 'WEB1')), 0, 25);
        $purpose = mb_substr(trim((string) config('services.bakong.purpose', 'Order payment')), 0, 25);
        $token = trim((string) config('services.bakong.token', ''));
        $amount = (float) $this->option('amount');

        if ($accountId === '' || $merchantName === '') {
            $this->error('Bakong account ID and merchant name are required.');

            return 1;
        }

        $this->info('Checking Bakong account...');
        $account = app(BakongApiService::class)->checkBakongAccount($accountId);
        if (($account['responseCode'] ?? null) !== 0) {
            $this->error((string) ($account['responseMessage'] ?? 'Bakong account check failed.'));

            return 1;
        }

        $currencyCode = $currency === 'KHR'
            ? KHQRData::CURRENCY_KHR
            : KHQRData::CURRENCY_USD;
        $billNumber = 'HEALTH-'.now()->format('ymdHisv');
        $qrResponse = BakongKHQR::generateIndividual(new IndividualInfo(
            bakongAccountID: $accountId,
            merchantName: $merchantName,
            merchantCity: $merchantCity,
            currency: $currencyCode,
            amount: $currencyCode === KHQRData::CURRENCY_KHR ? round($amount) : round($amount, 2),
            billNumber: $billNumber,
            storeLabel: $storeLabel !== '' ? $storeLabel : 'KHQR Shop',
            terminalLabel: $terminalLabel !== '' ? $terminalLabel : 'WEB1',
            purposeOfTransaction: $purpose !== '' ? $purpose : 'Order payment'
        ));

        $qr = $qrResponse->data['qr'] ?? null;
        if ($qr) {
            $qr = KhqrPayload::ensureDynamicExpiry(
                $qr,
                (int) config('services.bakong.expiry_seconds', 900)
            );
        }
        $md5 = $qr ? md5($qr) : null;
        if (!$qr || !$md5) {
            $this->error('Bakong KHQR generation failed.');

            return 1;
        }

        $verification = BakongKHQR::verify($qr);
        if (!$verification->isValid) {
            $this->error('Generated KHQR did not pass local validation.');

            return 1;
        }

        $decoded = BakongKHQR::decode($qr)->data;
        $this->info('Bakong account is reachable and KHQR generation is valid.');
        $this->line('Merchant: '.$merchantName.' <'.$accountId.'>');
        $this->line('Currency: '.$currency);
        $this->line('Bill number: '.$billNumber);
        $this->line('QR MD5: '.$md5);
        $this->line('Decoded amount: '.($decoded['transactionAmount'] ?? 'n/a'));

        if ($token === '') {
            $this->warn('BAKONG_TOKEN is not set. Transaction-status checks are disabled.');

            return 0;
        }

        $transaction = app(BakongApiService::class)->checkTransactionByMD5($md5);
        $this->line('Transaction API response: '.($transaction['responseMessage'] ?? 'n/a'));

        return 0;
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return 1;
    }
})->purpose('Validate Bakong account, KHQR generation, and transaction-status access');

Artisan::command('orders:sync', function () {
    $token = config('services.bakong.token');
    if (!$token) {
        $this->warn('BAKONG_TOKEN is not set.');
        return 1;
    }

    $bakong = app(BakongApiService::class);
    $pending = Order::where('status', 'PENDING')->get();

    $sendTelegram = function (Order $order): void {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!$botToken || !$chatId) {
            return;
        }

        try {
            $order->loadMissing('user');
            $user = $order->user;
            $items = $order->items ?? [];

            $lines = [
                'PAID INVOICE',
                'Order ID: ' . $order->id,
                'Reference: ' . ($order->bill_number ?: 'N/A'),
                'Status: PAID',
            ];

            if ($user) {
                $lines[] = 'Name: ' . ($user->name ?? 'N/A');
                $lines[] = 'Phone: ' . ($user->phone ?? 'N/A');
                $lines[] = 'User ID: ' . $user->id;
            }

            if (!empty($items)) {
                $lines[] = 'Items:';
                foreach ($items as $item) {
                    $lines[] = '- ID: ' . ($item['id'] ?? 'N/A')
                        . ' | Name: ' . ($item['name'] ?? 'N/A')
                        . ' | Size: ' . ($item['size'] ?? 'N/A')
                        . ' | Color: ' . ($item['color'] ?? 'N/A')
                        . ' | Qty: ' . ($item['qty'] ?? 1);
                }
            } else {
                $lines[] = 'Item Name: ' . $order->display_product_name;
            }

            $lines[] = 'Amount: ' . number_format((float) $order->amount, 2) . ' ' . ($order->currency ?? 'USD');
            $lines[] = 'Invoice: ' . url('/invoice/' . $order->id);

            Http::timeout(8)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => implode("\n", $lines),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram invoice send failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    };

    foreach ($pending as $order) {
        if (empty($order->md5)) {
            continue;
        }

        try {
            $result = $bakong->checkTransactionByMD5($order->md5);
            if (($result['responseCode'] ?? null) === 0 && $order->status !== 'PAID') {
                $order->status = 'PAID';
                $order->paid_at = Carbon::now();
                $order->save();

                $sendTelegram($order);
            }
        } catch (\Throwable $e) {
            Log::warning('orders:sync failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    $expired = Order::expirePending((int) config('services.bakong.expiry_seconds', 900));
    $this->info("orders:sync done. expired={$expired}");
})->purpose('Sync pending orders with Bakong and expire old ones');

Schedule::command('orders:sync')->everyMinute();
