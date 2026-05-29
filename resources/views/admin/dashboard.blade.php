@extends('admin.layouts.app')

@section('styles')
    @parent
    <style>
        .overview-metric-card {
            position: relative;
            overflow: hidden;
            min-height: 100%;
        }

        .overview-metric-card::after {
            content: "";
            position: absolute;
            inset: auto -28px -28px auto;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.14);
        }

        .overview-metric-label {
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.92;
        }

        .overview-metric-value {
            font-size: clamp(2rem, 4vw, 2.8rem);
            font-weight: 700;
            line-height: 1;
            margin-top: 10px;
        }

        .overview-metric-note {
            margin-top: 16px;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .hero-panel {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #28559a 52%, #4f8dd8 100%);
            color: #ffffff;
        }

        .hero-panel::before,
        .hero-panel::after {
            content: "";
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-panel::before {
            width: 240px;
            height: 240px;
            top: -90px;
            right: -90px;
            background: rgba(255, 255, 255, 0.11);
        }

        .hero-panel::after {
            width: 180px;
            height: 180px;
            bottom: -70px;
            left: -30px;
            background: rgba(34, 197, 94, 0.18);
        }

        .hero-panel > * {
            position: relative;
            z-index: 1;
        }

        .hero-kicker {
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.72);
        }

        .hero-amount {
            font-size: clamp(2.5rem, 5vw, 3.8rem);
            font-weight: 700;
            line-height: 1;
            margin-top: 12px;
        }

        .hero-copy {
            max-width: 34rem;
            margin-top: 12px;
            color: rgba(255, 255, 255, 0.76);
        }

        .hero-metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 24px;
        }

        .hero-metric {
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 2px;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .hero-metric .label,
        .pulse-stat .label,
        .insight-tile .label,
        .chart-subtitle {
            display: block;
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--admin-muted);
        }

        .hero-metric .label {
            color: rgba(255, 255, 255, 0.68);
        }

        .hero-metric .value,
        .pulse-stat .value,
        .insight-tile .value {
            display: block;
            font-size: 1.35rem;
            font-weight: 700;
            margin-top: 6px;
        }

        .hero-insights {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        .hero-insight {
            padding: 16px 18px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            background: rgba(15, 23, 42, 0.18);
            backdrop-filter: blur(12px);
        }

        .hero-insight-label {
            display: block;
            font-size: 0.76rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.62);
        }

        .hero-insight-value {
            display: block;
            font-size: 1.05rem;
            font-weight: 700;
            margin-top: 8px;
            color: #ffffff;
        }

        .hero-insight-note {
            display: block;
            margin-top: 6px;
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.72);
            line-height: 1.45;
        }

        .trend-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 10px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .trend-badge-light {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
        }

        .trend-up {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
        }

        .trend-down {
            background: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
        }

        .trend-neutral {
            background: rgba(148, 163, 184, 0.16);
            color: #475569;
        }

        .pulse-card,
        .chart-card,
        .insight-card {
            min-height: 100%;
        }

        .pulse-grid,
        .insight-grid {
            display: grid;
            gap: 12px;
        }

        .pulse-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 18px;
        }

        .pulse-stat,
        .insight-tile {
            border: 1px solid var(--admin-border);
            border-radius: 2px;
            padding: 14px 16px;
            background: linear-gradient(180deg, rgba(243, 246, 251, 0.92), #ffffff);
        }

        .pulse-note,
        .insight-note {
            margin-top: 16px;
            color: var(--admin-muted);
            font-size: 0.92rem;
        }

        .chart-head {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 12px;
            margin-bottom: 18px;
        }

        .chart-wrap {
            position: relative;
            height: 320px;
        }

        .status-legend {
            display: grid;
            gap: 12px;
            margin-top: 24px;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .status-meta {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex: 0 0 10px;
        }

        .top-products-list {
            display: grid;
            gap: 16px;
        }

        .top-product-item {
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(15, 23, 42, 0.06);
        }

        .top-product-item:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .top-product-head {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 12px;
            margin-bottom: 10px;
        }

        .top-product-name {
            font-weight: 700;
        }

        .top-product-meta {
            color: var(--admin-muted);
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .top-product-bar {
            height: 9px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(55, 120, 194, 0.12);
        }

        .top-product-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #3778c2, #22c55e);
        }

        .soft-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 16px;
        }

        .soft-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(55, 120, 194, 0.08);
            color: var(--admin-primary);
            font-weight: 600;
        }

        .empty-state {
            min-height: 240px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: var(--admin-muted);
            background: linear-gradient(180deg, rgba(243, 246, 251, 0.9), rgba(255, 255, 255, 0.98));
            border: 1px dashed rgba(100, 116, 139, 0.28);
            border-radius: 2px;
            padding: 24px;
        }

        @media (max-width: 991px) {
            .hero-metrics {
                grid-template-columns: 1fr;
            }

            .hero-insights {
                grid-template-columns: 1fr;
            }

            .pulse-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .chart-head {
                flex-direction: column;
            }

            .chart-wrap {
                height: 280px;
            }

            .top-product-head {
                flex-direction: column;
            }
        }
    </style>
