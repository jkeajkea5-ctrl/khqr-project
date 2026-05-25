@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-card overflow-hidden">
    <div class="p-4" style="background: linear-gradient(135deg, rgba(55,120,194,0.18), rgba(40,85,154,0.08)), rgba(21,7,52,0.9));">
        <div class="text-uppercase small admin-muted">Category</div>
        <h3 class="mb-0 text-white">Create new category</h3>
        <small class="text-white-50">Group products so customers can filter them easily</small>
    </div>

    <div class="p-4 p-md-5">
        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Category name</label>
                <input name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
                <div class="form-text">The slug will be generated automatically from this name.</div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-3 w-50">Cancel</a>
                <button class="btn btn-success rounded-3 w-50">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
