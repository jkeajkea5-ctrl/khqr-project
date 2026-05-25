<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\MediaPath;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = array_map(function (array $item): array {
            $item['image'] = MediaPath::resolve($item['image'] ?? null);
            $item['color_image'] = MediaPath::resolve($item['color_image'] ?? null);

            return $item;
        }, session('cart', []));

        return view('products.cart', ['cart' => $cart]);
    }

    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'size' => 'required|string|max:50',
            'color' => 'required|string|max:50',
        ]);

        $cart = session('cart', []);
        $id = $product->id;
        $key = $id.'|'.$validated['size'].'|'.$validated['color'];
        $colorImage = null;
        $rawColors = $product->getRawOriginal('colors');
        $colors = is_string($rawColors) ? json_decode($rawColors, true) : [];

        if (is_array($colors)) {
            foreach ($colors as $c) {
                if (($c['name'] ?? null) === $validated['color']) {
                    $colorImage = MediaPath::normalize($c['image'] ?? null);
                    break;
                }
            }
        }

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += 1;
        } else {
            $cart[$key] = [
                'key' => $key,
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'image' => MediaPath::normalize($product->getRawOriginal('image') ?: $product->image),
                'size' => $validated['size'],
                'color' => $validated['color'],
                'color_image' => $colorImage,
                'qty' => 1,
            ];
        }

        session(['cart' => $cart]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Added to cart',
                'count' => collect($cart)->sum(fn ($item) => $item['qty']),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item added to cart.');
    }

    public function update(Request $request, string $key)
    {
        $validated = $request->validate([
            'qty' => 'required|integer|min:0|max:99',
        ]);

        $cart = session('cart', []);

        if (!isset($cart[$key])) {
            return redirect()->route('cart.index');
        }

        if ($validated['qty'] === 0) {
            unset($cart[$key]);
        } else {
            $cart[$key]['qty'] = $validated['qty'];
        }

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Cart updated.');
    }

    public function remove(string $key)
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Item removed.');
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }
}
