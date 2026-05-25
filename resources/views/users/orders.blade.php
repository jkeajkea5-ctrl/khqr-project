@extends('layouts.app')

@section('styles')
@include('products._styles')
@endsection

@section('content')
@include('products._nav')
<div class="shop-page">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <div class="eyebrow">My orders</div>
                <h2 class="fw-semibold">Order history</h2>
            </div>
            <a href="{{ route('home') }}" class="btn btn-ghost">Back</a>
        </div>

        <div class="card-khqr p-4">
            @forelse($orders as $order)
                <div class="border-bottom py-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fw-semibold">Order #{{ $order->id }}</div>
                            <div class="text-muted small">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="price">${{ number_format($order->amount, 2) }}</div>
                            @php
                                $statusClass = match ($order->status) {
                                    'PAID' => 'bg-success',
                                    'FAILED' => 'bg-danger',
                                    'PENDING' => 'bg-warning text-dark',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $order->status }}</span>
                        </div>
                    </div>
                    @if(!empty($order->items))
                        <div class="mt-2 text-muted small">
                            @foreach($order->items as $item)
                                <div>{{ $item['name'] }} x{{ $item['qty'] }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-4">No orders yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
