<!DOCTYPE html>
<html>
<head>
 <title>TEAM10 Clothing Store</title>
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <link rel="icon" type="image/png" href="{{ asset('brand/team10-logo.png') }}">
 <link rel="shortcut icon" href="{{ asset('brand/team10-logo.png') }}">
 <link rel="apple-touch-icon" href="{{ asset('brand/team10-logo.png') }}">
 <link rel="preconnect" href="https://fonts.googleapis.com">
 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
 <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
 <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
 <link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">
 <link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
rel="stylesheet">
 @yield('styles')
 <style>
 :root {
     --app-radius: 2px;
     --bs-border-radius: 2px;
     --bs-border-radius-sm: 2px;
     --bs-border-radius-lg: 2px;
     --bs-border-radius-xl: 2px;
     --bs-border-radius-xxl: 2px;
     --bs-border-radius-2xl: 2px;
     --bs-border-radius-pill: 2px;
     --khqr-radius-sm: 2px;
     --khqr-radius-md: 2px;
     --khqr-radius-lg: 2px;
     --khqr-radius-xl: 2px;
 }

 .btn,
 .form-control,
 .form-select,
 .form-check-input,
 .input-group-text,
 .alert,
 .card,
 .dropdown-menu,
 .modal-content,
 .page-link,
 .badge,
 .table-responsive,
 .list-group-item,
 .search-bar,
 .card-khqr,
 .admin-card,
 .admin-topbar,
 .admin-nav a,
 .admin-sidebar,
 .stat-card,
 .hero-metric,
 .pulse-stat,
 .insight-tile,
 .status-item,
 .top-product-bar,
 .soft-pill,
 .empty-state,
 .icon-btn,
 .badge-soft,
 .summary-card,
 .cart-card,
 .qty-btn,
 .qty-input,
 .mobile-tabbar,
 .cart-footer,
 .mobile-action-bar,
 .spec-item,
 .choice-chip,
 .qr-box,
 .shop-nav,
 .product-card,
 .admin-list-item,
 .menu-trigger,
 .cart-toast,
 .cart-count,
 .tab-badge,
 .trend-badge,
 .hero-carousel .carousel-item img,
 .hero-carousel .carousel-caption,
 .top-product-bar span,
 .status-dot,
 .password-field .password-toggle,
 .rounded,
 .rounded-1,
 .rounded-2,
 .rounded-3,
 .rounded-4,
 .rounded-5,
 .rounded-pill,
 .rounded-top,
 .rounded-top-1,
 .rounded-top-2,
 .rounded-top-3,
 .rounded-top-4,
 .rounded-top-5,
 .rounded-bottom,
 .rounded-bottom-1,
 .rounded-bottom-2,
 .rounded-bottom-3,
 .rounded-bottom-4,
 .rounded-bottom-5,
 .rounded-start,
 .rounded-start-1,
 .rounded-start-2,
 .rounded-start-3,
 .rounded-start-4,
 .rounded-start-5,
 .rounded-end,
 .rounded-end-1,
 .rounded-end-2,
 .rounded-end-3,
 .rounded-end-4,
 .rounded-end-5,
 button[style*="border-radius"],
 a[style*="border-radius"],
 div[style*="border-radius"],
 span[style*="border-radius"],
 input[style*="border-radius"],
 textarea[style*="border-radius"],
 select[style*="border-radius"],
 img[style*="border-radius"] {
     border-radius: var(--app-radius) !important;
 }

 .password-field .form-control {
     padding-right: 56px;
 }

 .password-field .password-toggle {
     position: absolute;
     right: 14px;
     top: 50%;
     transform: translateY(-50%);
     width: 32px;
     height: 32px;
     display: inline-flex;
     align-items: center;
     justify-content: center;
     border: none;
     background: transparent;
     color: #64748b;
     padding: 0;
     z-index: 2;
 }

 .password-field .password-toggle:hover {
     background: rgba(100, 116, 139, 0.12);
     color: #0f172a;
 }

 .password-field .password-toggle:focus-visible {
     outline: 2px solid rgba(55, 120, 194, 0.45);
     outline-offset: 2px;
 }

 .password-field .password-toggle i {
     pointer-events: none;
 }

 .khqr-pagination {
     display: flex;
     flex-wrap: wrap;
     align-items: center;
     justify-content: space-between;
     gap: 12px;
 }

 .khqr-pagination-summary {
     color: #64748b;
     font-size: 0.95rem;
 }

 .khqr-pagination .pagination {
     gap: 8px;
 }

 .khqr-pagination .page-item {
     list-style: none;
 }

 .khqr-pagination .page-link {
     min-width: 42px;
     padding: 0.55rem 0.85rem;
     border: 1px solid rgba(15, 23, 42, 0.12);
     background: #ffffff;
     color: #0f172a;
     font-weight: 600;
     box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
 }

 .khqr-pagination .page-link:hover {
     background: #f8fafc;
     color: #0f172a;
 }

 .khqr-pagination .page-item.active .page-link {
     background: #28559A;
     border-color: #28559A;
     color: #ffffff;
 }

 .khqr-pagination .page-item.disabled .page-link {
     background: #e2e8f0;
     border-color: #e2e8f0;
     color: #94a3b8;
     box-shadow: none;
 }

 @media (max-width: 576px) {
     .khqr-pagination {
         flex-direction: column;
         align-items: stretch;
     }

     .khqr-pagination .pagination {
         justify-content: center;
         flex-wrap: wrap;
     }
 }
 </style>
</head>
<body>
 @yield('content')
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 <script>
 document.addEventListener('DOMContentLoaded', function () {
     document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
         button.addEventListener('click', function () {
             var wrapper = button.closest('.password-field');
             var input = wrapper ? wrapper.querySelector('input') : null;
             var icon = button.querySelector('i');

             if (!input) {
                 return;
             }

             var willShow = input.type === 'password';
             input.type = willShow ? 'text' : 'password';
             button.setAttribute('aria-label', willShow ? 'Hide password' : 'Show password');
             button.setAttribute('aria-pressed', willShow ? 'true' : 'false');

             if (icon) {
                 icon.classList.toggle('bi-eye', !willShow);
                 icon.classList.toggle('bi-eye-slash', willShow);
             }
         });
     });
 });
 </script>
 @yield('scripts')
</body>
</html>
