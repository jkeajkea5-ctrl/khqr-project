@extends('layouts.app')

@section('styles')
@include('products._styles')
<style>
.profile-shell {
    display: grid;
    gap: 24px;
}

.profile-hero {
    position: relative;
    overflow: hidden;
    padding: 28px;
    background:
        radial-gradient(420px 220px at 0% 0%, rgba(55, 120, 194, 0.22) 0%, transparent 60%),
        linear-gradient(135deg, rgba(21, 7, 52, 0.96) 0%, rgba(40, 85, 154, 0.96) 55%, rgba(55, 120, 194, 0.9) 100%);
    color: #ffffff;
}

.profile-hero::after {
    content: '';
    position: absolute;
    inset: auto -40px -60px auto;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);
}

.profile-hero .eyebrow {
    color: rgba(255, 255, 255, 0.72);
}

.profile-avatar {
    width: 82px;
    height: 82px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.08);
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: 0.04em;
}

.profile-hero-meta {
    color: rgba(255, 255, 255, 0.78);
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 14px;
}

.profile-stat {
    padding: 18px;
    background: #ffffff;
    border: 1px solid rgba(21, 7, 52, 0.08);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
}

.profile-stat-label {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--khqr-muted);
}

.profile-stat-value {
    margin-top: 6px;
    font-size: 1.55rem;
    font-weight: 700;
    color: var(--khqr-ink);
}

.profile-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
    gap: 24px;
}

.profile-panel {
    padding: 24px;
}

.profile-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}

.profile-panel-title {
    font-size: 1.12rem;
    font-weight: 700;
    margin-bottom: 4px;
}

.profile-panel-note {
    color: var(--khqr-muted);
    font-size: 0.92rem;
}

.profile-field {
    display: grid;
    gap: 8px;
    margin-bottom: 16px;
}

.profile-field label {
    font-size: 0.84rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #475569;
}

.profile-quick-links {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-top: 20px;
}

.profile-link-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    color: inherit;
    text-decoration: none;
    background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(243,246,251,0.98) 100%);
    border: 1px solid rgba(21, 7, 52, 0.08);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.profile-link-card:hover {
    transform: translateY(-2px);
    border-color: rgba(55, 120, 194, 0.28);
    box-shadow: 0 18px 30px rgba(15, 23, 42, 0.1);
}

.profile-link-icon {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(55, 120, 194, 0.12);
    color: var(--khqr-primary);
}

.profile-link-text strong {
    display: block;
    font-size: 0.95rem;
}

.profile-link-text span {
    display: block;
    font-size: 0.8rem;
    color: var(--khqr-muted);
}

.activity-list {
    display: grid;
    gap: 12px;
}

.activity-item {
    padding: 16px 18px;
    background: #f8fbff;
    border: 1px solid rgba(21, 7, 52, 0.08);
}

.activity-meta {
    color: var(--khqr-muted);
    font-size: 0.85rem;
}

.profile-empty {
    padding: 24px;
    border: 1px dashed rgba(21, 7, 52, 0.18);
    background: rgba(255, 255, 255, 0.72);
    text-align: center;
    color: var(--khqr-muted);
}

.logout-inline {
    display: inline-flex;
}

