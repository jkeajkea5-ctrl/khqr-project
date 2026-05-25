@extends('admin.layouts.app')

@section('admin_content')
<div class="card border-0 shadow-lg rounded-4 overflow-hidden">
    <div class="bg-dark text-white p-4">
        <h3 class="mb-0">Edit Product</h3>
        <small class="text-white-50">{{ $product->name }}</small>
    </div>

    <div class="card-body p-4 p-md-5">
        @if($errors->any())
            <div class="alert alert-danger rounded-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.products.update',$product->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input name="name" class="form-control rounded-3" value="{{ old('name',$product->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Category</label>
                <select name="category_id" class="form-select rounded-3">
                    <option value="">Select category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ (string) old('category_id', $product->category_id) === (string) $category->id ? 'selected' : '' }}>
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
                <textarea name="description" class="form-control rounded-3" rows="4">{{ old('description',$product->description) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Price (USD)</label>
                <input type="number" name="price" class="form-control rounded-3" value="{{ old('price',$product->price) }}" min="0.01" step="0.01" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Sizes</label>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['XS','S','M','L','XL','XXL'] as $sz)
                        <label class="btn btn-outline-secondary rounded-3 px-3 py-2">
                            <input type="checkbox" name="sizes[]" value="{{ $sz }}" class="form-check-input me-2"
                                {{ in_array($sz, old('sizes', $product->sizes ?? [])) ? 'checked' : '' }}>
                            {{ $sz }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Colors (name + image)</label>
                @if(!empty($product->colors))
                    <div class="mb-2 text-muted small">Current colors:</div>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        @foreach($product->colors as $c)
                            <div class="d-flex align-items-center gap-2 border rounded-3 p-2">
                                @if(!empty($c['image']))
                                    <img src="{{ $c['image'] }}" alt="{{ $c['name'] ?? 'Color' }}" style="width:40px;height:40px;object-fit:cover;border-radius:8px;">
                                @endif
                                <span>{{ $c['name'] ?? 'Color' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

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
                <label class="form-label fw-semibold">Replace Image (optional)</label>
                <input type="file" name="image" class="form-control rounded-3" accept="image/*">
                <div class="mt-3">
                    <div class="text-muted small mb-1">Current:</div>
                    <img src="{{ $product->image }}" class="rounded-3 border" style="width:160px;height:110px;object-fit:cover;">
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary rounded-3 w-50">Back</a>
                <button class="btn btn-primary rounded-3 w-50">Update</button>
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
