<style>
:root {
    --khqr-primary: #28559A;
    --khqr-primary-dark: #1F3E77;
    --khqr-accent: #3778C2;
    --khqr-ink: #150734;
    --khqr-bg: #f3f6fb;
    --khqr-card: #ffffff;
    --khqr-border: rgba(21, 7, 52, 0.08);
    --khqr-muted: #64748b;
    --khqr-radius-sm: 10px;
    --khqr-radius-md: 14px;
    --khqr-radius-lg: 18px;
    --khqr-radius-xl: 22px;
}

body {
    background: var(--khqr-bg);
    color: var(--khqr-ink);
    font-family: 'Space Grotesk', system-ui, -apple-system, 'Segoe UI', sans-serif;
}

.shop-nav {
    position: sticky;
    top: 0;
    z-index: 10;
    background: rgba(248, 250, 252, 0.9);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--khqr-border);
}

.shop-nav .brand {
    display: inline-flex;
    align-items: center;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #0f172a;
    text-decoration: none;
}

.shop-nav .brand-logo {
    height: 42px;
    width: auto;
    display: block;
}

.shop-nav .brand-text {
    font-size: 1.05rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #0f172a;
    white-space: nowrap;
}

.icon-btn {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: var(--khqr-radius-md);
    border: 1px solid rgba(21, 7, 52, 0.08);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    color: var(--khqr-ink);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.icon-btn:hover {
    transform: translateY(-1px);
    border-color: rgba(55, 120, 194, 0.22);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
}

.icon-btn:focus-visible {
    outline: 2px solid rgba(55, 120, 194, 0.22);
    outline-offset: 2px;
}

.icon-btn i {
    font-size: 1.1rem;
}

.shop-page {
    background: radial-gradient(900px 420px at 10% 10%, rgba(55,120,194,0.18) 0%, rgba(55,120,194,0) 60%),
                radial-gradient(900px 380px at 90% 20%, rgba(40,85,154,0.18) 0%, rgba(40,85,154,0) 55%),
                #f3f6fb;
    min-height: calc(100vh - 64px);
}

.hero-carousel .carousel-item {
    height: 340px;
}

.hero-carousel .carousel-item img {
    object-fit: cover;
    height: 100%;
    width: 100%;
    border-radius: var(--khqr-radius-xl);
}

.hero-carousel .carousel-caption {
    background: rgba(15, 23, 42, 0.55);
    border-radius: var(--khqr-radius-lg);
    padding: 14px 18px;
    backdrop-filter: blur(6px);
    text-align: center;
    left: 50%;
    right: auto;
    bottom: 16px;
    transform: translateX(-50%);
    max-width: 80%;
}

.hero-carousel .carousel-indicators {
    bottom: 8px;
}

.hero-carousel .carousel-indicators [data-bs-target] {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background-color: #ffffff;
    opacity: 0.6;
}

.hero-carousel .carousel-indicators .active {
    opacity: 1;
    background-color: var(--khqr-accent);
}

.search-bar {
    background: #ffffff;
    border-radius: var(--khqr-radius-lg);
    border: 1px solid rgba(15, 23, 42, 0.1);
    padding: 10px 12px 10px 16px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
}

.search-bar .form-control {
    border: none;
    box-shadow: none;
    background: transparent;
    min-height: 40px;
    padding: 0;
    font-weight: 500;
}

.search-bar .form-control:focus {
    box-shadow: none;
}

.search-submit {
    min-width: 44px;
    justify-content: center;
}

.product-actions {
    gap: 12px;
}

.product-actions .btn {
    min-width: 132px;
    justify-content: center;
}

.product-meta {
    min-height: 32px;
}

.product-actions-cta {
    justify-content: flex-end;
}

.detail-actions {
    gap: 12px;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.cart-qty {
    flex-wrap: wrap;
}

.checkout-card .summary-card {
    width: 100%;
}

.product-hero {
    width: 100%;
}

.cart-toast {
    position: fixed;
    left: 50%;
    bottom: 90px;
    transform: translateX(-50%) translateY(20px);
    background: #150734;
    color: #ffffff;
    padding: 10px 18px;
    border-radius: 999px;
    box-shadow: 0 12px 24px rgba(21, 7, 52, 0.25);
    opacity: 0;
    transition: all 0.2s ease;
    z-index: 30;
}

.cart-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.cart-card {
    background: #ffffff;
    border: 1px solid rgba(21, 7, 52, 0.08);
    border-radius: var(--khqr-radius-lg);
    padding: 12px;
    box-shadow: 0 10px 20px rgba(21, 7, 52, 0.08);
    overflow: hidden;
}

.cart-thumb {
    width: 84px;
    height: 84px;
    object-fit: cover;
    border-radius: var(--khqr-radius-md);
}

.color-thumb {
    width: 22px;
    height: 22px;
    border-radius: 5px;
    object-fit: cover;
}

.qty-btn {
    width: 34px;
    height: 34px;
    border-radius: var(--khqr-radius-sm);
    border: 1px solid rgba(21, 7, 52, 0.12);
    background: #f3f6fb;
}

.qty-input {
    width: 48px;
    text-align: center;
    border: 1px solid rgba(21, 7, 52, 0.12);
    border-radius: var(--khqr-radius-sm);
    height: 34px;
}

.cart-footer {
    position: fixed;
    bottom: 52px;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid rgba(21, 7, 52, 0.08);
    padding: 10px 16px;
    display: flex;
    gap: 12px;
    align-items: center;
    z-index: 25;
}

.mobile-action-bar {
    position: fixed;
    bottom: 52px;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid rgba(21, 7, 52, 0.08);
    padding: 10px 16px;
    display: flex;
    gap: 10px;
    z-index: 25;
}

.mobile-action-bar .btn {
    min-width: 0;
    flex: 1 1 0;
}

.cart-card-actions,
.cart-update-form {
    display: flex;
    align-items: center;
    gap: 8px;
}

.cart-card-actions {
    justify-content: space-between;
}

.cart-total {
    font-weight: 700;
    color: var(--khqr-primary);
}

.hero {
    text-align: center;
    margin-bottom: 48px;
}

.hero .eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.2em;
    font-size: 0.75rem;
    color: var(--khqr-muted);
}

.hero .lead {
    max-width: 640px;
    margin: 0 auto;
    color: var(--khqr-muted);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
}

.section-subtitle {
    color: var(--khqr-muted);
}

.card-khqr {
    background: var(--khqr-card);
    border-radius: var(--khqr-radius-xl);
    border: 1px solid var(--khqr-border);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
}

.product-card {
    display: flex;
    flex-direction: column;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    content-visibility: auto;
    contain-intrinsic-size: 360px 420px;
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 24px 50px rgba(15, 23, 42, 0.15);
}

.product-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    display: block;
}

