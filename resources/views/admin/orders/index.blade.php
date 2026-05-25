@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-page-header">
    <div>
        <div class="text-uppercase small admin-muted">Orders</div>
        <h2 class="fw-semibold mb-0">All orders</h2>
    </div>
    <span class="admin-muted small">{{ now()->format('d M Y') }}</span>
</div>

<div class="admin-card p-0">
    <div class="card-body p-0">
        <div class="table-responsive d-none d-md-block">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>MD5</th>
                        <th class="pe-4">Time</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($orders as $o)
                    <tr>
                        <td class="ps-4">{{ $o->id }}</td>
                        <td class="fw-semibold">{{ $o->display_product_name }}</td>
                        <td class="text-success fw-bold">{{ number_format($o->amount, $o->currency === 'USD' ? 2 : 0) }} {{ $o->currency }}</td>
                        <td>
                            @if($o->status === 'PAID')
                                <span class="badge bg-success">PAID</span>
                            @elseif($o->status === 'FAILED')
                                <span class="badge bg-danger">FAILED</span>
                            @else
                                <span class="badge bg-warning text-dark">PENDING</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $o->md5 }}</td>
                        <td class="pe-4">
                            <div class="small text-muted">Created: {{ $o->created_at->format('d M Y, h:i A') }}</div>
                            @if($o->paid_at)
                                <div class="small">Paid: {{ $o->paid_at->format('d M Y, h:i A') }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">No orders yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-md-none p-3">
            @foreach($orders as $o)
                <div class="admin-card p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <div class="fw-semibold">#{{ $o->id }} - {{ $o->display_product_name }}</div>
                        <div class="fw-semibold text-primary">{{ number_format($o->amount, $o->currency === 'USD' ? 2 : 0) }} {{ $o->currency }}</div>
                    </div>
                    <div class="admin-muted small mt-1">MD5: {{ $o->md5 }}</div>
                    <div class="admin-muted small">Created: {{ $o->created_at->format('d M Y, h:i A') }}</div>
                    @if($o->paid_at)
                        <div class="admin-muted small">Paid: {{ $o->paid_at->format('d M Y, h:i A') }}</div>
                    @endif
                    <div class="mt-2">
                        @if($o->status === 'PAID')
                            <span class="badge bg-success">PAID</span>
                        @elseif($o->status === 'FAILED')
                            <span class="badge bg-danger">FAILED</span>
                        @else
                            <span class="badge bg-warning text-dark">PENDING</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="mt-3">{{ $orders->links() }}</div>
@endsection