@endsection

@section('admin_content')
@php
    $currentAdmin = auth('admin')->user();
    $currency = $analytics['currency'] ?? 'USD';
    $currencyDecimals = $currency === 'KHR' ? 0 : 2;
    $formatAmount = function (float|int $amount) use ($currency, $currencyDecimals): string {
        $formatted = number_format((float) $amount, $currencyDecimals);

        return $currency === 'USD' ? '$'.$formatted : $formatted.' '.$currency;
    };
    $formatTrend = function (?float $change, string $period): array {
        if ($change === null) {
            return ['text' => 'New '.$period, 'class' => 'trend-neutral'];
        }

        if (abs($change) < 0.05) {
            return ['text' => 'Flat '.$period, 'class' => 'trend-neutral'];
        }

        return [
            'text' => ($change > 0 ? '+' : '').number_format($change, 1).'% '.$period,
            'class' => $change > 0 ? 'trend-up' : 'trend-down',
        ];
    };
    $revenueTrend = $formatTrend($analytics['revenue_change'] ?? null, 'vs previous 7 days');
    $todayRevenueTrend = $formatTrend($analytics['today_revenue_change'] ?? null, 'vs yesterday');
    $todayUnitsTrend = $formatTrend($analytics['today_units_change'] ?? null, 'vs yesterday');
    $weekRevenueTrend = $formatTrend($analytics['week_revenue_change'] ?? null, 'vs last week');
    $weekUnitsTrend = $formatTrend($analytics['week_units_change'] ?? null, 'vs last week');
    $maxTopUnits = collect($analytics['top_products'] ?? [])->max('units_sold') ?: 1;
    $statusColors = ['#22c55e', '#f97316', '#ef4444'];
    $trendChart = $analytics['trend_chart'] ?? ['labels' => [], 'revenue' => [], 'units' => [], 'orders' => []];
    $statusChart = $analytics['status_chart'] ?? ['labels' => [], 'data' => []];
    $hasStatusData = array_sum($statusChart['data']) > 0;
    $formattedPaidRevenue = $formatAmount($paidRevenue);
    $formattedAverageOrderValue = $formatAmount($analytics['average_order_value'] ?? 0);
    $formattedTodayRevenue = $formatAmount($analytics['today']['revenue'] ?? 0);
    $formattedWeekRevenue = $formatAmount($analytics['this_week']['revenue'] ?? 0);
    $formattedBestSalesDayRevenue = !empty($analytics['best_sales_day'])
        ? $formatAmount($analytics['best_sales_day']['revenue'])
        : null;
    $latestPaidOrder = $latestPaid->first();
    $latestPaidOrderMoment = $latestPaidOrder?->paid_at
        ? $latestPaidOrder->paid_at->format('d M, h:i A')
        : null;
    $topProducts = collect($analytics['top_products'] ?? [])
        ->map(function (array $product) use ($formatAmount): array {
            $product['formatted_revenue'] = $formatAmount($product['revenue'] ?? 0);

            return $product;
        })
        ->all();
@endphp