.product-body {
    flex: 1 1 auto;
}

.badge-soft {
    background: rgba(55, 120, 194, 0.14);
    color: var(--khqr-primary);
    border-radius: 12px;
    padding: 6px 10px;
    font-size: 0.75rem;
    font-weight: 600;
}

.price {
    font-weight: 700;
    color: var(--khqr-primary);
}

.shop-page .form-control,
.shop-page .form-select {
    border: 1px solid rgba(21, 7, 52, 0.12);
    border-radius: var(--khqr-radius-md);
    min-height: 44px;
    padding: 0.68rem 0.9rem;
    box-shadow: none;
}

.shop-page .form-control.form-control-sm,
.shop-page .form-select.form-select-sm {
    min-height: 38px;
    padding: 0.42rem 0.7rem;
    border-radius: 12px;
}

.shop-page .form-control:focus,
.shop-page .form-select:focus {
    border-color: rgba(55, 120, 194, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(55, 120, 194, 0.12);
}

.shop-page .form-check-input {
    accent-color: var(--khqr-primary);
}

.search-bar .form-control,
.search-bar .form-control:focus {
    border: none;
    background: transparent;
    box-shadow: none;
    padding: 0;
    min-height: 40px;
}

.btn-khqr {
    background: var(--khqr-primary);
    color: #ffffff;
    border: 1px solid transparent;
    padding: 10px 18px;
    border-radius: var(--khqr-radius-md);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    letter-spacing: 0.01em;
    box-shadow: 0 10px 20px rgba(40, 85, 154, 0.25);
    transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.btn-khqr:hover {
    background: var(--khqr-primary-dark);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 14px 26px rgba(40, 85, 154, 0.28);
}

.btn-ghost {
    background: rgba(255, 255, 255, 0.92);
    color: var(--khqr-ink);
    border: 1px solid var(--khqr-border);
    padding: 10px 18px;
    border-radius: var(--khqr-radius-md);
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    letter-spacing: 0.01em;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.btn-ghost:hover {
    border-color: rgba(14, 116, 144, 0.4);
    color: #0f172a;
    background: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
}

.btn-khqr.btn-sm,
.btn-ghost.btn-sm,
.shop-page .btn-outline-danger.btn-sm {
    min-height: 38px;
    padding: 8px 12px;
    border-radius: 12px;
}

.shop-page .btn-outline-danger {
    border-radius: var(--khqr-radius-md);
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 18px;
    font-weight: 600;
}

.spec-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 12px;
}

.spec-item {
    padding: 12px 16px;
    border-radius: var(--khqr-radius-md);
    background: #f1f5f9;
}

.choice-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: var(--khqr-radius-md);
    border: 1px solid rgba(21, 7, 52, 0.12);
    background: #ffffff;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
    cursor: pointer;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
}

.choice-chip:hover {
    transform: translateY(-1px);
    border-color: rgba(55, 120, 194, 0.35);
    box-shadow: 0 12px 20px rgba(15, 23, 42, 0.08);
}

.choice-chip input {
    margin: 0;
    flex: 0 0 auto;
}

.choice-chip-media {
    padding-right: 16px;
}

.choice-chip-preview {
    width: 36px;
    height: 36px;
    object-fit: cover;
    border-radius: 8px;
}

.spec-item > span:first-child {
    display: block;
    font-size: 0.75rem;
    color: var(--khqr-muted);
}

.choice-chip span {
    display: inline;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--khqr-ink);
}

