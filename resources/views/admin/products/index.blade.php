@extends('admin.layouts.app')

@section('admin_content')
@php($currentAdmin = auth('admin')->user())
<div class="admin-page-header">
    <div>
        <div class="text-uppercase small admin-muted">Products</div>
        <h2 class="fw-semibold mb-0">Catalog</h2>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
@endif

<div class="admin-card p-0">
    <div class="card-body p-0">
    <div class="table-responsive admin-table-responsive d-none d-md-block">
        <table class="table align-middle mb-0 admin-table-compact">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Sizes</th>
                        <th>Colors</th>
                        <th>Created</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $p)
                    <tr>
                        <td class="ps-4" style="width:90px;">
                            <img src="{{ $p->image }}" class="rounded-3 border" style="width:70px;height:50px;object-fit:cover;">
                        </td>
                        <td class="fw-semibold">{{ $p->name }}</td>
                        <td class="text-muted">{{ $p->category?->name ?? '-' }}</td>
                        <td class="text-success fw-bold">${{ number_format($p->price, 2) }}</td>
                        <td class="text-muted">{{ !empty($p->sizes) ? implode(', ', $p->sizes) : ($p->size ?? '-') }}</td>
                        <td class="text-muted">{{ !empty($p->colors) ? collect($p->colors)->pluck('name')->implode(', ') : ($p->color ?? '-') }}</td>
                        <td class="text-muted">{{ $p->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-4">
                            <div class="admin-actions">
                                @if($currentAdmin?->canManageProducts())
                                    <a class="admin-quick-btn admin-quick-btn-edit" href="{{ route('admin.products.edit',$p->id) }}">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>
                                @endif

                                @if($currentAdmin?->canDeleteProducts())
                                    <form class="d-inline" method="POST" action="{{ route('admin.products.destroy',$p->id) }}"
                                          onsubmit="return confirm('Delete this product?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-quick-btn admin-quick-btn-delete" type="submit">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                @elseif(!$currentAdmin?->canManageProducts())
                                    <span class="badge text-bg-light border">Read only</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">No products.</td></tr>
                @endforelse
                </tbody>
        </table>
    </div>
    <div class="d-md-none p-3">
            @foreach($products as $p)
                <div class="admin-card p-3 mb-3">
                    <div class="d-flex gap-3">
                        <img src="{{ $p->image }}" style="width:72px;height:72px;object-fit:cover;border-radius:12px;">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $p->name }}</div>
                            <div class="admin-muted small">Category: {{ $p->category?->name ?? '-' }}</div>
                            <div class="admin-muted small">${{ number_format($p->price, 2) }}</div>
                            <div class="admin-muted small">Sizes: {{ !empty($p->sizes) ? implode(', ', $p->sizes) : ($p->size ?? '-') }}</div>
                            <div class="admin-muted small">Colors: {{ !empty($p->colors) ? collect($p->colors)->pluck('name')->implode(', ') : ($p->color ?? '-') }}</div>
                        </div>
                    </div>
                    @if($currentAdmin?->canManageProducts() || $currentAdmin?->canDeleteProducts())
                        <div class="d-flex gap-2 mt-3">
                            @if($currentAdmin?->canManageProducts())
                                <a class="admin-quick-btn admin-quick-btn-edit {{ $currentAdmin?->canDeleteProducts() ? 'flex-fill' : 'w-100' }}" href="{{ route('admin.products.edit',$p->id) }}">
                                    <i class="bi bi-pencil"></i>
                                    Edit
                                </a>
                            @endif
                            @if($currentAdmin?->canDeleteProducts())
                                <form method="POST" action="{{ route('admin.products.destroy',$p->id) }}" class="{{ $currentAdmin?->canManageProducts() ? 'flex-fill' : 'w-100' }}" onsubmit="return confirm('Delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-quick-btn admin-quick-btn-delete w-100" type="submit">
                                        <i class="bi bi-trash"></i>
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    @else
                        <div class="mt-3">
                            <span class="badge text-bg-light border">Read only</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        @if($currentAdmin?->canManageProducts())
            <div class="d-flex justify-content-end mt-3 px-3 pb-3">
                <a href="{{ route('admin.products.create') }}" class="admin-quick-btn admin-quick-btn-add">
                    <i class="bi bi-plus"></i>
                    Add Product
                </a>
            </div>
        @endif
    </div>
</div>

<div class="mt-3">{{ $products->links() }}</div>
@endsection
