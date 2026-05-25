<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductAdminController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(12);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:catagories,id',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0.01',
            'size'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:50',
            'sizes'       => 'nullable|array',
            'sizes.*'     => 'in:XS,S,M,L,XL,XXL',
            'colors'      => 'nullable|array',
            'colors.name' => 'nullable|array',
            'colors.name.*' => 'nullable|string|max:50',
            'colors.image' => 'nullable|array',
            'colors.image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $path = $request->file('image')->store('products', 'public');
        $colors = $this->buildColors($request);

        Product::create([
            'name'        => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'description' => $validated['description'] ?? '',
            'price'       => $validated['price'],
            'size'        => $validated['size'] ?? null,
            'color'       => $validated['color'] ?? null,
            'sizes'       => $validated['sizes'] ?? null,
            'colors'      => $colors,
            'image'       => $path,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'nullable|exists:catagories,id',
            'description' => 'nullable|string|max:2000',
            'price'       => 'required|numeric|min:0.01',
            'size'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:50',
            'sizes'       => 'nullable|array',
            'sizes.*'     => 'in:XS,S,M,L,XL,XXL',
            'colors'      => 'nullable|array',
            'colors.name' => 'nullable|array',
            'colors.name.*' => 'nullable|string|max:50',
            'colors.image' => 'nullable|array',
            'colors.image.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);

        $data = [
            'name'        => $validated['name'],
            'category_id' => $validated['category_id'] ?? null,
            'description' => $validated['description'] ?? '',
            'price'       => $validated['price'],
            'size'        => $validated['size'] ?? null,
            'color'       => $validated['color'] ?? null,
            'sizes'       => $validated['sizes'] ?? null,
        ];

        $colors = $this->buildColors($request);
        if ($colors !== null) {
            $data['colors'] = $colors;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    private function buildColors(Request $request): ?array
    {
        if (!$request->has('colors') && !$request->hasFile('colors.image')) {
            return null;
        }

        $names = $request->input('colors.name', []);
        $images = $request->file('colors.image', []);
        $colors = [];

        $max = max(count($names), count($images));
        for ($i = 0; $i < $max; $i++) {
            $name = $names[$i] ?? null;
            $imageFile = $images[$i] ?? null;

            if (!$name && !$imageFile) {
                continue;
            }

            $imageUrl = null;
            if ($imageFile) {
                $path = $imageFile->store('product-colors', 'public');
                $imageUrl = $path;
            }

            $colors[] = [
                'name' => $name ?: 'Color '.($i + 1),
                'image' => $imageUrl,
            ];
        }

        return $colors ?: null;
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted.');
    }
}
