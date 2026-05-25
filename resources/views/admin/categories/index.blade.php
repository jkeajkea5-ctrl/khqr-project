@extends('admin.layouts.app')

@section('admin_content')
@php($currentAdmin = auth('admin')->user())
<div class="admin-page-header">
    <div>
        <div class="text-uppercase small admin-muted">Category</div>
        <h2 class="fw-semibold mb-0">Manage categories</h2>
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
                        <th class="ps-4">Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Created</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $category->name }}</td>
                        <td class="text-muted">{{ $category->slug }}</td>
                        <td>{{ $category->products_count }}</td>
                        <td class="text-muted">{{ $category->created_at->format('d M Y') }}</td>
                        <td class="text-end pe-4">
                            <div class="admin-actions">
                                @if($currentAdmin?->canManageCategories())
                                    <a class="admin-quick-btn admin-quick-btn-edit" href="{{ route('admin.categories.edit', $category->id) }}">
                                        <i class="bi bi-pencil"></i>
                                        Edit
                                    </a>
                                @endif
                                @if($currentAdmin?->canDeleteCategories())
                                    <form class="d-inline" method="POST" action="{{ route('admin.categories.destroy', $category->id) }}"
                                          onsubmit="return confirm('Delete this category? Products in it will become uncategorized.')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-quick-btn admin-quick-btn-delete" type="submit">
                                            <i class="bi bi-trash"></i>
                                            Delete
                                        </button>
                                    </form>
                                @elseif(!$currentAdmin?->canManageCategories())
                                    <span class="badge text-bg-light border">Read only</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">No categories yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-md-none p-3">
            @forelse($categories as $category)
                <div class="admin-card p-3 mb-3">
                    <div class="fw-semibold">{{ $category->name }}</div>
                    <div class="admin-muted small">Slug: {{ $category->slug }}</div>
                    <div class="admin-muted small">Products: {{ $category->products_count }}</div>
                    <div class="admin-muted small">Created: {{ $category->created_at->format('d M Y') }}</div>

                    <div class="d-flex gap-2 mt-3">
                        @if($currentAdmin?->canManageCategories())
                            <a class="admin-quick-btn admin-quick-btn-edit {{ $currentAdmin?->canDeleteCategories() ? 'flex-fill' : 'w-100' }}" href="{{ route('admin.categories.edit', $category->id) }}">
                                <i class="bi bi-pencil"></i>
                                Edit
                            </a>
                        @endif
                        @if($currentAdmin?->canDeleteCategories())
                            <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}" class="{{ $currentAdmin?->canManageCategories() ? 'flex-fill' : 'w-100' }}" onsubmit="return confirm('Delete this category? Products in it will become uncategorized.')">
                                @csrf
                                @method('DELETE')
                                <button class="admin-quick-btn admin-quick-btn-delete w-100" type="submit">
                                    <i class="bi bi-trash"></i>
                                    Delete
                                </button>
                            </form>
                        @elseif(!$currentAdmin?->canManageCategories())
                            <div class="w-100">
                                <span class="badge text-bg-light border">Read only</span>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">No categories yet.</div>
            @endforelse
        </div>

        @if($currentAdmin?->canManageCategories())
            <div class="d-flex justify-content-end mt-3 px-3 pb-3">
                <a href="{{ route('admin.categories.create') }}" class="admin-quick-btn admin-quick-btn-add">
                    <i class="bi bi-plus"></i>
                    Add Category
                </a>
            </div>
        @endif
    </div>
</div>

<div class="mt-3">{{ $categories->links() }}</div>
@endsection
