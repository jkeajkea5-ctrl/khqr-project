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
        overflow-x: hidden;
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
        width: 100%;
    }

    .admin-nav a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 2px;
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
        border-radius: 2px;
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
        border-radius: 2px;
        box-shadow: var(--admin-shadow);
        color: var(--admin-text);
        animation: fadeUp 0.4s ease;
    }

    .admin-table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .admin-card,
    .admin-topbar,
    .admin-list-item,
    .view-content {
        min-width: 0;
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
        border-radius: 2px;
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
        background: #22c55e;
    }

    .admin-quick-btn-add:hover {
        background: #16a34a;
    }

    .admin-quick-btn-edit {
        background: #3778C2;
    }

    .admin-quick-btn-edit:hover {
        background: #28559A;
    }

    .admin-quick-btn-delete {
        background: #ef4444;
    }

    .admin-quick-btn-delete:hover {
        background: #dc2626;
    }

    .admin-quick-btn-neutral {
        background: #3778C2;
    }

    .admin-quick-btn-neutral:hover {
        background: #28559A;
    }

    .admin-table-compact {
        width: 100%;
        min-width: 520px;
    }

    .admin-muted { color: var(--admin-muted); }

    .btn-khqr {
        background: var(--admin-primary);
        color: #ffffff;
        border: none;
        padding: 10px 18px;
        border-radius: 2px;
        font-weight: 600;
        box-shadow: 0 12px 24px rgba(40, 85, 154, 0.3);
    }

    .btn-khqr:hover {
        background: var(--admin-accent);
        color: #ffffff;
    }

    .stat-card {
        border-radius: 2px;
        padding: 16px;
        color: #ffffff;
    }

    .stat-green { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .stat-blue { background: linear-gradient(135deg, #3778C2, #28559A); }
    .stat-red { background: linear-gradient(135deg, #ef4444, #dc2626); }
    .stat-yellow { background: linear-gradient(135deg, #eab308, #ca8a04); }

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

    .view-content .pagination {
        flex-wrap: wrap;
        gap: 8px;
        margin: 0;
    }

    .pagination .page-link {
        color: var(--admin-primary);
        border: 1px solid var(--admin-border);
        background: #ffffff;
        border-radius: 2px;
        padding: 0.5rem 0.75rem;
        font-weight: 600;
        min-height: 40px;
        line-height: 1;
        box-shadow: 0 8px 16px rgba(15, 23, 42, 0.06);
        transition: all 0.2s ease;
    }

    .pagination .page-link-nav {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-width: 82px;
        padding-inline: 0.9rem;
    }

    .pagination .page-link:hover {
        background: var(--admin-primary);
        color: #ffffff;
        border-color: var(--admin-primary);
        transform: translateY(-1px);
    }

    .pagination .page-item.active .page-link {
        background: var(--admin-primary);
        color: #ffffff;
        border-color: var(--admin-primary);
    }

    .pagination .page-item.disabled .page-link {
        background: #f3f6fb;
        color: var(--admin-muted);
        border-color: var(--admin-border);
        cursor: not-allowed;
    }

    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        background: #ffffff;
        color: var(--admin-primary);
        border: 1px solid var(--admin-border);
        border-radius: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .pagination .page-item:first-child .page-link.page-link-nav,
    .pagination .page-item:last-child .page-link.page-link-nav {
        min-width: 82px;
    }

    .pagination .page-item:first-child .page-link:hover,
    .pagination .page-item:last-child .page-link:hover {
        background: var(--admin-primary);
        color: #ffffff;
        border-color: var(--admin-primary);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 767px) {
        .admin-main {
            padding: 14px 12px 86px !important;
        }

        .admin-topbar {
            border-radius: 2px;
            padding: 0.65rem 0.75rem;
            align-items: flex-start !important;
            gap: 10px;
        }

        .admin-topbar h4 {
            font-size: 1rem;
        }

        .admin-topbar > .d-flex:last-child {
            flex: 0 0 auto;
        }

        .admin-page-header {
            display: flex;
            flex-direction: column;
            align-items: flex-start !important;
            gap: 6px;
        }

        .admin-page-header h2 {
            font-size: 1.35rem;
        }

        .admin-card {
            border-radius: 2px;
        }

        .admin-card.p-4,
        .admin-card .p-4,
        .card-body.p-4 {
            padding: 1rem !important;
        }

        .admin-card .p-md-5,
        .card-body.p-md-5 {
            padding: 1rem !important;
        }

        .admin-list-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .admin-list-item > .d-flex:last-child {
            width: 100%;
            align-items: stretch !important;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            justify-content: stretch;
        }

        .admin-actions form,
        .admin-actions .btn,
        .admin-actions .admin-quick-btn {
            width: 100%;
        }

        .admin-actions .btn,
        .admin-quick-btn {
            min-height: 42px;
            padding: 0.55rem 0.7rem;
            font-size: 0.84rem;
            border-radius: 2px;
            white-space: normal;
            text-align: center;
        }

        .admin-card .d-flex.gap-2.mt-3,
        .admin-card .d-flex.gap-2 {
            flex-wrap: wrap;
        }

        .admin-card .d-flex.gap-2.mt-3 > *,
        .admin-card .d-flex.gap-2 > .admin-quick-btn,
        .admin-card .d-flex.gap-2 > form {
            flex: 1 1 140px;
        }

        .form-control-lg,
        .form-select-lg {
            min-height: 46px;
            font-size: 1rem;
        }

        .form-control,
        .form-select,
        textarea {
            max-width: 100%;
        }

        input[type="file"] {
            font-size: 0.9rem;
        }

        .admin-card img {
            max-width: 100%;
        }

        .admin-muted,
        .admin-card .small,
        .admin-card td {
            overflow-wrap: anywhere;
        }

        .menu-trigger {
            bottom: 18px;
            right: 14px;
            width: 48px;
            height: 48px;
        }

        .admin-sidebar {
            width: min(86vw, 320px);
            padding: 20px !important;
        }

        .view-content .pagination {
            justify-content: center;
        }

        .pagination .page-link {
            padding: 0.48rem 0.62rem;
        }

        .pagination .page-link-nav {
            min-width: 74px;
            padding-inline: 0.7rem;
        }

        .pagination .page-item:first-child .page-link.page-link-nav,
        .pagination .page-item:last-child .page-link.page-link-nav {
            min-width: 74px;
        }
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
