<?php

namespace App\Http\Controllers;

use App\Services\BakongApiService;
use App\Services\OrderPaymentService;
use App\Models\Product;
use App\Models\Order;
use App\Support\KhqrPayload;
use App\Support\MediaPath;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

class PaymentController extends Controller
{
    private const DEFAULT_EXPIRY_SECONDS = 900;

    public function __construct(
        private readonly BakongApiService $bakongApi,
        private readonly OrderPaymentService $orderPayments,
    ) {
    }

    private function merchantConfig(): array
    {
        $currency = strtoupper((string) config('services.bakong.currency', 'USD'));
        if (!in_array($currency, ['USD', 'KHR'], true)) {
            $currency = 'USD';
        }

        return [
            'payment_provider' => strtolower(trim((string) config('services.bakong.payment_provider', 'bakong'))),
            'token' => trim((string) config('services.bakong.token', '')),
            'account_id' => trim((string) config('services.bakong.account_id', '')),
            'merchant_name' => trim((string) config('services.bakong.merchant_name', '')),
            'merchant_city' => trim((string) config('services.bakong.merchant_city', 'Phnom Penh')),
            'khqr_link_api_url' => rtrim(
                trim((string) config('services.bakong.khqr_link_api_url', 'https://api.khqr.link')),
                '/'
            ),
            'khqr_link_api_key' => trim((string) config('services.bakong.khqr_link_api_key', '')),
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

    private function buildExpirationTimestamp(int $expirySeconds): string
    {
        $creationTimestamp = (int) floor(microtime(true) * 1000);

        return (string) ($creationTimestamp + (max(60, $expirySeconds) * 1000));
    }

    private function generateQr(float $amount, ?string $billNumber = null): array
    {
        $merchant = $this->merchantConfig();
        $amount = $merchant['currency'] === 'KHR'
            ? round($amount)
            : round($amount, 2);
        $billNumber = $billNumber ?: $this->buildBillNumber();

        if (($merchant['payment_provider'] ?? 'bakong') === 'khqr_link') {
            return $this->generateKhqrLinkQr($merchant, $amount, $billNumber);
        }

        return $this->generateBakongQr($merchant, $amount, $billNumber);
    }

    private function generateBakongQr(array $merchant, float $amount, string $billNumber): array
    {
        $expirationTimestamp = $this->buildExpirationTimestamp($merchant['expiry_seconds']);

        try {
            if ($merchant['token'] === '') {
                throw new \RuntimeException('Bakong token is not configured for this deployment.');
            }

            if ($merchant['account_id'] === '') {
                throw new \RuntimeException('Bakong account ID is not configured.');
            }

            if ($merchant['merchant_name'] === '') {
                throw new \RuntimeException('Bakong merchant name is not configured.');
            }

            // Keep the KHQR payload minimal. Bakong mobile apps are stricter than
            // the SDK verifier about optional additional-data tags.
            $info = new IndividualInfo(
                $merchant['account_id'],
                $merchant['merchant_name'],
                $merchant['merchant_city'],
                null,
                null,
                $merchant['currency_code'],
                $amount
            );

            // Set the expiry after construction so checkout still works if the
            // deployed SDK build does not expose this constructor parameter name.
            if (property_exists($info, 'expirationTimestamp')) {
                $info->expirationTimestamp = $expirationTimestamp;
            }

            $response = BakongKHQR::generateIndividual($info);
            $qr = $response->data['qr'] ?? null;
            if (
                $qr
                && $merchant['currency_code'] === KHQRData::CURRENCY_USD
                && method_exists(KhqrPayload::class, 'normalizeUsdAmount')
            ) {
                $qr = KhqrPayload::normalizeUsdAmount($qr, $amount);
            }
            $md5 = $response->data['md5'] ?? ($qr ? md5($qr) : null);
            if ($qr) {
                $md5 = md5($qr);
            }

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
                'qr_image_url' => null,
                'md5' => $md5,
                'bill_number' => $billNumber,
                'expiry_seconds' => $merchant['expiry_seconds'],
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
                'qr_image_url' => null,
                'md5' => null,
                'bill_number' => $billNumber,
                'expiry_seconds' => $merchant['expiry_seconds'],
                'error' => $e->getMessage(),
            ];
        }
    }

    private function generateKhqrLinkQr(array $merchant, float $amount, string $billNumber): array
    {
        try {
            if ($merchant['account_id'] === '') {
                throw new \RuntimeException('Bakong account ID is not configured.');
            }

            if ($merchant['merchant_name'] === '') {
                throw new \RuntimeException('Bakong merchant name is not configured.');
            }

            if ($merchant['khqr_link_api_url'] === '') {
                throw new \RuntimeException('KHQR Link API URL is not configured.');
            }

            $requestPayload = [
                'amount' => $merchant['currency'] === 'KHR'
                    ? (string) round($amount)
                    : number_format($amount, 2, '.', ''),
                'bakongid' => $merchant['account_id'],
                'merchantname' => $merchant['merchant_name'],
            ];

            $request = Http::acceptJson()
                ->timeout(20)
                ->connectTimeout(10);

            if ($merchant['khqr_link_api_key'] !== '') {
                $requestPayload['apikey'] = $merchant['khqr_link_api_key'];
                $request = $request->withHeaders([
                    'X-API-Key' => $merchant['khqr_link_api_key'],
                ]);
            }

            $response = $request->get($merchant['khqr_link_api_url'].'/v1/khqr/create', $requestPayload);

            $payload = $response->json();

            if (!is_array($payload)) {
                throw new \RuntimeException('KHQR Link returned an unexpected response.');
            }

            if (!$response->successful() || strtolower(trim((string) ($payload['status'] ?? ''))) !== 'success') {
                $message = trim((string) ($payload['message'] ?? $payload['error'] ?? 'KHQR Link could not create a payment.'));

                throw new \RuntimeException($message !== '' ? $message : 'KHQR Link could not create a payment.');
            }

            $md5 = trim((string) ($payload['md5'] ?? ''));
            $qrImageUrl = $this->normalizeKhqrLinkQrUrl($payload['qr'] ?? null);

            if ($md5 === '' || $qrImageUrl === null) {
                throw new \RuntimeException('KHQR Link did not return a usable QR payload.');
            }

            return [
                'qr' => null,
                'qr_image_url' => $qrImageUrl,
                'md5' => $md5,
                'bill_number' => $billNumber,
                'expiry_seconds' => $this->resolveRemoteExpirySeconds($payload, $merchant['expiry_seconds']),
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::error('KHQR Link generation failed', [
                'account_id' => $merchant['account_id'],
                'merchant_name' => $merchant['merchant_name'],
                'currency' => $merchant['currency'],
                'amount' => $amount,
                'bill_number' => $billNumber,
                'error' => $e->getMessage(),
            ]);

            return [
                'qr' => null,
                'qr_image_url' => null,
                'md5' => null,
                'bill_number' => $billNumber,
                'expiry_seconds' => $merchant['expiry_seconds'],
                'error' => $e->getMessage(),
            ];
        }
    }

    private function normalizeKhqrLinkQrUrl(mixed $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        return preg_replace('/^http:\/\//i', 'https://', $url);
    }

    private function resolveRemoteExpirySeconds(array $payload, int $fallback): int
    {
        $expiresAt = trim((string) ($payload['expires_at'] ?? ''));

        if ($expiresAt === '') {
            return max(60, $fallback);
        }

        try {
            $seconds = now()->diffInSeconds(Carbon::parse($expiresAt), false);

            return max(60, (int) $seconds);
        } catch (\Throwable) {
            return max(60, $fallback);
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
        $qrImageUrl = $qrPayload['qr_image_url'] ?? null;
        $md5 = $qrPayload['md5'] ?? null;
        $billNumber = $qrPayload['bill_number'] ?? null;
        $qrError = $qrPayload['error'] ?? null;
        $expirySeconds = max(60, (int) ($qrPayload['expiry_seconds'] ?? $merchant['expiry_seconds']));

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
            'qrImageUrl' => $qrImageUrl,
            'md5'     => $md5,
            'billNumber' => $billNumber,
            'qrError' => $qrError ?? null,
            'orderId' => $order?->id,
            'expirySeconds' => $expirySeconds,
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
        $qrImageUrl = $qrPayload['qr_image_url'] ?? null;
        $md5 = $qrPayload['md5'] ?? null;
        $billNumber = $qrPayload['bill_number'] ?? null;
        $qrError = $qrPayload['error'] ?? null;
        $expirySeconds = max(60, (int) ($qrPayload['expiry_seconds'] ?? $merchant['expiry_seconds']));

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
            'qrImageUrl' => $qrImageUrl,
            'md5'     => $md5,
            'billNumber' => $billNumber,
            'qrError' => $qrError ?? null,
            'orderId' => $order?->id,
            'expirySeconds' => $expirySeconds,
        ]);
    }

    public function verifyTransaction(Request $request)
    {
        $request->validate([
            'md5' => 'required|string',
        ]);

        try {
            $md5 = (string) $request->input('md5');
            Order::expirePending($this->merchantConfig()['expiry_seconds']);
            $result = $this->bakongApi->checkTransactionByMD5($md5);

            if (($result['responseCode'] ?? null) === 0) {
                $order = Order::where('md5', $md5)->first();

                Log::info('Bakong verify success', [
                    'md5' => $md5,
                    'order_id' => $order?->id,
                    'response_message' => $result['responseMessage'] ?? null,
                ]);

                if ($order && $order->status !== 'PAID') {
                    $this->orderPayments->markAsPaid($order, Carbon::now());
                    $this->removePurchasedCartItems($order);
                    $this->sendTelegramPaidInvoice($order);
                }

                $result['order_id'] = $order?->id;
                if ($order) {
                    $result['invoice_url'] = URL::temporarySignedRoute('invoice', now()->addDays(30), [
                        'order' => $order,
                    ]);
                }
            }

            return response()->json($result);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $normalizedMessage = strtolower($message);
            Log::warning('Bakong verify failed', [
                'md5' => $request->input('md5'),
                'error' => $message,
            ]);
            $verificationUnavailable = str_contains($normalizedMessage, 'unable to reach the bakong api')
                || str_contains($normalizedMessage, 'unable to reach the bakong verify proxy')
                || str_contains($normalizedMessage, 'unable to reach the khqr link api')
                || str_contains($normalizedMessage, 'not configured')
                || str_contains($normalizedMessage, 'bakong api returned http 403')
                || str_contains($normalizedMessage, 'bakong verify proxy')
                || str_contains($normalizedMessage, 'khqr link')
                || str_contains($normalizedMessage, 'cloudfront')
                || str_contains($normalizedMessage, 'request blocked')
                || str_contains($normalizedMessage, 'request could not be satisfied');

            return response()->json([
                'error' => $message,
                'responseCode' => 1,
                'verificationUnavailable' => $verificationUnavailable,
            ]);
        }
    }

    public function invoice(Request $request, Order $order)
    {
        abort_if($order->status !== 'PAID', 403, 'Invoice available after payment only.');

        $isOwner = Auth::check() && (string) Auth::id() === (string) $order->user_id;
        $isAdmin = Auth::guard('admin')->check();
        $hasValidSignature = $request->hasValidSignature();

        abort_unless(
            $isOwner || $isAdmin || $hasValidSignature,
            403,
            'You are not allowed to view this invoice.'
        );

        $order->loadMissing('user');

        return response()
            ->view('products.invoice', compact('order'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    private function sendTelegramPaidInvoice(Order $order): void
    {
        $this->orderPayments->sendTelegramPaidInvoice($order);
    }
}
