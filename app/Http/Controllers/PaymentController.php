<?php

namespace App\Http\Controllers;

use App\Services\BakongApiService;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Support\KhqrPayload;
use App\Support\MediaPath;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

class PaymentController extends Controller
{
    private const DEFAULT_EXPIRY_SECONDS = 900;

    public function __construct(
        private readonly BakongApiService $bakongApi
    ) {
    }

    private function merchantConfig(): array
    {
        $currency = strtoupper((string) config('services.bakong.currency', 'USD'));
        if (!in_array($currency, ['USD', 'KHR'], true)) {
            $currency = 'USD';
        }

        return [
            'account_id' => trim((string) config('services.bakong.account_id', '')),
            'merchant_name' => trim((string) config('services.bakong.merchant_name', '')),
            'merchant_city' => trim((string) config('services.bakong.merchant_city', 'Phnom Penh')),
            'currency' => $currency,
            'currency_code' => $currency === 'KHR'
                ? KHQRData::CURRENCY_KHR
                : KHQRData::CURRENCY_USD,
            'store_label' => $this->optionalKhqrValue(
                config('services.bakong.store_label'),
                'KHQR Shop'
            ),
            'terminal_label' => $this->optionalKhqrValue(
                config('services.bakong.terminal_label'),
                'WEB1'
            ),
            'purpose' => $this->optionalKhqrValue(
                config('services.bakong.purpose'),
                'Order payment'
            ),
            'expiry_seconds' => max(
                60,
                (int) config('services.bakong.expiry_seconds', self::DEFAULT_EXPIRY_SECONDS)
            ),
        ];
    }

    private function optionalKhqrValue(mixed $value, string $default): string
    {
        $value = preg_replace('/\s+/', ' ', trim((string) $value));
        $value = $value !== '' ? $value : $default;

        return mb_substr($value, 0, 25);
    }

    private function buildBillNumber(): string
    {
        return 'ORD-'.now()->format('ymdHisv').'-'.Str::upper(Str::random(2));
    }

    private function generateQr(float $amount, ?string $billNumber = null): array
    {
        $merchant = $this->merchantConfig();
        $amount = $merchant['currency'] === 'KHR'
            ? round($amount)
            : round($amount, 2);
        $billNumber = $billNumber ?: $this->buildBillNumber();

        try {
            if ($merchant['account_id'] === '') {
                throw new \RuntimeException('Bakong account ID is not configured.');
            }

            if ($merchant['merchant_name'] === '') {
                throw new \RuntimeException('Bakong merchant name is not configured.');
            }

            $info = new IndividualInfo(
                bakongAccountID: $merchant['account_id'],
                merchantName: $merchant['merchant_name'],
                merchantCity: $merchant['merchant_city'],
                currency: $merchant['currency_code'],
                amount: $amount,
                billNumber: $billNumber,
                storeLabel: $merchant['store_label'],
                terminalLabel: $merchant['terminal_label'],
                purposeOfTransaction: $merchant['purpose']
            );

            $response = BakongKHQR::generateIndividual($info);
            $qr = $response->data['qr'] ?? null;
            if ($qr) {
                $qr = KhqrPayload::ensureDynamicExpiry($qr, $merchant['expiry_seconds']);
            }
            $md5 = $qr ? md5($qr) : null;

            if (!$qr || !$md5) {
                throw new \RuntimeException('Bakong did not return a QR payload.');
            }

            $verification = BakongKHQR::verify($qr);
            if (!$verification->isValid) {
                throw new \RuntimeException('Generated KHQR failed validation.');
            }

            $decoded = BakongKHQR::decode($qr)->data;
            $decodedAmount = isset($decoded['transactionAmount']) ? (float) $decoded['transactionAmount'] : 0.0;
            $decodedCurrency = (string) ($decoded['transactionCurrency'] ?? '');

            if (
                ($decoded['bakongAccountID'] ?? null) !== $merchant['account_id']
                || abs($decodedAmount - $amount) > 0.00001
                || $decodedCurrency !== (string) $merchant['currency_code']
            ) {
                throw new \RuntimeException('Generated KHQR payload does not match the configured payment details.');
            }

            return [
                'qr' => $qr,
                'md5' => $md5,
                'bill_number' => $billNumber,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('KHQR generation failed', [
                'account_id' => $merchant['account_id'],
                'merchant_name' => $merchant['merchant_name'],
                'merchant_city' => $merchant['merchant_city'],
                'currency' => $merchant['currency'],
                'amount' => $amount,
                'bill_number' => $billNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'qr' => null,
                'md5' => null,
                'bill_number' => $billNumber,
                'error' => $e->getMessage(),
            ];
        }
    }

    private function removePurchasedCartItems(Order $order): void
    {
        $cart = session('cart', []);
        if ($cart === []) {
            return;
        }

        $cartKeys = collect($order->items ?? [])
            ->map(function ($item): ?string {
                if (!is_array($item)) {
                    return null;
                }

                $key = $item['cart_key'] ?? $item['key'] ?? null;

                return is_string($key) && $key !== '' ? $key : null;
            })
            ->filter()
            ->values()
            ->all();

        if ($cartKeys === []) {
            return;
        }

        foreach ($cartKeys as $key) {
            unset($cart[$key]);
        }

        session(['cart' => $cart]);
    }

    public function checkout($id)
    {
        $merchant = $this->merchantConfig();
        Order::expirePending($merchant['expiry_seconds']);
        $product = Product::findOrFail($id);

        $qrPayload = $this->generateQr((float) $product->price);
        $qr = $qrPayload['qr'] ?? null;
        $md5 = $qrPayload['md5'] ?? null;
        $billNumber = $qrPayload['bill_number'] ?? null;
        $qrError = $qrPayload['error'] ?? null;

        $order = null;
        if ($md5) {
            $order = Order::create([
                'product_id'   => $product->id,
                'user_id'      => Auth::id(),
                'product_name' => $product->name,
                'amount'       => $product->price,
                'currency'     => $merchant['currency'],
                'md5'          => $md5,
                'bill_number'  => $billNumber,
                'status'       => 'PENDING',
                'items'        => [
                    [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => (float) $product->price,
                        'image' => MediaPath::normalize($product->getRawOriginal('image') ?: $product->image),
                        'size' => $product->size,
                        'color' => $product->color,
                        'qty' => 1,
                    ],
                ],
            ]);
        }

        return view('products.checkout', [
            'product' => $product,
            'items'   => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'image' => MediaPath::normalize($product->getRawOriginal('image') ?: $product->image),
                    'size' => $product->size,
                    'color' => $product->color,
                    'qty' => 1,
                ],
            ],
            'total'   => (float) $product->price,
            'currency' => $merchant['currency'],
            'qr'      => $qr,
            'md5'     => $md5,
            'billNumber' => $billNumber,
            'qrError' => $qrError ?? null,
            'orderId' => $order?->id,
            'expirySeconds' => $merchant['expiry_seconds'],
        ]);
    }

