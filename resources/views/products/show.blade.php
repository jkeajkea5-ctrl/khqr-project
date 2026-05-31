@extends('layouts.app')

@section('styles')
@include('products._styles')
@endsection

@section('content')
@include('products._nav', ['showBack' => true, 'useBrandLogo' => false])
<div class="shop-page">
    <div class="container py-5">
        <div class="mb-4">
            <h2 class="section-title">Product details</h2>
            <p class="section-subtitle">Review size and color before adding to cart.</p>
        </div>

        <div class="card-khqr p-4 p-md-5 product-detail-card">
            <div class="row align-items-center g-4">
                <div class="col-md-5 text-center">
                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-fluid rounded-4 product-hero" style="max-height:280px;object-fit:cover;">
                </div>
                <div class="col-md-7">
                    <h3 class="fw-semibold">{{ $product->name }}</h3>
                    <p class="text-muted">{{ $product->description }}</p>

                    <form id="addToCartForm" action="{{ route('cart.add', $product->id) }}" method="POST" class="mt-3">
                        @csrf

                        <div class="spec-grid my-3">
                            <div class="spec-item">
                                <span>Size</span>
                                @php
                                    $sizes = $product->sizes ?? ($product->size ? [$product->size] : []);
                                @endphp
                                @if(!empty($sizes))
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @foreach($sizes as $sz)
                                            <label class="choice-chip">
                                                <input type="radio" name="size" value="{{ $sz }}" class="form-check-input" required {{ $loop->first ? 'checked' : '' }}>
                                                <span>{{ $sz }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small mt-2">No size options.</div>
                                    <input type="hidden" name="size" value="N/A">
                                @endif
                            </div>
                            <div class="spec-item">
                                <span>Color</span>
                                @php
                                    $colors = $product->colors ?? ($product->color ? [['name' => $product->color, 'image' => $product->image]] : []);
                                @endphp
                                @if(!empty($colors))
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @foreach($colors as $c)
                                            <label class="choice-chip choice-chip-media">
                                                <input type="radio" name="color" value="{{ $c['name'] ?? 'Color' }}" class="form-check-input" required {{ $loop->first ? 'checked' : '' }}>
                                                @if(!empty($c['image']))
                                                    <img src="{{ $c['image'] }}" alt="{{ $c['name'] ?? 'Color' }}" class="choice-chip-preview">
                                                @endif
                                                <span>{{ $c['name'] ?? 'Color' }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted small mt-2">No color options.</div>
                                    <input type="hidden" name="color" value="N/A">
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3 mt-3">
                            <span class="price fs-4 me-2">${{ number_format($product->price, 2) }}</span>
                            <a href="{{ route('home') }}" class="btn btn-ghost d-none d-md-inline-flex">
                                <i class="bi bi-arrow-left"></i>
                                Back
                            </a>
                        </div>

                        <div class="detail-actions d-none d-md-grid mt-4">
                            <button type="submit" class="btn btn-ghost">
                                <i class="bi bi-bag-plus"></i>
                                Add to cart
                            </button>
                            <button type="submit" class="btn btn-khqr" data-redirect="cart">
                                <i class="bi bi-lightning-charge"></i>
                                Buy now
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="cartToast" class="cart-toast">Added to cart</div>
<div class="mobile-action-bar d-md-none mb-2">
    <button class="btn btn-ghost w-100" type="submit" form="addToCartForm">
        <i class="bi bi-bag-plus"></i>
        Add to cart
    </button>
    <button class="btn btn-khqr w-100" type="submit" form="addToCartForm" data-redirect="cart">
        <i class="bi bi-lightning-charge"></i>
        Buy now
    </button>
</div>
@endsection

@section('scripts')
<script>
const addForm = document.getElementById('addToCartForm');
const toast = document.getElementById('cartToast');
if (addForm) {
    addForm.addEventListener('submit', async (e) => {
        const submitter = e.submitter;
        const shouldRedirectCart = submitter && submitter.dataset.redirect === 'cart';
        e.preventDefault();
        const formData = new FormData(addForm);
        const res = await fetch(addForm.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        if (res.ok) {
            const data = await res.json();
            const countEl = document.querySelector('.cart-count');
            if (countEl && data.count !== undefined) countEl.textContent = data.count;
            const tabBadge = document.querySelector('.tab-badge');
            if (tabBadge && data.count !== undefined) tabBadge.textContent = data.count;
            if (shouldRedirectCart) {
                window.location.href = "{{ route('cart.index') }}";
                return;
            }
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 1800);
        } else {
            addForm.submit();
        }
    });
}
</script>
@endsection
