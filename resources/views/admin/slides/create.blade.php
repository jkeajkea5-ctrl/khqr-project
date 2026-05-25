@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-card overflow-hidden">
    <div class="p-4" style="background: linear-gradient(135deg, rgba(55,120,194,0.18), rgba(40,85,154,0.08)), rgba(21,7,52,0.9));">
        <div class="text-uppercase small admin-muted">Slides</div>
        <h3 class="mb-0 text-white">Create slide</h3>
        <small class="text-white-50">Upload a hero image for the homepage slideshow</small>
    </div>

    <div class="p-4 p-md-5">
        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.slides.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Link (optional)</label>
                    <input name="link" class="form-control rounded-3" value="{{ old('link') }}" placeholder="/product/1">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Position</label>
                    <input type="number" name="position" class="form-control rounded-3" value="{{ old('position', 0) }}" min="0">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Active</label>
                    <select name="is_active" class="form-select rounded-3">
                        <option value="1" selected>Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label fw-semibold">Image</label>
                <input type="file" name="image" class="form-control rounded-3" accept="image/*" required>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="{{ route('admin.slides.index') }}" class="btn btn-outline-light w-50">Cancel</a>
                <button class="btn btn-khqr w-50">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection


