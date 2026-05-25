@php
    $cart = session('cart', []);
    $count = collect($cart)->sum(fn ($item) => $item['qty']);
    $shouldUseBrandLogo = $useBrandLogo ?? (
        !($showBack ?? false)
        && !request()->routeIs('user.*')
    );
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
                <form method="POST" action="{{ route('user.logout') }}">
                    @csrf
                    <button class="icon-btn" type="submit"><i class="bi bi-box-arrow-right"></i></button>
                </form>
            @else
                <a href="{{ route('user.login') }}" class="icon-btn"><i class="bi bi-person"></i></a>
                <a href="{{ route('user.register') }}" class="icon-btn"><i class="bi bi-person-plus"></i></a>
            @endauth
        </div>
    </div>
</nav>

<nav class="mobile-tabbar d-md-none">
    <a href="{{ route('home') }}" class="tab-item {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="bi bi-house"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('cart.index') }}" class="tab-item {{ request()->routeIs('cart.*') ? 'active' : '' }}">
        <i class="bi bi-bag"></i>
        <span>Cart</span>
        @if($count > 0)
            <span class="tab-badge">{{ $count }}</span>
        @endif
    </a>
    <a href="{{ route('user.orders') }}" class="tab-item {{ request()->routeIs('user.orders') ? 'active' : '' }}">
        <i class="bi bi-receipt"></i>
        <span>Orders</span>
    </a>
    <a href="{{ route('user.login') }}" class="tab-item {{ request()->routeIs('user.login') ? 'active' : '' }}">
        <i class="bi bi-person"></i>
        <span>Account</span>
    </a>
</nav>
