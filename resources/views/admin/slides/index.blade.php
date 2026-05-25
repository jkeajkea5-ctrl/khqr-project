@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-page-header">
    <div>
        <div class="text-uppercase small admin-muted">Slides</div>
        <h2 class="fw-semibold mb-0">Homepage slideshow</h2>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="admin-card p-4">
    <div class="table-responsive admin-table-responsive d-none d-md-block">
        <table class="table align-middle mb-0 admin-table-compact">
            <thead class="admin-muted small text-uppercase">
                <tr>
                    <th>Preview</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slides as $s)
                <tr>
                    <td style="width:120px;">
                        <img src="{{ $s->image }}" alt="Slide" style="width:100px;height:60px;object-fit:cover;border-radius:12px;">
                    </td>
                    <td>{{ $s->position }}</td>
                    <td>
                        <span class="badge {{ $s->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $s->is_active ? 'Active' : 'Hidden' }}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="admin-actions">
                            <a href="{{ route('admin.slides.edit', $s->id) }}" class="admin-quick-btn admin-quick-btn-edit">
                                <i class="bi bi-pencil"></i>
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.slides.destroy', $s->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="admin-quick-btn admin-quick-btn-delete" type="submit" onclick="return confirm('Delete this slide?')">
                                    <i class="bi bi-trash"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center admin-muted py-4">No slides yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-md-none">
        @foreach($slides as $s)
            <div class="admin-card p-3 mb-3">
                <div class="d-flex gap-3 align-items-center">
                    <img src="{{ $s->image }}" alt="Slide" style="width:96px;height:64px;object-fit:cover;border-radius:12px;">
                    <div class="flex-grow-1">
                        <div class="admin-muted small">Position: {{ $s->position }}</div>
                        <span class="badge {{ $s->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $s->is_active ? 'Active' : 'Hidden' }}
                        </span>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('admin.slides.edit', $s->id) }}" class="admin-quick-btn admin-quick-btn-edit flex-fill">
                        <i class="bi bi-pencil"></i>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('admin.slides.destroy', $s->id) }}" class="w-50">
                        @csrf
                        @method('DELETE')
                        <button class="admin-quick-btn admin-quick-btn-delete w-100" type="submit" onclick="return confirm('Delete this slide?')">
                            <i class="bi bi-trash"></i>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div class="d-flex justify-content-end mt-3">
        <a href="{{ route('admin.slides.create') }}" class="admin-quick-btn admin-quick-btn-add">
            <i class="bi bi-plus"></i>
            Add Slide
        </a>
    </div>
</div>
@endsection