@media (max-width: 992px) {
    .profile-stats,
    .profile-grid,
    .profile-quick-links {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection

@section('content')
@include('products._nav', ['useBrandLogo' => false])
<div class="shop-page">
    <div class="container py-4 py-md-5">
        <div class="profile-shell">
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-0">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-0">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $initials = collect(explode(' ', trim((string) $user->name)))
                    ->filter()
                    ->take(2)
                    ->map(fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                    ->implode('');
                $joinDate = optional($user->created_at)->format('d M Y');
                $lastPaidAt = optional($stats['latest_paid_at'] ?? null)->format('d M Y, h:i A');
            @endphp

            <section class="card-khqr profile-hero">
                <div class="row g-4 align-items-center position-relative">
                    <div class="col-lg-8">
                        <div class="eyebrow mb-2">Account Center</div>
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <div class="profile-avatar">{{ $initials !== '' ? $initials : 'U' }}</div>
                            <div>
                                <h1 class="mb-1 fw-semibold">{{ $user->name }}</h1>
                                <div class="profile-hero-meta">
                                    <span>{{ $user->phone }}</span>
                                    @if($user->email)
                                        <span class="mx-2 d-none d-md-inline">•</span>
                                        <span class="d-block d-md-inline">{{ $user->email }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="text-lg-end profile-hero-meta">
                            <div>Joined {{ $joinDate ?: 'Recently' }}</div>
                            <div class="mt-2">Last paid order {{ $lastPaidAt ?: 'Not yet available' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="profile-stats">
                <div class="card-khqr profile-stat">
                    <div class="profile-stat-label">Total orders</div>
                    <div class="profile-stat-value">{{ number_format($stats['total_orders']) }}</div>
                </div>
                <div class="card-khqr profile-stat">
                    <div class="profile-stat-label">Paid orders</div>
                    <div class="profile-stat-value">{{ number_format($stats['paid_orders']) }}</div>
                </div>
                <div class="card-khqr profile-stat">
                    <div class="profile-stat-label">Pending orders</div>
                    <div class="profile-stat-value">{{ number_format($stats['pending_orders']) }}</div>
                </div>
                <div class="card-khqr profile-stat">
                    <div class="profile-stat-label">Paid total</div>
                    <div class="profile-stat-value">${{ number_format($stats['paid_total'], 2) }}</div>
                </div>
            </section>

            <section class="profile-grid">
                <div class="card-khqr profile-panel">
                    <div class="profile-panel-header">
                        <div>
                            <div class="profile-panel-title">My profile</div>
                            <div class="profile-panel-note">Keep your shopping details current for smoother checkout and invoices.</div>
                        </div>
                        <form method="POST" action="{{ route('user.logout') }}" class="logout-inline">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm">
                                <i class="bi bi-box-arrow-right"></i>
                                Sign out
                            </button>
                        </form>
                    </div>

                    <form method="POST" action="{{ route('user.profile.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="profile-field">
                            <label for="name">Full name</label>
                            <input id="name" type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="profile-field">
                            <label for="phone">Phone number</label>
                            <input id="phone" type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>

                        <div class="profile-field">
                            <label for="email">Email address</label>
                            <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" placeholder="Optional">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="profile-field mb-0">
                                    <label for="password">New password</label>
                                    <input id="password" type="password" name="password" class="form-control" placeholder="Leave blank to keep current">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="profile-field mb-0">
                                    <label for="password_confirmation">Confirm password</label>
                                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="submit" class="btn btn-khqr">
                                <i class="bi bi-check2-circle"></i>
                                Save changes
                            </button>
                            <a href="{{ route('user.orders') }}" class="btn btn-ghost">
                                <i class="bi bi-receipt"></i>
                                View all orders
                            </a>
                        </div>
                    </form>

                    <div class="profile-quick-links">
                        <a href="{{ route('home') }}" class="profile-link-card">
                            <span class="profile-link-icon"><i class="bi bi-house"></i></span>
                            <span class="profile-link-text">
                                <strong>Shop</strong>
                                <span>Browse latest drops</span>
                            </span>
                        </a>
                        <a href="{{ route('cart.index') }}" class="profile-link-card">
                            <span class="profile-link-icon"><i class="bi bi-bag"></i></span>
                            <span class="profile-link-text">
                                <strong>Cart</strong>
                                <span>Review your picks</span>
                            </span>
                        </a>
                        <a href="{{ route('user.orders') }}" class="profile-link-card">
                            <span class="profile-link-icon"><i class="bi bi-clock-history"></i></span>
                            <span class="profile-link-text">
                                <strong>Orders</strong>
                                <span>Track recent checkouts</span>
                            </span>
                        </a>
                    </div>
                </div>

                <div class="card-khqr profile-panel">
                    <div class="profile-panel-header">
                        <div>
                            <div class="profile-panel-title">Recent activity</div>
                            <div class="profile-panel-note">A quick view of your latest checkout history.</div>
                        </div>
                    </div>

                    @if($recentOrders->isNotEmpty())
                        <div class="activity-list">
                            @foreach($recentOrders as $order)
                                <article class="activity-item">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">{{ $order->display_product_name }}</div>
                                            <div class="activity-meta mt-1">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                                        </div>
                                        <span class="badge {{ $order->status === 'PAID' ? 'bg-success' : ($order->status === 'FAILED' ? 'bg-danger' : 'bg-warning text-dark') }}">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div class="fw-semibold text-primary">${{ number_format((float) $order->amount, 2) }}</div>
                                        @if($order->bill_number)
                                            <div class="activity-meta">{{ $order->bill_number }}</div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="profile-empty">
                            No orders yet. Start shopping and your activity will appear here.
                        </div>
                    @endif

                    <div class="summary-card mt-4">
                        <div class="text-muted small">Account status</div>
                        <div class="fs-5 fw-semibold">Active shopper</div>
                        <div class="text-muted small mt-1">
                            {{ number_format($stats['failed_orders']) }} failed or expired order{{ $stats['failed_orders'] === 1 ? '' : 's' }} recorded so far.
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
