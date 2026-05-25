@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-card overflow-hidden">
    <div class="bg-dark text-white p-4">
        <h3 class="mb-0">Edit Category</h3>
        <small class="text-white-50">{{ $category->name }}</small>
    </div>

    <div class="p-4 p-md-5">
        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Category name</label>
                <input name="name" class="form-control rounded-3" value="{{ old('name', $category->name) }}" required>
                <div class="form-text">Current slug: {{ $category->slug }}</div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary rounded-3 w-50">Back</a>
                <button class="btn btn-primary rounded-3 w-50">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
