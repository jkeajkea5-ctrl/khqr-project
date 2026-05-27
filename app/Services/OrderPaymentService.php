<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class OrderPaymentService
{
    public function markAsPaid(Order $order, ?CarbonInterface $paidAt = null, ?string $resolvedBy = null): bool
    {
        $changed = false;
        $paidAt = $paidAt ?? now();

        if ($order->status !== 'PAID') {
            $order->status = 'PAID';
            $changed = true;
        }

        if (!$order->paid_at) {
            $order->paid_at = $paidAt;
            $changed = true;
        }

        if (($order->manual_review_status ?? null) === 'requested') {
            $order->manual_review_status = 'approved';
            $order->manual_review_resolved_at = $paidAt;
            $order->manual_review_resolved_by = $resolvedBy;
            $changed = true;
        }

        if ($changed) {
            $order->save();
        }

        return $changed;
    }

    public function markAsFailed(Order $order, ?string $resolvedBy = null, ?string $reason = null): bool
    {
        $changed = false;

        if ($order->status !== 'FAILED') {
            $order->status = 'FAILED';
            $changed = true;
        }

        $order->manual_review_status = 'rejected';
        $order->manual_review_resolved_at = now();
        $order->manual_review_resolved_by = $resolvedBy;
        $order->manual_review_resolution_note = $this->normalizeNote($reason);

        if ($order->isDirty([
            'manual_review_status',
            'manual_review_resolved_at',
            'manual_review_resolved_by',
            'manual_review_resolution_note',
        ])) {
            $changed = true;
        }

        if ($changed) {
            $order->save();
        }

        return $changed;
    }

    public function sendTelegramPaidInvoice(Order $order): void
    {
        [$token, $chatId] = $this->telegramCredentials();

        if (!$token || !$chatId) {
            return;
        }

        try {
            $order->loadMissing('user');
            $user = $order->user;
            $items = $order->items ?? [];

            $lines = [
                'PAID INVOICE',
                'Order ID: '.$order->id,
                'Reference: '.($order->bill_number ?: 'N/A'),
                'Status: PAID',
            ];

            if ($user) {
                $lines[] = 'Name: '.($user->name ?? 'N/A');
                $lines[] = 'Phone: '.($user->phone ?? 'N/A');
                $lines[] = 'User ID: '.$user->id;
            }

            if (!empty($items)) {
                $lines[] = 'Items:';
                foreach ($items as $item) {
                    $lines[] = '- ID: '.($item['id'] ?? 'N/A')
                        .' | Name: '.($item['name'] ?? 'N/A')
                        .' | Size: '.($item['size'] ?? 'N/A')
                        .' | Color: '.($item['color'] ?? 'N/A')
                        .' | Qty: '.($item['qty'] ?? 1);
                }
            } else {
                $lines[] = 'Item Name: '.$order->display_product_name;
            }

            $lines[] = 'Amount: '.$this->formatAmount($order);
            $lines[] = 'Invoice: '.$this->invoiceUrl($order);

            $this->sendTelegramMessage($token, $chatId, implode("\n", $lines), 'Telegram invoice send failed', [
                'order_id' => $order->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram invoice send failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function telegramCredentials(): array
    {
        return [
            config('services.telegram.bot_token'),
            config('services.telegram.chat_id'),
        ];
    }

    private function sendTelegramMessage(
        string $token,
        string $chatId,
        string $message,
        string $logMessage,
        array $context = []
    ): void {
        try {
            Http::timeout(8)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::warning($logMessage, $context + [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function normalizeNote(?string $note): ?string
    {
        $normalized = preg_replace('/\s+/', ' ', trim((string) $note));

        if (!is_string($normalized) || $normalized === '') {
            return null;
        }

        return mb_substr($normalized, 0, 500);
    }

    private function invoiceUrl(Order $order): string
    {
        return URL::temporarySignedRoute('invoice', now()->addDays(30), [
            'order' => $order,
        ]);
    }

    private function formatAmount(Order $order): string
    {
        $currency = strtoupper((string) ($order->currency ?? 'USD'));
        $decimals = $currency === 'KHR' ? 0 : 2;

        return number_format((float) $order->amount, $decimals).' '.$currency;
    }
}