<div class="admin-page-header">
    <div>
        <div class="text-uppercase small admin-muted">Dashboard</div>
        <h2 class="fw-semibold">Overview</h2>
    </div>
    <div class="admin-muted small">Updated {{ now()->format('d M Y, h:i A') }}</div>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-blue overview-metric-card">
            <div class="overview-metric-label">Products</div>
            <div class="overview-metric-value">{{ number_format($totalProducts) }}</div>
            <div class="overview-metric-note">{{ number_format($totalUsers) }} registered shoppers</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-red overview-metric-card">
            <div class="overview-metric-label">Orders</div>
            <div class="overview-metric-value">{{ number_format($totalOrders) }}</div>
            <div class="overview-metric-note">{{ number_format($failedOrders) }} failed or expired</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-green overview-metric-card">
            <div class="overview-metric-label">Paid</div>
            <div class="overview-metric-value">{{ number_format($paidOrders) }}</div>
            <div class="overview-metric-note">{{ number_format($analytics['payment_rate'] ?? 0, 1) }}% success rate</div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card stat-yellow overview-metric-card">
            <div class="overview-metric-label">Pending</div>
            <div class="overview-metric-value">{{ number_format($pendingOrders) }}</div>
            <div class="overview-metric-note">Awaiting payment confirmation</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="admin-card p-4 p-lg-5 hero-panel">
            <div class="hero-kicker">Revenue collected</div>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3">
                <div>
                    <div class="hero-amount">{{ $formattedPaidRevenue }}</div>
                    <div class="hero-copy">
                        Track how sales are moving across the shop with live revenue, order value, and product volume from paid orders.
                    </div>
                </div>
                <span class="trend-badge trend-badge-light">{{ $revenueTrend['text'] }}</span>
            </div>

            <div class="hero-metrics">
                <div class="hero-metric">
                    <span class="label">Units sold</span>
                    <span class="value">{{ number_format($analytics['total_units_sold'] ?? 0) }}</span>
                </div>
                <div class="hero-metric">
                    <span class="label">Average order</span>
                    <span class="value">{{ $formattedAverageOrderValue }}</span>
                </div>
                <div class="hero-metric">
                    <span class="label">Average units / order</span>
                    <span class="value">{{ number_format($analytics['average_units_per_order'] ?? 0, 1) }}</span>
                </div>
            </div>

            <div class="hero-insights">
                <div class="hero-insight">
                    <span class="hero-insight-label">Best sales day</span>
                    @if(!empty($analytics['best_sales_day']))
                        <span class="hero-insight-value">{{ $analytics['best_sales_day']['label'] }}</span>
                        <span class="hero-insight-note">{{ $formattedBestSalesDayRevenue }} in paid revenue</span>
                    @else
                        <span class="hero-insight-value">Waiting for activity</span>
                        <span class="hero-insight-note">Your strongest sales day will appear here once paid orders build up.</span>
                    @endif
                </div>
                <div class="hero-insight">
                    <span class="hero-insight-label">Latest payment</span>
                    @if($latestPaidOrder)
                        <span class="hero-insight-value">{{ $latestPaidOrder->formatted_amount }}</span>
                        <span class="hero-insight-note">#{{ $latestPaidOrder->id }} @if($latestPaidOrderMoment) | {{ $latestPaidOrderMoment }} @endif</span>
                    @else
                        <span class="hero-insight-value">No payment yet</span>
                        <span class="hero-insight-note">The newest confirmed payment will show here as orders come in.</span>
                    @endif
                </div>
                <div class="hero-insight">
                    <span class="hero-insight-label">Customer momentum</span>
                    <span class="hero-insight-value">{{ number_format($analytics['paid_customers'] ?? 0) }} paying users</span>
                    <span class="hero-insight-note">{{ number_format($analytics['repeat_customers'] ?? 0) }} repeat customers returning to buy again.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-card p-4 pulse-card">
            <div class="d-flex justify-content-between align-items-start gap-3">
                <div>
                    <div class="text-uppercase small admin-muted">Sales pulse</div>
                    <h5 class="fw-semibold mb-1">Today & this week</h5>
                    <div class="chart-subtitle">Quick signals you can act on fast.</div>
                </div>
                <div class="admin-muted small text-end">Live snapshot</div>
            </div>

            <div class="pulse-grid">
                <div class="pulse-stat">
                    <span class="label">Today revenue</span>
                    <span class="value">{{ $formattedTodayRevenue }}</span>
                    <span class="trend-badge {{ $todayRevenueTrend['class'] }} mt-2">{{ $todayRevenueTrend['text'] }}</span>
                </div>
                <div class="pulse-stat">
                    <span class="label">Today units</span>
                    <span class="value">{{ number_format($analytics['today']['units'] ?? 0) }}</span>
                    <span class="trend-badge {{ $todayUnitsTrend['class'] }} mt-2">{{ $todayUnitsTrend['text'] }}</span>
                </div>
                <div class="pulse-stat">
                    <span class="label">This week revenue</span>
                    <span class="value">{{ $formattedWeekRevenue }}</span>
                    <span class="trend-badge {{ $weekRevenueTrend['class'] }} mt-2">{{ $weekRevenueTrend['text'] }}</span>
                </div>
                <div class="pulse-stat">
                    <span class="label">This week units</span>
                    <span class="value">{{ number_format($analytics['this_week']['units'] ?? 0) }}</span>
                    <span class="trend-badge {{ $weekUnitsTrend['class'] }} mt-2">{{ $weekUnitsTrend['text'] }}</span>
                </div>
            </div>

            <div class="soft-pills">
                <span class="soft-pill"><i class="bi bi-receipt-cutoff"></i> {{ number_format($analytics['today']['orders'] ?? 0) }} orders today</span>
                <span class="soft-pill"><i class="bi bi-people"></i> {{ number_format($analytics['paid_customers'] ?? 0) }} paying users</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-8">
        <div class="admin-card p-4 chart-card">
            <div class="chart-head">
                <div>
                    <div class="text-uppercase small admin-muted">Trend chart</div>
                    <h5 class="fw-semibold mb-1">Sales over the last {{ $analytics['trend_days'] ?? 14 }} days</h5>
                    <div class="chart-subtitle">Revenue and units sold are plotted together so movement is easier to spot.</div>
                </div>
                <div class="admin-muted small text-xl-end">Paid orders only</div>
            </div>
            <div class="chart-wrap">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="admin-card p-4 chart-card">
            <div class="chart-head">
                <div>
                    <div class="text-uppercase small admin-muted">Order mix</div>
                    <h5 class="fw-semibold mb-1">Payment status</h5>
                    <div class="chart-subtitle">A quick view of paid, pending, and failed orders.</div>
                </div>
            </div>

            @if($hasStatusData)
                <div class="chart-wrap" style="height: 240px;">
                    <canvas id="statusChart"></canvas>
                </div>
            @else
                <div class="empty-state">Orders will appear here once checkout activity starts.</div>
            @endif

            <div class="status-legend">
                @foreach($statusChart['labels'] as $index => $label)
                    <div class="status-item">
                        <span class="status-meta">
                            <span class="status-dot" style="background: {{ $statusColors[$index] ?? '#94a3b8' }}"></span>
                            {{ $label }}
                        </span>
                        <strong>{{ number_format(($statusChart['data'][$index] ?? 0)) }}</strong>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="admin-card p-4 insight-card">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="text-uppercase small admin-muted">Top products</div>
                    <h5 class="fw-semibold mb-1">Best sellers</h5>
                    <div class="chart-subtitle">Ranked by total units sold from paid orders.</div>
                </div>
                <span class="admin-muted small">All time</span>
            </div>

            @if(!empty($topProducts))
                <div class="top-products-list">
                    @foreach($topProducts as $product)
                        @php
                            $barWidth = max(8, (int) round(($product['units_sold'] / $maxTopUnits) * 100));
                        @endphp
                        <div class="top-product-item">
                            <div class="top-product-head">
                                <div>
                                    <div class="top-product-name">{{ $product['name'] }}</div>
                                    <div class="top-product-meta">{{ number_format($product['units_sold']) }} sold | {{ $product['formatted_revenue'] }}</div>
                                </div>
                                <div class="fw-semibold text-primary">{{ number_format($product['units_sold']) }} units</div>
                            </div>
                            <div class="top-product-bar">
                                <span style="width: {{ $barWidth }}%"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">Top-selling products will show here once the first paid orders come in.</div>
            @endif
        </div>
    </div>

    <div class="col-xl-6">
        <div class="admin-card p-4 insight-card">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <div class="text-uppercase small admin-muted">Business snapshot</div>
                    <h5 class="fw-semibold mb-1">More signals</h5>
                    <div class="chart-subtitle">Helpful context around your customers, team, and best-performing day.</div>
                </div>
                <span class="admin-muted small">Overview</span>
            </div>

            <div class="insight-grid">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="insight-tile">
                            <span class="label">Repeat customers</span>
                            <span class="value">{{ number_format($analytics['repeat_customers'] ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="insight-tile">
                            <span class="label">Paying users</span>
                            <span class="value">{{ number_format($analytics['paid_customers'] ?? 0) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="insight-tile">
                            <span class="label">Staff accounts</span>
                            <span class="value">{{ number_format($totalAdmins) }}</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="insight-tile">
                            <span class="label">Registered users</span>
                            <span class="value">{{ number_format($totalUsers) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pulse-note">
                @if(!empty($analytics['best_sales_day']))
                    Best sales day so far was <strong>{{ $analytics['best_sales_day']['label'] }}</strong> with
                    <strong>{{ $formattedBestSalesDayRevenue }}</strong> in paid revenue.
                @else
                    Your strongest sales day will appear here once paid orders are recorded.
                @endif
            </div>

            @if($currentAdmin?->canManageAdmins())
                <a href="{{ route('admin.admins.index') }}" class="btn btn-khqr mt-3">Manage Staff</a>
            @elseif($currentAdmin?->canViewUsers())
                <a href="{{ route('admin.users.index') }}" class="btn btn-khqr mt-3">View Users</a>
            @endif
        </div>
    </div>
</div>

<div class="admin-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-semibold mb-1">Latest paid orders</h5>
            <div class="chart-subtitle">Recent payments that have already been confirmed.</div>
        </div>
        <span class="admin-muted small">Last 10 payments</span>
    </div>

    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0">
            <thead class="admin-muted small text-uppercase">
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Paid At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($latestPaid as $o)
                    <tr>
                        <td>{{ $o->id }}</td>
                        <td>{{ $o->display_product_name }}</td>
                        <td class="fw-semibold text-primary">{{ $o->formatted_amount }}</td>
                        <td>{{ optional($o->paid_at)->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">No paid orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-md-none">
        @forelse($latestPaid as $o)
            <div class="admin-list-item">
                <div>
                    <div class="fw-semibold">#{{ $o->id }} - {{ $o->display_product_name }}</div>
                    <div class="admin-muted small">{{ optional($o->paid_at)->format('d M Y, h:i A') }}</div>
                </div>
                <div class="fw-semibold text-primary">{{ $o->formatted_amount }}</div>
            </div>
        @empty
            <div class="text-center text-muted py-4">No paid orders yet.</div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart) {
                return;
            }

            const trendData = @json($trendChart);
            const statusData = @json($statusChart);
            const currencyCode = @json($currency);
            const decimals = currencyCode === 'KHR' ? 0 : 2;
            let moneyFormatter = null;

            try {
                moneyFormatter = new Intl.NumberFormat(undefined, {
                    style: 'currency',
                    currency: currencyCode,
                    maximumFractionDigits: decimals,
                });
            } catch (error) {
                moneyFormatter = null;
            }

            const formatMoney = function (value) {
                const amount = Number(value || 0);

                if (moneyFormatter) {
                    return moneyFormatter.format(amount);
                }

                return currencyCode === 'USD'
                    ? '$' + amount.toFixed(decimals)
                    : amount.toFixed(decimals) + ' ' + currencyCode;
            };

            const trendCanvas = document.getElementById('salesTrendChart');

            if (trendCanvas) {
                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: trendData.labels,
                        datasets: [
                            {
                                label: 'Revenue',
                                data: trendData.revenue,
                                yAxisID: 'y',
                                borderColor: '#3778C2',
                                backgroundColor: 'rgba(55, 120, 194, 0.16)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            },
                            {
                                label: 'Units sold',
                                data: trendData.units,
                                yAxisID: 'y1',
                                borderColor: '#22c55e',
                                backgroundColor: 'rgba(34, 197, 94, 0.12)',
                                fill: false,
                                tension: 0.3,
                                pointRadius: 3,
                                pointHoverRadius: 5,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 10,
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        if (context.dataset.label === 'Revenue') {
                                            return 'Revenue: ' + formatMoney(context.parsed.y);
                                        }

                                        return 'Units sold: ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                }
                            },
                            y: {
                                position: 'left',
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        return formatMoney(value);
                                    }
                                }
                            },
                            y1: {
                                position: 'right',
                                beginAtZero: true,
                                grid: {
                                    drawOnChartArea: false,
                                },
                                ticks: {
                                    precision: 0,
                                }
                            }
                        }
                    }
                });
            }

            const statusCanvas = document.getElementById('statusChart');

            if (statusCanvas && Array.isArray(statusData.data) && statusData.data.some(function (value) { return Number(value) > 0; })) {
                new Chart(statusCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: statusData.labels,
                        datasets: [{
                            data: statusData.data,
                            backgroundColor: ['#22c55e', '#f97316', '#ef4444'],
                            borderWidth: 0,
                            hoverOffset: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                display: false,
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