.spec-item strong {
    font-size: 0.95rem;
}

.summary-card {
    background: #fff7ed;
    border-radius: 16px;
    border: 1px solid rgba(234, 88, 12, 0.2);
    padding: 16px 20px;
}

.qr-box {
    width: 320px;
    max-width: 100%;
    margin: 0 auto;
    padding: 18px;
    border-radius: var(--khqr-radius-lg);
    background: #ffffff;
    border: 1px solid var(--khqr-border);
    box-shadow: inset 0 0 0 1px rgba(40, 85, 154, 0.08);
}

.timer-number {
    font-size: 2rem;
    font-weight: 700;
    color: #ea580c;
}

.table-khqr th,
.table-khqr td {
    vertical-align: middle;
}

.cart-count {
    background: var(--khqr-ink);
    color: #ffffff;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 0.7rem;
    margin-left: 6px;
    position: absolute;
    top: -6px;
    right: -6px;
}

.filter-bar {
    align-items: center;
}

.filter-bar .btn {
    min-height: 38px;
    border-radius: 12px;
}

.product-cta {
    min-width: 140px;
}

.mobile-tabbar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #ffffff;
    border-top: 1px solid rgba(21, 7, 52, 0.08);
    display: flex;
    justify-content: space-around;
    padding: 8px 12px 10px;
    z-index: 20;
}

.tab-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    color: var(--khqr-muted);
    font-size: 0.75rem;
    position: relative;
}

.tab-item i {
    font-size: 1.2rem;
}

.tab-item.active {
    color: var(--khqr-primary);
}

.tab-badge {
    position: absolute;
    top: -4px;
    right: -10px;
    background: #e11d48;
    color: #fff;
    border-radius: 999px;
    padding: 1px 6px;
    font-size: 0.65rem;
}

@media (max-width: 992px) {
    .hero-carousel .carousel-item {
        height: 260px;
    }

    .hero {
        text-align: center;
    }
}

