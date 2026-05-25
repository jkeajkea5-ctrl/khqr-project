@extends('layouts.app')

@section('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap');

    :root {
        --admin-ink: #150734;
        --admin-primary: #28559A;
        --admin-accent: #3778C2;
        --admin-bg: #f3f6fb;
        --admin-card: #ffffff;
        --admin-text: #0f172a;
        --admin-muted: #64748b;
        --admin-border: rgba(21, 7, 52, 0.08);
        --admin-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
    }

    .admin-shell {
        font-family: 'Space Grotesk', sans-serif;
        min-height: 100vh;
        background: var(--admin-bg);
        color: var(--admin-text);
    }

    .admin-sidebar {
        background: #ffffff;
        border-right: 1px solid var(--admin-border);
        transition: all 0.3s ease;
        z-index: 1100;
    }

    .admin-main {
        flex: 1 1 auto;
        min-width: 0;
    }

    .admin-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 14px;
        color: var(--admin-ink);
        text-decoration: none;
        font-weight: 600;
        transition: 0.2s;
        margin-bottom: 8px;
    }

    .admin-nav a:hover,
    .admin-nav a.active {
        background: rgba(55, 120, 194, 0.12);
        color: var(--admin-primary);
    }

    .menu-trigger {
        position: fixed;
        bottom: 18px;
        right: 18px;
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--admin-primary);
        color: #ffffff;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        border: none;
        z-index: 1200;
        box-shadow: 0 10px 24px rgba(40, 85, 154, 0.35);
    }

    @media (min-width: 992px) {
        .admin-shell .row.g-0 { display: flex; flex-wrap: nowrap; }
        .admin-sidebar { position: sticky; top: 0; width: 200px; height: 100vh; flex: 0 0 200px; }
        .admin-main { margin-left: 0; width: auto; flex: 1 1 auto; }
    }

    @media (max-width: 991px) {
        .menu-trigger { display: flex; }

        .admin-sidebar {
            position: fixed;
            left: -100%;
            width: 82%;
            height: 100vh;
            box-shadow: 16px 0 30px rgba(15, 23, 42, 0.2);
        }

        .admin-sidebar.show { left: 0; }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            display: none;
            z-index: 1050;
        }

        .sidebar-overlay.show { display: block; }
    }

    .admin-topbar {
        background: #ffffff;
        border: 1px solid var(--admin-border);
        border-radius: 20px;
        padding: 0.75rem 1rem;
        box-shadow: var(--admin-shadow);
        margin-bottom: 16px !important;
    }

    .admin-main {
        padding: 24px;
        max-width: 100%;
    }

    .admin-page-header {
        margin-bottom: 16px;
    }

    .admin-table-compact th,
    .admin-table-compact td {
        padding: 0.45rem 0.6rem;
    }

    .admin-card {
        background: var(--admin-card);
        border: 1px solid var(--admin-border);
        border-radius: 18px;
        box-shadow: var(--admin-shadow);
        color: var(--admin-text);
        animation: fadeUp 0.4s ease;
    }

    .admin-table-responsive {
        overflow-x: auto;
    }

    .admin-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .admin-actions form {
        margin: 0;
    }

    .admin-actions .btn {
        white-space: nowrap;
        padding: 6px 10px;
        font-size: 0.85rem;
    }

    .admin-quick-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0.6rem 0.9rem;
        border: 1px solid transparent;
        color: #ffffff;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 700;
        letter-spacing: 0.01em;
        box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }

    .admin-quick-btn:hover {
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(15, 23, 42, 0.16);
        filter: brightness(1.03);
    }

    .admin-quick-btn:focus-visible {
        outline: 2px solid rgba(55, 120, 194, 0.25);
        outline-offset: 2px;
        color: #ffffff;
    }

    .admin-quick-btn i {
        font-size: 0.95rem;
        line-height: 1;
    }

    .admin-quick-btn-add {
        background: #bc7080;
    }

    .admin-quick-btn-edit {
        background: #746088;
    }

    .admin-quick-btn-delete {
        background: #3f5c83;
    }

    .admin-quick-btn-neutral {
        background: #64748b;
    }

    .admin-table-compact {
        width: auto;
        min-width: 520px;
    }

    .admin-muted { color: var(--admin-muted); }

    .btn-khqr {
        background: var(--admin-primary);
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 999px;
        font-weight: 600;
        box-shadow: 0 12px 24px rgba(40, 85, 154, 0.3);
    }

    .btn-khqr:hover {
        background: var(--admin-accent);
        color: #ffffff;
    }

    .stat-card {
        border-radius: 18px;
        padding: 16px;
        color: #ffffff;
    }

    .stat-green { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .stat-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
    .stat-blue { background: linear-gradient(135deg, #3778C2, #28559A); }
    .stat-purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }

    .admin-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    .admin-list-item:last-child { border-bottom: none; }

    .admin-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid var(--admin-accent);
        background: linear-gradient(135deg, var(--admin-accent), var(--admin-primary));
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex: 0 0 44px;
    }

    .admin-avatar,
    .admin-avatar img,
    .admin-avatar span {
        border-radius: 50% !important;
    }

    .admin-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .admin-profile-photo,
    .admin-profile-initials {
        border-radius: 50% !important;
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
@php($currentAdmin = auth('admin')->user())
<div class="admin-shell">
    
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <button class="menu-trigger d-lg-none" id="menuBtn">
        <i class="bi bi-list" id="menuIcon"></i>
    </button>

    <div class="container-fluid p-0">
        <div class="row g-0">
            
            <aside class="admin-sidebar p-4" id="sidebar">
                <div class="mb-5 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="h5 fw-bold text-primary mb-0">TEAM10</div>
                        <small class="text-muted">CLOTHING STORE</small>
                    </div>
                    <button class="btn btn-link text-white d-lg-none" onclick="toggleSidebar()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <nav class="admin-nav">
                    <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    @if($currentAdmin?->canManageAdmins())
                        <a href="{{ route('admin.admins.index') }}" class="{{ request()->routeIs('admin.admins.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i> Staff
                        </a>
                    @endif
                    @if($currentAdmin?->canViewUsers())
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Users
                        </a>
                    @endif
                    @if($currentAdmin?->canViewProducts())
                        <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                            <i class="bi bi-box"></i> Products
                        </a>
                    @endif
                    @if($currentAdmin?->canViewCategories())
                        <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="bi bi-tags"></i> Category
                        </a>
                    @endif
                    @if($currentAdmin?->canManageSlides())
                        <a href="{{ route('admin.slides.index') }}" class="{{ request()->routeIs('admin.slides.*') ? 'active' : '' }}">
                            <i class="bi bi-images"></i> Slides
                        </a>
                    @endif
                    @if($currentAdmin?->canViewOrders())
                        <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                            <i class="bi bi-cart"></i> Orders
                        </a>
                    @endif
                    @if($currentAdmin?->canViewPayments())
                        <a href="{{ route('admin.payments.paid') }}" class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-check"></i> History
                        </a>
                    @endif
                </nav>

                <div class="mt-auto pt-5">
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn btn-outline-danger w-100 rounded-pill py-2 border-0" style="background: rgba(225, 29, 72, 0.1);">
                            <i class="bi bi-power me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </aside>

            <main class="p-4 p-lg-5 admin-main">
                <div class="admin-topbar d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="small fw-bold text-primary text-uppercase">{{ $currentAdmin?->roleLabel() ?? 'Admin' }}</div>
                        <h4 class="fw-bold mb-0">TEAM10 Dashboard</h4>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @yield('admin_actions')
                        <div class="text-end d-none d-sm-block">
                            <div class="fw-semibold">{{ $currentAdmin?->name }}</div>
                            <div class="admin-muted small">{{ $currentAdmin?->email }}</div>
                        </div>
                        <div class="admin-avatar" aria-label="{{ $currentAdmin?->name }}">
                            @if($currentAdmin?->photo)
                                <img src="{{ $currentAdmin->photo }}" alt="{{ $currentAdmin->name }}">
                            @else
                                <span>{{ $currentAdmin?->initials() }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="view-content">
                    @if(session('error'))
                        <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">{{ session('error') }}</div>
                    @endif
                    @yield('admin_content')
                </div>
            </main>
        </div>
    </div>
</div>

{{-- Sidebar Toggle Script --}}
<script>
    const menuBtn = document.getElementById('menuBtn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const menuIcon = document.getElementById('menuIcon');

    function toggleSidebar() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
        
        // Change icon from list to x
        if(sidebar.classList.contains('show')) {
            menuIcon.classList.replace('bi-list', 'bi-x-lg');
        } else {
            menuIcon.classList.replace('bi-x-lg', 'bi-list');
        }
    }

    menuBtn.addEventListener('click', toggleSidebar);
    overlay.addEventListener('click', toggleSidebar);
</script>
@endsection