    public function checkoutCart(Request $request)
    {
        $merchant = $this->merchantConfig();
        Order::expirePending($merchant['expiry_seconds']);
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $selected = array_filter((array) $request->input('selected', []));
        if ($request->has('selected') && empty($selected)) {
            return redirect()->route('cart.index')->with('error', 'Please select at least one item.');
        }

        if (!empty($selected)) {
            $selectedKeys = array_flip($selected);
            $cart = array_intersect_key($cart, $selectedKeys);
            if (empty($cart)) {
                return redirect()->route('cart.index')->with('error', 'Selected items are no longer in your cart.');
            }
        }

        $items = array_values(array_map(function (array $item): array {
            $item['image'] = MediaPath::normalize($item['image'] ?? null);
            $item['color_image'] = MediaPath::normalize($item['color_image'] ?? null);
            $item['cart_key'] = $item['key'] ?? null;

            return $item;
        }, $cart));
        $total = collect($items)->sum(fn ($item) => $item['price'] * $item['qty']);

        $qrPayload = $this->generateQr((float) $total);
        $qr = $qrPayload['qr'] ?? null;
        $md5 = $qrPayload['md5'] ?? null;
        $billNumber = $qrPayload['bill_number'] ?? null;
        $qrError = $qrPayload['error'] ?? null;

        $order = null;
        if ($md5) {
            $first = $items[0];
            $order = Order::create([
                'user_id'      => Auth::id(),
                'product_id'   => $first['id'],
                'product_name' => Order::summarizeItems($items, $first['name'] ?? null),
                'amount'       => $total,
                'currency'     => $merchant['currency'],
                'md5'          => $md5,
                'bill_number'  => $billNumber,
                'status'       => 'PENDING',
                'items'        => $items,
            ]);
        }

        return view('products.checkout', [
            'items'   => $items,
            'total'   => $total,
            'currency' => $merchant['currency'],
            'qr'      => $qr,
            'md5'     => $md5,
            'billNumber' => $billNumber,
            'qrError' => $qrError ?? null,
            'orderId' => $order?->id,
            'expirySeconds' => $merchant['expiry_seconds'],
        ]);
    }

    public function verifyTransaction(Request $request)
    {
        $request->validate([
            'md5' => 'required|string',
        ]);

        try {
            Order::expirePending($this->merchantConfig()['expiry_seconds']);
            $result = $this->bakongApi->checkTransactionByMD5($request->md5);

            if (($result['responseCode'] ?? null) === 0) {
                $order = Order::where('md5', $request->md5)->first();

                if ($order && $order->status !== 'PAID') {
                    $order->status = 'PAID';
                    $order->paid_at = Carbon::now();
                    $order->save();
                    $this->removePurchasedCartItems($order);
                    $this->sendTelegramPaidInvoice($order);
                }

                $result['order_id'] = $order?->id;
            }

            return response()->json($result);

        } catch (\Exception $e) {
            $status = str_contains(strtolower($e->getMessage()), 'unable to reach the bakong api')
                ? 503
                : 500;

            return response()->json([
                'error' => $e->getMessage(),
                'responseCode' => 1,
            ], $status);
        }
    }

    public function invoice(Order $order)
    {
        abort_if($order->status !== 'PAID', 403, 'Invoice available after payment only.');
        return view('products.invoice', compact('order'));
    }

    private function sendTelegramPaidInvoice(Order $order): void
    {
        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');

        if (!$token || !$chatId) {
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

            Http::timeout(8)->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => implode("\n", $lines),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram invoice send failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
