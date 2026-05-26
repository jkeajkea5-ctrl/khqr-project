@extends('layouts.app')

@section('styles')
@include('products._styles')
@endsection

@section('content')
@include('products._nav', ['useBrandLogo' => false])
<div class="shop-page">
    <div class="container py-5">

        @if(isset($slides) && $slides->count())
        <div id="heroCarousel" class="carousel slide hero-carousel mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                @foreach($slides as $slide)
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                    @if($slide->link)
                        <a href="{{ $slide->link }}">
                            <img src="{{ $slide->image }}" alt="{{ $slide->title ?? 'Slide' }}">
                        </a>
                    @else
                        <img src="{{ $slide->image }}" alt="{{ $slide->title ?? 'Slide' }}">
                    @endif
                </div>
                @endforeach
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
        @endif

        <form class="search-bar d-flex align-items-center gap-2 mb-4" method="GET" action="{{ route('home') }}">
            <i class="bi bi-search text-muted"></i>
            <input type="text" name="q" class="form-control" placeholder="Search products..." value="{{ request('q') }}">
            @if(request()->filled('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <button class="btn btn-khqr btn-sm search-submit" type="submit">
                <i class="bi bi-search"></i>
                <span class="d-none d-sm-inline">Search</span>
            </button>
        </form>

        @if(isset($categories) && $categories->count())
        <div class="filter-bar d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('home', array_filter(['q' => request('q')])) }}" class="btn {{ request('category') ? 'btn-ghost' : 'btn-khqr' }} btn-sm">
                All
            </a>
            @foreach($categories as $category)
                <a href="{{ route('home', array_filter(['category' => $category->slug, 'q' => request('q')])) }}"
                   class="btn {{ request('category') === $category->slug ? 'btn-khqr' : 'btn-ghost' }} btn-sm">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
        @endif

        <div class="mb-4">
            @if(isset($selectedCategory) && $selectedCategory)
                <div class="text-muted small mt-1">Showing category: {{ $selectedCategory->name }}</div>
            @endif
        </div>

        <div class="row g-4 product-grid">
            @forelse($products as $product)
            <div class="col-lg-4 col-md-6">
                <div class="card-khqr product-card h-100">
                    <img src="{{ $product->image }}" alt="{{ $product->name }}">
                    <div class="p-4 d-flex flex-column product-body">
                        <h5 class="fw-semibold">{{ $product->name }}</h5>
                        <p class="text-muted small flex-grow-1 d-none d-md-block">{{ \Illuminate\Support\Str::limit($product->description, 90) }}</p>

                        <div class="d-flex flex-wrap gap-2 product-meta">
                            <span class="badge-soft">Bought: {{ number_format((int) ($product->total_bought ?? 0)) }}</span>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-2 d-none d-md-flex">
                            @php
                                $firstSize = !empty($product->sizes) ? $product->sizes[0] : ($product->size ?? 'N/A');
                                $firstColor = !empty($product->colors) ? ($product->colors[0]['name'] ?? 'N/A') : ($product->color ?? 'N/A');
                            @endphp
                            <span class="badge-soft">Size: {{ $firstSize }}</span>
                            <span class="badge-soft">Color: {{ $firstColor }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 product-actions">
                            <span class="price">${{ number_format($product->price, 2) }}</span>
                            <div class="d-flex gap-2 product-actions-cta">
                                <a href="{{ route('product.show', $product->id) }}" class="btn btn-khqr btn-sm product-cta">
                                    View details
                                    <i class="bi bi-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card-khqr text-center p-5">
                    <p class="text-muted mb-0">No products available.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
