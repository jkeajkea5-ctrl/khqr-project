@extends('layouts.app')

@section('styles')
@include('products._styles')
@endsection

@section('scripts')
<script>
function stepQty(btn, delta) {
    const input = btn.parentElement.querySelector('.qty-input');
    const current = parseInt(input.value || '0', 10);
    const next = Math.max(0, Math.min(99, current + delta));
    input.value = next;
}

document.addEventListener('DOMContentLoaded', () => {
    const formatMoney = (value) => {
        return '$' + value.toFixed(2);
    };

    const updateSelectedTotal = () => {
        const rows = document.querySelectorAll('[data-cart-row]');
        let total = 0;
        rows.forEach((row) => {
            if (row.offsetParent === null) return;
            const cb = row.querySelector('.cart-select');
            if (!cb || !cb.checked) return;
            const price = parseFloat(row.dataset.price || '0');
            const qtyInput = row.querySelector('input[name="qty"]');
            const qty = qtyInput ? parseInt(qtyInput.value || '0', 10) : 0;
            total += price * qty;
        });

        const totalEls = document.querySelectorAll('[data-cart-total]');
        totalEls.forEach((el) => {
            el.textContent = formatMoney(total);
        });

        const checkoutBtns = document.querySelectorAll('.checkout-form button[type="submit"]');
        checkoutBtns.forEach((btn) => {
            btn.disabled = total === 0;
        });
    };

    const forms = document.querySelectorAll('.checkout-form');
    forms.forEach((form) => {
        form.addEventListener('submit', (e) => {
            const checked = Array.from(document.querySelectorAll('.cart-select:checked'));
            const existing = form.querySelectorAll('input[name="selected[]"]');
            existing.forEach((el) => el.remove());

            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one item to go to pay.');
                return;
            }

            checked.forEach((cb) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'selected[]';
                hidden.value = cb.value;
                form.appendChild(hidden);
            });
        });
    });

    document.querySelectorAll('.cart-select').forEach((cb) => {
        cb.addEventListener('change', updateSelectedTotal);
    });

    document.querySelectorAll('input[name="qty"]').forEach((input) => {
        input.addEventListener('change', updateSelectedTotal);
        input.addEventListener('input', updateSelectedTotal);
    });

    updateSelectedTotal();
});
</script>
@endsection

@section('content')
@include('products._nav')
<div class="shop-page">
    <div class="container py-5">
        <div class="mb-4">
            <h2 class="section-title">Your cart</h2>
            <p class="section-subtitle">Review items and adjust quantities.</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @php
            $total = collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);
        @endphp

        <div class="card-khqr p-4">
            @if(empty($cart))
                <p class="text-muted mb-0">Your cart is empty.</p>
            @else
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-khqr align-middle">
                        <thead class="text-muted">
                            <tr>
                                <th class="text-center" style="width:52px;">Select</th>
                                <th>Item</th>
                                <th>Specs</th>
                                <th class="text-end">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($cart as $item)
                            <tr data-cart-row data-price="{{ $item['price'] }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input cart-select" value="{{ $item['key'] }}" checked>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" style="width:64px;height:64px;object-fit:cover;border-radius:12px;">
                                        <div>
                                            <div class="fw-semibold">{{ $item['name'] }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-muted small">Size: {{ $item['size'] ?? 'N/A' }}</div>
                                    <div class="text-muted small d-flex align-items-center gap-2">
                                        @if(!empty($item['color_image']))
                                            <img src="{{ $item['color_image'] }}" alt="{{ $item['color'] ?? 'Color' }}" style="width:24px;height:24px;object-fit:cover;border-radius:6px;">
                                        @endif
                                        Color: {{ $item['color'] ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="text-end">${{ number_format($item['price'], 2) }}</td>
                                <td class="text-center" style="min-width:140px;">
                                    <form action="{{ route('cart.update', $item['key']) }}" method="POST" class="d-flex justify-content-center gap-2 cart-qty">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="qty" class="form-control form-control-sm" value="{{ $item['qty'] }}" min="0" max="99" style="width:70px;">
                                        <button class="btn btn-ghost btn-sm" type="submit">Update</button>
                                    </form>
                                </td>
                                <td class="text-end">${{ number_format($item['price'] * $item['qty'], 2) }}</td>
                                <td class="text-end">
                                    <form action="{{ route('cart.remove', $item['key']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-md-none">
                    @foreach($cart as $item)
                        <div class="cart-card mb-3" data-cart-row data-price="{{ $item['price'] }}">
                            <div class="d-flex gap-3">
                                <div class="pt-1">
                                    <input type="checkbox" class="form-check-input cart-select" value="{{ $item['key'] }}" checked>
                                </div>
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="cart-thumb">
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $item['name'] }}</div>
                                    <div class="text-muted small">Size: {{ $item['size'] ?? 'N/A' }}</div>
                                    <div class="text-muted small d-flex align-items-center gap-2">
                                        @if(!empty($item['color_image']))
                                            <img src="{{ $item['color_image'] }}" alt="{{ $item['color'] ?? 'Color' }}" class="color-thumb">
                                        @endif
                                        Color: {{ $item['color'] ?? 'N/A' }}
                                    </div>
                                    <div class="price mt-2">${{ number_format($item['price'], 2) }}</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <form action="{{ route('cart.update', $item['key']) }}" method="POST" class="d-flex align-items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <button type="button" class="qty-btn" onclick="stepQty(this, -1)">-</button>
                                    <input type="number" name="qty" class="qty-input" value="{{ $item['qty'] }}" min="0" max="99">
                                    <button type="button" class="qty-btn" onclick="stepQty(this, 1)">+</button>
                                    <button class="btn btn-ghost btn-sm" type="submit">Update</button>
                                </form>
                                <form action="{{ route('cart.remove', $item['key']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 cart-actions d-none d-md-flex">
                    <div class="summary-card">
                        <div class="text-muted small">Total</div>
                        <div class="fs-4 fw-semibold" data-cart-total>${{ number_format($total, 2) }}</div>
                    </div>
                    <div class="d-flex gap-2 mt-3 mt-md-0">
                        <a href="{{ route('home') }}" class="btn btn-ghost">Continue shopping</a>
                        <a href="{{ route('cart.clear') }}" class="btn btn-outline-danger">Clear cart</a>
                        <form action="{{ route('checkout.cart') }}" method="POST" class="checkout-form">
                            @csrf
                            <button class="btn btn-khqr" type="submit">Go To Pay</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if(!empty($cart))
<div class="cart-footer d-md-none">
    <div class="cart-total">USD <span data-cart-total>${{ number_format($total, 2) }}</span></div>
    <form action="{{ route('checkout.cart') }}" method="POST" class="flex-grow-1 checkout-form">
        @csrf
        <button class="btn btn-khqr w-100" type="submit">Go To Pay</button>
    </form>
</div>
@endif
@endsection
