<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');
        $selectedCategory = null;
        $categorySlug = trim((string) $request->input('category'));
        $search = trim((string) $request->input('q'));

        if ($categorySlug !== '') {
            $selectedCategory = Cache::remember(
                'storefront:category:'.$categorySlug,
                now()->addMinutes(10),
                fn () => Category::where('slug', $categorySlug)->first()
            );

            if ($selectedCategory) {
                $query->where('category_id', $selectedCategory->id);
            } else {
                $query->where('id', '__missing_category__');
            }
        }

        if ($search !== '') {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $products = $query->get();
        $this->appendTotalBought($products);

        $categories = Cache::remember('storefront:categories', now()->addMinutes(5), function () {
            $categories = Category::orderBy('name')->get();
            $counts = Product::query()
                ->whereIn('category_id', $categories->pluck('id')->all())
                ->get(['category_id'])
                ->countBy(fn (Product $product): string => (string) $product->category_id);

            return $categories->transform(function (Category $category) use ($counts): Category {
                $category->setAttribute('products_count', (int) ($counts[(string) $category->id] ?? 0));

                return $category;
            });
        });
        $slides = Cache::remember(
            'storefront:slides:active',
            now()->addMinutes(5),
            fn () => Slide::where('is_active', true)->orderBy('position')->get()
        );

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
            ->map(fn ($id) => $this->normalizeIdentifier($id))
            ->filter()
            ->all();

        $trackedIds = array_fill_keys($productIds, true);
        sort($productIds);

        $purchaseCounts = Cache::remember(
            'storefront:purchase-counts:'.sha1(json_encode($productIds)),
            now()->addMinutes(2),
            function () use ($productIds, $trackedIds): array {
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
                            $itemProductId = $this->normalizeIdentifier($item['id'] ?? null);

                            if ($itemProductId === null || !isset($trackedIds[$itemProductId])) {
                                continue;
                            }

                            $qty = array_key_exists('qty', $item)
                                ? max(0, (int) $item['qty'])
                                : 1;

                            $purchaseCounts[$itemProductId] += $qty;
                        }

                        continue;
                    }

                    $orderProductId = $this->normalizeIdentifier($order->product_id);

                    if ($orderProductId !== null && isset($trackedIds[$orderProductId])) {
                        $purchaseCounts[$orderProductId] += 1;
                    }
                }

                return $purchaseCounts;
            }
        );

        $products->each(function (Product $product) use ($purchaseCounts): void {
            $key = $this->normalizeIdentifier($product->id);
            $product->setAttribute('total_bought', $key !== null ? ($purchaseCounts[$key] ?? 0) : 0);
        });
    }

    private function normalizeIdentifier(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized !== '' ? $normalized : null;
    }
}
