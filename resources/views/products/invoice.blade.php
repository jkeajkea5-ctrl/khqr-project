@extends('layouts.app')

@section('styles')
@include('products._styles')
<style>
@media print {
    nav, .btn, .shop-nav { display: none !important; }
    #invoiceArea { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>
@endsection

@section('content')
@include('products._nav', ['showMobileTabbar' => false])
<div class="shop-page">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('home') }}" class="btn btn-ghost">Back</a>
        </div>

        <div class="card-khqr" id="invoiceArea">
            <div class="bg-dark text-white p-4 rounded-top">
                <div class="d-flex justify-content-between">
                    <div>
                        <h3 class="mb-0">INVOICE</h3>
                        <small class="text-white-50">Payment successful</small>
                    </div>
                    <div class="text-end">
                        <div class="fw-semibold">Order #{{ $order->id }}</div>
                        <small class="text-white-50">{{ $order->created_at->format('d M Y, h:i A') }}</small>
                    </div>
                </div>
            </div>

            <div class="p-4 p-md-5">
                @php
                    $currencyCode = strtoupper((string) ($order->currency ?? 'USD'));
                    $formatAmount = function (float $amount) use ($currencyCode): string {
                        return $currencyCode === 'KHR'
                            ? number_format($amount, 0).' KHR'
                            : '$'.number_format($amount, 2);
                    };
                @endphp
                @if($order->bill_number)
                    <p class="text-muted mb-1">Reference: {{ $order->bill_number }}</p>
                @endif
                <p class="text-muted mb-1">QR MD5: {{ $order->md5 }}</p>
                <p class="mb-4"><span class="badge bg-success">PAID</span></p>

                @if($order->user)
                    <div class="mb-4">
                        <div class="fw-semibold">Customer</div>
                        <div class="text-muted small">Name: {{ $order->user->name ?? 'N/A' }}</div>
                        <div class="text-muted small">Phone: {{ $order->user->phone ?? 'N/A' }}</div>
                        <div class="text-muted small">User ID: {{ $order->user->id }}</div>
                    </div>
                @endif

                @php
                    $items = $order->items ?? [];
                @endphp

                @if(!empty($items))
                    <div class="table-responsive">
                        <table class="table table-khqr">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Specs</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item['name'] }}</div>
                                            <div class="text-muted small">ID: {{ $item['id'] ?? 'N/A' }}</div>
                                        </td>
                                        <td>
                                            <div class="text-muted small">Size: {{ $item['size'] ?? 'N/A' }}</div>
                                            <div class="text-muted small">Color: {{ $item['color'] ?? 'N/A' }}</div>
                                        </td>
                                        <td class="text-end">{{ $formatAmount((float) $item['price']) }}</td>
                                        <td class="text-center">{{ $item['qty'] }}</td>
                                        <td class="text-end">{{ $formatAmount((float) ($item['price'] * $item['qty'])) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <h5 class="fw-bold">{{ $order->display_product_name }}</h5>
                @endif

                <div class="summary-card mt-3">
                    <div class="text-muted small">Total</div>
                    <div class="fs-4 fw-semibold">{{ $formatAmount((float) $order->amount) }}</div>
                </div>

                <div class="text-center text-muted mt-4">
                    Thank you for your purchase.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
