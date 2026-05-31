<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderPaymentService;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderAdminController extends Controller
{
    public function __construct(
        private readonly OrderPaymentService $orderPayments
    ) {
    }

    public function dashboard()
    {
        $this->expirePendingOrders();
        $totalProducts = Product::count();
        $totalOrders   = Order::count();
        $paidOrders    = Order::where('status', 'PAID')->count();
        $pendingOrders = Order::where('status', 'PENDING')->count();
        $failedOrders  = Order::where('status', 'FAILED')->count();
        $totalAdmins   = Admin::count();
        $totalUsers    = User::count();

        $paidRevenue = (float) Order::where('status', 'PAID')->sum('amount');
        $latestPaid = Order::where('status', 'PAID')->latest('paid_at')->take(10)->get();
        $latestPaid->each(function (Order $order): void {
            $order->setAttribute(
                'formatted_amount',
                $this->formatAmountByCurrency((float) $order->amount, $order->currency),
            );
        });
        $paidOrderData = Order::where('status', 'PAID')
            ->orderBy('paid_at')
            ->get([
                'id',
                'user_id',
                'product_id',
                'product_name',
                'amount',
                'currency',
                'paid_at',
                'created_at',
                'items',
            ]);

        try {
            $analytics = $this->buildSalesAnalytics(
                $paidOrderData,
                $totalOrders,
                $paidOrders,
                $pendingOrders,
                $failedOrders,
                $paidRevenue,
            );
        } catch (\Throwable $exception) {
            Log::warning('Admin dashboard analytics failed; falling back to summary-only mode.', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            $analytics = $this->emptyAnalytics(
                $totalOrders,
                $paidOrders,
                $pendingOrders,
                $failedOrders,
                $paidRevenue,
            );
        }

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'paidOrders',
            'pendingOrders',
            'failedOrders',
            'totalAdmins',
            'totalUsers',
            'paidRevenue',
            'latestPaid',
            'analytics',
        ));
    }

    public function index()
    {
        $this->expirePendingOrders();
        $orders = Order::with('user')->latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function paid()
    {
        $this->expirePendingOrders();
        $orders = Order::where('status', 'PAID')->latest('paid_at')->paginate(15);
        return view('admin.payments.paid', compact('orders'));
    }

    public function markPaid(Order $order)
    {
        if ($order->status === 'PAID') {
            return redirect()
                ->route('admin.orders.index')
                ->with('success', 'Order #'.$order->id.' is already marked as paid.');
        }

        $changed = $this->orderPayments->markAsPaid(
            $order,
            now(),
            (string) Auth::guard('admin')->id(),
        );

        if ($changed) {
            $this->orderPayments->sendTelegramPaidInvoice($order);
        }

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order #'.$order->id.' was marked as paid.');
    }

    public function markFailed(Order $order)
    {
        if ($order->status === 'PAID') {
            return redirect()
                ->route('admin.orders.index')
                ->with('error', 'Paid orders cannot be marked as failed.');
        }

        $this->orderPayments->markAsFailed(
            $order,
            (string) Auth::guard('admin')->id(),
            'Marked as failed by admin review.',
        );

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order #'.$order->id.' was marked as failed.');
    }

    public function destroy(Order $order)
    {
        if ($order->status === 'PAID') {
            return redirect()
                ->route('admin.orders.index')
                ->with('error', 'Paid orders cannot be deleted.');
        }

        $orderId = $order->id;
        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Order #'.$orderId.' was deleted.');
    }

    private function buildSalesAnalytics(
        Collection $paidOrdersData,
        int $totalOrders,
        int $paidOrders,
        int $pendingOrders,
        int $failedOrders,
        float $paidRevenue,
    ): array {
        $trendDays = 14;
        $today = now();
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        $yesterdayStart = $today->copy()->subDay()->startOfDay();
        $yesterdayEnd = $today->copy()->subDay()->endOfDay();
        $thisWeekStart = $today->copy()->startOfWeek();
        $thisWeekEnd = $today->copy()->endOfWeek();
        $previousWeekStart = $thisWeekStart->copy()->subWeek();
        $previousWeekEnd = $thisWeekStart->copy()->subSecond();
        $last7Start = $today->copy()->subDays(6)->startOfDay();
        $previous7Start = $last7Start->copy()->subDays(7);
        $previous7End = $last7Start->copy()->subSecond();
        $trendStart = $today->copy()->subDays($trendDays - 1)->startOfDay();

        $trendBuckets = [];
        for ($date = $trendStart->copy(); $date->lte($todayStart); $date->addDay()) {
            $trendBuckets[$date->toDateString()] = [
                'date' => $date->toDateString(),
                'label' => $date->format('d M'),
                'revenue' => 0.0,
                'units' => 0,
                'orders' => 0,
            ];
        }

        $allTimeRevenueByDay = [];
        $productStats = [];
        $customerOrders = [];
        $totalUnitsSold = 0;

        $todayStats = ['revenue' => 0.0, 'units' => 0, 'orders' => 0];
        $yesterdayStats = ['revenue' => 0.0, 'units' => 0, 'orders' => 0];
        $thisWeekStats = ['revenue' => 0.0, 'units' => 0, 'orders' => 0];
        $previousWeekStats = ['revenue' => 0.0, 'units' => 0, 'orders' => 0];
        $last7Stats = ['revenue' => 0.0, 'units' => 0, 'orders' => 0];
        $previous7Stats = ['revenue' => 0.0, 'units' => 0, 'orders' => 0];

        foreach ($paidOrdersData as $order) {
            try {
                $paidAt = $this->resolveOrderDate($order);
                $dayKey = $paidAt->toDateString();
                $orderRevenue = (float) $order->amount;
                $orderUnits = 0;
                $items = is_array($order->items) ? $order->items : [];

                $userKey = $this->normalizeIdentifier($order->user_id);
                if ($userKey !== null) {
                    $customerOrders[$userKey] = ($customerOrders[$userKey] ?? 0) + 1;
                }

                if ($items !== []) {
                    foreach ($items as $item) {
                        if (!is_array($item)) {
                            continue;
                        }

                        $quantity = $this->normalizeQuantity($item['qty'] ?? 1);
                        $orderUnits += $quantity;

                        $productKey = $this->buildProductKey(
                            $item['id'] ?? null,
                            $item['name'] ?? $order->product_name,
                            $order->id,
                        );

                        if (!isset($productStats[$productKey])) {
                            $productStats[$productKey] = [
                                'name' => $this->normalizeProductName($item['name'] ?? $order->product_name),
                                'units_sold' => 0,
                                'revenue' => 0.0,
                            ];
                        }

                        $productStats[$productKey]['units_sold'] += $quantity;
                        $productStats[$productKey]['revenue'] += (float) ($item['price'] ?? 0) * $quantity;
                    }
                } else {
                    $orderUnits = 1;

                    $productKey = $this->buildProductKey(
                        $order->product_id,
                        $order->product_name,
                        $order->id,
                    );

                    if (!isset($productStats[$productKey])) {
                        $productStats[$productKey] = [
                            'name' => $this->normalizeProductName($order->product_name),
                            'units_sold' => 0,
                            'revenue' => 0.0,
                        ];
                    }

                    $productStats[$productKey]['units_sold'] += 1;
                    $productStats[$productKey]['revenue'] += $orderRevenue;
                }

                $totalUnitsSold += $orderUnits;
                $allTimeRevenueByDay[$dayKey] = ($allTimeRevenueByDay[$dayKey] ?? 0) + $orderRevenue;

                if (isset($trendBuckets[$dayKey])) {
                    $trendBuckets[$dayKey]['revenue'] += $orderRevenue;
                    $trendBuckets[$dayKey]['units'] += $orderUnits;
                    $trendBuckets[$dayKey]['orders'] += 1;
                }

                if ($paidAt->between($todayStart, $todayEnd, true)) {
                    $this->accumulateWindowStats($todayStats, $orderRevenue, $orderUnits);
                }

                if ($paidAt->between($yesterdayStart, $yesterdayEnd, true)) {
                    $this->accumulateWindowStats($yesterdayStats, $orderRevenue, $orderUnits);
                }

                if ($paidAt->between($thisWeekStart, $thisWeekEnd, true)) {
                    $this->accumulateWindowStats($thisWeekStats, $orderRevenue, $orderUnits);
                }

                if ($paidAt->between($previousWeekStart, $previousWeekEnd, true)) {
                    $this->accumulateWindowStats($previousWeekStats, $orderRevenue, $orderUnits);
                }

                if ($paidAt->greaterThanOrEqualTo($last7Start)) {
                    $this->accumulateWindowStats($last7Stats, $orderRevenue, $orderUnits);
                }

                if ($paidAt->between($previous7Start, $previous7End, true)) {
                    $this->accumulateWindowStats($previous7Stats, $orderRevenue, $orderUnits);
                }
            } catch (\Throwable $exception) {
                Log::warning('Skipping malformed paid order during dashboard analytics.', [
                    'order_id' => $order->id ?? null,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $topProducts = collect($productStats)
            ->sort(function (array $left, array $right): int {
                return [$right['units_sold'], $right['revenue']] <=> [$left['units_sold'], $left['revenue']];
            })
            ->take(5)
            ->values()
            ->map(function (array $product): array {
                $product['revenue'] = round($product['revenue'], 2);

                return $product;
            });

        $bestSalesDay = collect($allTimeRevenueByDay)
            ->map(fn (float $revenue, string $date): array => [
                'date' => $date,
                'label' => Carbon::parse($date)->format('d M Y'),
                'revenue' => round($revenue, 2),
            ])
            ->sortByDesc('revenue')
            ->first();

        $reportCurrency = $paidOrdersData->pluck('currency')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first() ?: strtoupper((string) config('services.bakong.currency', 'USD'));

        $trendCollection = collect($trendBuckets)->values();

        return [
            'currency' => $reportCurrency,
            'trend_days' => $trendDays,
            'total_units_sold' => $totalUnitsSold,
            'average_order_value' => $paidOrders > 0 ? round($paidRevenue / $paidOrders, 2) : 0.0,
            'average_units_per_order' => $paidOrders > 0 ? round($totalUnitsSold / $paidOrders, 1) : 0.0,
            'payment_rate' => $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 1) : 0.0,
            'paid_customers' => count($customerOrders),
            'repeat_customers' => collect($customerOrders)->filter(fn (int $count): bool => $count > 1)->count(),
            'today' => $todayStats,
            'this_week' => $thisWeekStats,
            'best_sales_day' => $bestSalesDay,
            'revenue_change' => $this->calculateChange($last7Stats['revenue'], $previous7Stats['revenue']),
            'units_change' => $this->calculateChange($last7Stats['units'], $previous7Stats['units']),
            'today_revenue_change' => $this->calculateChange($todayStats['revenue'], $yesterdayStats['revenue']),
            'today_units_change' => $this->calculateChange($todayStats['units'], $yesterdayStats['units']),
            'week_revenue_change' => $this->calculateChange($thisWeekStats['revenue'], $previousWeekStats['revenue']),
            'week_units_change' => $this->calculateChange($thisWeekStats['units'], $previousWeekStats['units']),
            'trend_chart' => [
                'labels' => $trendCollection->pluck('label')->all(),
                'revenue' => $trendCollection->pluck('revenue')->map(fn (float $value): float => round($value, 2))->all(),
                'units' => $trendCollection->pluck('units')->all(),
                'orders' => $trendCollection->pluck('orders')->all(),
            ],
            'status_chart' => [
                'labels' => ['Paid', 'Pending', 'Failed'],
                'data' => [$paidOrders, $pendingOrders, $failedOrders],
            ],
            'top_products' => $topProducts->all(),
        ];
    }

    private function resolveOrderDate(Order $order): Carbon
    {
        try {
            $date = $order->paid_at ?? $order->created_at ?? now();

            return $date instanceof Carbon ? $date : Carbon::parse($date);
        } catch (\Throwable) {
            $fallback = $order->created_at ?? now();

            try {
                return $fallback instanceof Carbon ? $fallback : Carbon::parse($fallback);
            } catch (\Throwable) {
                return now();
            }
        }
    }

    private function normalizeQuantity(mixed $quantity): int
    {
        $value = (int) $quantity;

        return $value > 0 ? $value : 1;
    }

    private function normalizeProductName(?string $name): string
    {
        $name = trim((string) $name);

        return $name !== '' ? $name : 'Unnamed product';
    }

    private function buildProductKey(mixed $productId, mixed $productName, int $orderId): string
    {
        $productKey = $this->normalizeIdentifier($productId);

        if ($productKey !== null) {
            return 'product-'.$productKey;
        }

        $normalizedName = strtolower(trim((string) $productName));

        if ($normalizedName !== '') {
            return 'name-'.$normalizedName;
        }

        return 'order-'.$orderId;
    }

    private function normalizeIdentifier(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }

    private function accumulateWindowStats(array &$stats, float $revenue, int $units): void
    {
        $stats['revenue'] += $revenue;
        $stats['units'] += $units;
        $stats['orders'] += 1;
    }

    private function calculateChange(float|int $current, float|int $previous): ?float
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous == 0.0) {
            return $current > 0 ? null : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function formatAmountByCurrency(float $amount, ?string $currency): string
    {
        $currency = strtoupper(trim((string) $currency));
        $currency = $currency !== '' ? $currency : 'USD';
        $decimals = $currency === 'KHR' ? 0 : 2;
        $formatted = number_format($amount, $decimals);

        return $currency === 'USD'
            ? '$'.$formatted
            : $formatted.' '.$currency;
    }

    private function expirePendingOrders(): void
    {
        try {
            Order::expirePending(2000);
        } catch (\Throwable $exception) {
            Log::warning('Admin pending order expiry failed.', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function emptyAnalytics(
        int $totalOrders,
        int $paidOrders,
        int $pendingOrders,
        int $failedOrders,
        float $paidRevenue,
    ): array {
        return [
            'currency' => strtoupper((string) config('services.bakong.currency', 'USD')),
            'trend_days' => 14,
            'total_units_sold' => 0,
            'average_order_value' => $paidOrders > 0 ? round($paidRevenue / $paidOrders, 2) : 0.0,
            'average_units_per_order' => 0.0,
            'payment_rate' => $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 1) : 0.0,
            'paid_customers' => 0,
            'repeat_customers' => 0,
            'today' => ['revenue' => 0.0, 'units' => 0, 'orders' => 0],
            'this_week' => ['revenue' => 0.0, 'units' => 0, 'orders' => 0],
            'best_sales_day' => null,
            'revenue_change' => 0.0,
            'units_change' => 0.0,
            'today_revenue_change' => 0.0,
            'today_units_change' => 0.0,
            'week_revenue_change' => 0.0,
            'week_units_change' => 0.0,
            'trend_chart' => [
                'labels' => [],
                'revenue' => [],
                'units' => [],
                'orders' => [],
            ],
            'status_chart' => [
                'labels' => ['Paid', 'Pending', 'Failed'],
                'data' => [$paidOrders, $pendingOrders, $failedOrders],
            ],
            'top_products' => [],
        ];
    }
}
