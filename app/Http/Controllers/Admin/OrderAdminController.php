<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OrderAdminController extends Controller
{
    public function dashboard()
    {
        Order::expirePending(2000);
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

        $analytics = $this->buildSalesAnalytics(
            $paidOrderData,
            $totalOrders,
            $paidOrders,
            $pendingOrders,
            $failedOrders,
            $paidRevenue,
        );

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
        Order::expirePending(2000);
        $orders = Order::latest()->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function paid()
    {
        Order::expirePending(2000);
        $orders = Order::where('status', 'PAID')->latest('paid_at')->paginate(15);
        return view('admin.payments.paid', compact('orders'));
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
            $paidAt = $this->resolveOrderDate($order);
            $dayKey = $paidAt->toDateString();
            $orderRevenue = (float) $order->amount;
            $orderUnits = 0;
            $items = is_array($order->items) ? $order->items : [];

            if ($order->user_id) {
                $customerOrders[(int) $order->user_id] = ($customerOrders[(int) $order->user_id] ?? 0) + 1;
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
        $date = $order->paid_at ?? $order->created_at ?? now();

        return $date instanceof Carbon ? $date : Carbon::parse($date);
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
        $productId = (int) $productId;

        if ($productId > 0) {
            return 'product-'.$productId;
        }

        $normalizedName = mb_strtolower(trim((string) $productName));

        if ($normalizedName !== '') {
            return 'name-'.$normalizedName;
        }

        return 'order-'.$orderId;
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
}
