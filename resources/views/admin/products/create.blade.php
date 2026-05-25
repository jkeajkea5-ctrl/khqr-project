@extends('admin.layouts.app')

@section('admin_content')
<div class="admin-card overflow-hidden">
    <div class="p-4" style="background: linear-gradient(135deg, rgba(55,120,194,0.18), rgba(40,85,154,0.08)), rgba(21,7,52,0.9));">
        <div class="text-uppercase small admin-muted">Product Studio</div>
        <h3 class="mb-0 text-white">Create new product</h3>
        <small class="text-white-50">Add catalog details, sizes, and color images</small>
    </div>

    <div class="p-4 p-md-5">
        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input name="name" class="form-control rounded-3" value="{{ old('name') }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select rounded-3">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @if($categories->isEmpty())
                    <div class="form-text text-danger">No categories yet. <a href="{{ route('admin.categories.create') }}">Create a category first</a>.</div>
                @endif
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control rounded-3" rows="4">{{ old('description') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Price (USD)</label>
                <input type="number" name="price" class="form-control rounded-3" value="{{ old('price') }}" min="0.01" step="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sizes</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['XS','S','M','L','XL','XXL'] as $sz)
                        <label class="btn btn-outline-secondary rounded-3 px-3 py-2">
                            <input type="checkbox" name="sizes[]" value="{{ $sz }}" class="form-check-input me-2">
                            {{ $sz }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Colors (name + image)</label>
                <div id="colorRows" class="d-grid gap-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input name="colors[name][]" class="form-control rounded-3" placeholder="Color name">
                        </div>
                        <div class="col-md-8">
                            <input type="file" name="colors[image][]" class="form-control rounded-3" accept="image/*">
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary rounded-3 mt-2" onclick="addColorRow()">+ Add another color</button>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Image</label>
                <input type="file" name="image" class="form-control rounded-3" accept="image/*" required>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-3 w-50">Cancel</a>
                <button class="btn btn-success rounded-3 w-50">Save</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
function addColorRow() {
    const rows = document.getElementById('colorRows');
    const row = document.createElement('div');
    row.className = 'row g-2';
    row.innerHTML = `
        <div class="col-md-4">
            <input name="colors[name][]" class="form-control rounded-3" placeholder="Color name">
        </div>
        <div class="col-md-8">
            <input type="file" name="colors[image][]" class="form-control rounded-3" accept="image/*">
        </div>
    `;
    rows.appendChild(row);
}
</script>
@endsection
@endsection

