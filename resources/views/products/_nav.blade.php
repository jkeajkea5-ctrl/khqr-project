@php
    $cart = session('cart', []);
    $count = collect($cart)->sum(fn ($item) => $item['qty']);
    $showMobileTabbar = $showMobileTabbar ?? true;
    $shouldUseBrandLogo = $useBrandLogo ?? (
        !($showBack ?? false)
        && !request()->routeIs('user.*')
    );
    $accountRoute = auth()->check() ? route('user.profile') : route('user.login');
@endphp
<nav class="shop-nav">
    <div class="container d-flex justify-content-between align-items-center py-3">
        <div class="d-flex align-items-center gap-2">
            @if(!empty($showBack))
                <a href="{{ route('home') }}" class="icon-btn d-md-none" aria-label="Back">
                    <i class="bi bi-arrow-left"></i>
                </a>
            @endif
            <a href="{{ route('home') }}" class="brand" aria-label="TEAM10 Clothing Store">
                @if($shouldUseBrandLogo)
                    <img src="{{ asset('brand/team10-logo.png') }}" alt="TEAM10 Clothing Store" class="brand-logo">
                @else
                    <span class="brand-text">TEAM10 Clothing Store</span>
                @endif
            </a>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('cart.index') }}" class="icon-btn">
                <i class="bi bi-bag"></i>
                @if($count > 0)
                    <span class="cart-count">{{ $count }}</span>
                @endif
            </a>
            @auth
                <a href="{{ route('user.orders') }}" class="icon-btn"><i class="bi bi-receipt"></i></a>
                <a href="{{ route('user.profile') }}" class="icon-btn"><i class="bi bi-person"></i></a>
            @else
                <a href="{{ route('user.login') }}" class="icon-btn"><i class="bi bi-person"></i></a>
                <a href="{{ route('user.register') }}" class="icon-btn"><i class="bi bi-person-plus"></i></a>
            @endauth
        </div>
    </div>
</nav>

@if($showMobileTabbar)
<nav class="mobile-tabbar d-md-none">
    <a href="{{ route('home') }}" class="tab-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="bi bi-house"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('user.orders') }}" class="tab-item {{ request()->routeIs('user.orders') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i>
        <span>Orders</span>
    </a>
    <a href="{{ $accountRoute }}" class="tab-item {{ request()->routeIs('user.profile', 'user.login', 'user.register') ? 'active' : '' }}">
        <i class="bi bi-person"></i>
        <span>Account</span>
    </a>
</nav>
@endif
