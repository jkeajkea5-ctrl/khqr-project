<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        $selectedCategory = null;

        if ($request->filled('category')) {
            $selectedCategory = Category::where('slug', $request->input('category'))->first();

            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $products = $query->get();
        $this->appendTotalBought($products);

        $categories = Category::withCount('products')->orderBy('name')->get();
        $slides = Slide::where('is_active', true)->orderBy('position')->get();

        return view('products.index', compact('products', 'slides', 'categories', 'selectedCategory'));
    }

    public function show($id)
    {
        $product = Product::with('category')->findOrFail($id);

        return view('products.show', compact('product'));
    }

    private function appendTotalBought(Collection $products): void
    {
        if ($products->isEmpty()) {
            return;
        }

        $productIds = $products->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $trackedIds = array_fill_keys($productIds, true);
        $purchaseCounts = array_fill_keys($productIds, 0);

        $paidOrders = Order::where('status', 'PAID')
            ->where(function ($query) use ($productIds) {
                $query->whereIn('product_id', $productIds)
                    ->orWhereNotNull('items');
            })
            ->get(['product_id', 'items']);

        foreach ($paidOrders as $order) {
            $items = is_array($order->items) ? $order->items : [];

            if ($items !== []) {
                foreach ($items as $item) {
                    $itemProductId = (int) ($item['id'] ?? 0);

                    if (!isset($trackedIds[$itemProductId])) {
                        continue;
                    }

                    $qty = array_key_exists('qty', $item)
                        ? max(0, (int) $item['qty'])
                        : 1;

                    $purchaseCounts[$itemProductId] += $qty;
                }

                continue;
            }

            $orderProductId = (int) $order->product_id;

            if (isset($trackedIds[$orderProductId])) {
                $purchaseCounts[$orderProductId] += 1;
            }
        }

        $products->each(function (Product $product) use ($purchaseCounts): void {
            $product->setAttribute('total_bought', $purchaseCounts[(int) $product->id] ?? 0);
        });
    }
}