@media (max-width: 576px) {
    .container {
        padding-left: 16px;
        padding-right: 16px;
    }

    .hero h1 {
        font-size: 1.75rem;
    }

    .hero .lead {
        font-size: 0.95rem;
    }

    .hero-carousel .carousel-item {
        height: 220px;
    }

    .shop-nav .brand-logo {
        height: 34px;
    }

    .shop-nav .brand-text {
        font-size: 0.86rem;
        max-width: 56vw;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .shop-nav .container {
        padding-top: 10px !important;
        padding-bottom: 10px !important;
        gap: 10px;
    }

    .icon-btn {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        flex: 0 0 38px;
    }

    .search-bar {
        padding: 6px 10px;
        flex-direction: row;
        align-items: center;
        gap: 8px;
    }

    .search-bar .form-control {
        font-size: 0.9rem;
    }

    .search-bar .btn {
        padding: 6px 10px;
        font-size: 0.85rem;
        border-radius: 10px;
    }

    .btn-khqr,
    .btn-ghost {
        padding: 8px 16px;
        font-size: 0.9rem;
    }

    .product-card img {
        height: 156px;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 14px;
    }

    .product-grid > [class^="col-"] {
        width: 100%;
    }

    .product-grid .col-md-6,
    .product-grid .col-lg-4 {
        flex: 0 0 auto;
    }

    .product-card h5 {
        font-size: 1rem;
        margin-bottom: 6px;
        text-align: center;
    }

    .product-card .price {
        font-size: 1.05rem;
        width: 100%;
        text-align: center;
    }

    .product-card .badge-soft {
        margin-inline: auto;
    }

    .product-actions .btn {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .product-cta {
        width: 100%;
        min-width: 0;
    }

    .product-detail-card {
        padding: 16px !important;
    }

    .product-hero {
        max-height: 220px !important;
    }

    .product-actions {
        flex-direction: column;
        align-items: center !important;
        text-align: center;
    }

    .product-actions > span {
        font-size: 1rem;
        width: 100%;
        text-align: center;
    }

    .product-meta {
        justify-content: center;
    }

    .product-body {
        padding: 14px 12px !important;
    }

    .product-actions-cta {
        width: 100%;
        justify-content: center;
    }

    .product-actions .btn {
        width: auto;
        min-width: 132px;
        margin-inline: auto;
    }

    .detail-actions .btn {
        width: 100%;
    }

    .spec-grid {
        grid-template-columns: 1fr;
    }

    .choice-chip {
        min-width: 104px;
        justify-content: center;
    }

    .choice-chip-media {
        min-width: 100%;
        justify-content: flex-start;
    }

    .table-khqr {
        font-size: 0.9rem;
    }

    .cart-card {
        padding: 12px;
    }

    .cart-card > .d-flex:first-child {
        gap: 10px !important;
    }

    .cart-thumb {
        width: 82px;
        height: 82px;
        flex: 0 0 82px;
    }

    .cart-card .flex-grow-1 {
        min-width: 0;
    }

    .cart-card .fw-semibold,
    .cart-card .small {
        overflow-wrap: anywhere;
    }

    .cart-card-actions {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 10px;
        align-items: stretch;
    }

    .cart-update-form {
        display: grid;
        grid-template-columns: 44px minmax(48px, 1fr) 44px;
        gap: 8px;
        min-width: 0;
    }

    .cart-update-form .btn {
        grid-column: 1 / -1;
        width: 100%;
    }

    .cart-remove-form,
    .cart-remove-form .btn {
        width: 100%;
        height: 100%;
    }

    .cart-remove-form .btn {
        min-height: 84px;
        padding-inline: 12px;
    }

    .qty-btn,
    .qty-input {
        width: 100%;
        height: 38px;
    }

    .cart-footer {
        bottom: 58px;
        padding: 10px 26px calc(10px + env(safe-area-inset-bottom));
        gap: 14px;
    }

    .cart-footer .cart-total {
        flex: 0 0 auto;
        white-space: nowrap;
        font-size: 0.95rem;
    }

    .mobile-action-bar {
        bottom: 58px;
        padding: 10px 26px calc(10px + env(safe-area-inset-bottom));
    }

    .mobile-action-bar .btn {
        padding-inline: 10px;
        font-size: 0.86rem;
    }

    .cart-actions {
        gap: 16px;
    }

    .cart-actions .summary-card {
        width: 100%;
    }

    .cart-actions .btn {
        width: 100%;
    }

    .shop-page {
        padding-bottom: 116px;
    }

    .mobile-tabbar {
        padding: 8px 20px calc(10px + env(safe-area-inset-bottom));
    }
}
</style>
