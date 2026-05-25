<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Slide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class VercelBootstrapSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguarded(function (): void {
            $categoryKeys = $this->existingCategoryKeys();

            if (!Category::query()->exists()) {
                foreach ($this->categories() as $category) {
                    $createdCategory = Category::query()->create($category);
                    $categoryKeys[(string) $createdCategory->slug] = (string) $createdCategory->getKey();
                }
            }

            if (!Product::query()->exists()) {
                foreach ($this->products($categoryKeys) as $product) {
                    Product::query()->create($product);
                }
            }

            if (!Slide::query()->exists()) {
                foreach ($this->slides() as $slide) {
                    Slide::query()->create($slide);
                }
            }
        });
    }

    private function existingCategoryKeys(): array
    {
        $keys = [];

        foreach (Category::query()->get() as $category) {
            $slug = trim((string) $category->slug);

            if ($slug === '') {
                continue;
            }

            $keys[$slug] = (string) $category->getKey();
        }

        return $keys;
    }

    private function categories(): array
    {
        return [
            [
                'name' => 'T-Shirt',
                'slug' => 't-shirt',
                'created_at' => '2026-05-16 10:18:19',
                'updated_at' => '2026-05-16 10:18:19',
            ],
            [
                'name' => 'Pant',
                'slug' => 'pant',
                'created_at' => '2026-05-18 20:56:08',
                'updated_at' => '2026-05-18 20:56:08',
            ],
            [
                'name' => 'Sheos',
                'slug' => 'sheos',
                'created_at' => '2026-05-18 20:56:20',
                'updated_at' => '2026-05-18 20:56:20',
            ],
        ];
    }

    private function products(array $categoryKeys): array
    {
        return [
            [
                'name' => 'T Shirt',
                'category_id' => $categoryKeys['t-shirt'] ?? null,
                'description' => 'New Brand',
                'price' => '0.01',
                'size' => null,
                'color' => null,
                'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'colors' => [
                    [
                        'name' => 'White-Blue',
                        'image' => 'product-colors/vAttxhAW1CQdNLnz0IEwA6zNUTvoIB3A7uvNMCQP.jpg',
                    ],
                ],
                'image' => 'products/JuYMbyoeLg9o3zA5XZmCS3EYUobxxMcGU5Q8HL2x.jpg',
                'created_at' => '2026-05-18 20:26:30',
                'updated_at' => '2026-05-18 21:20:19',
            ],
            [
                'name' => 'Pants',
                'category_id' => $categoryKeys['pant'] ?? null,
                'description' => 'Pant',
                'price' => '0.02',
                'size' => null,
                'color' => null,
                'sizes' => ['XS', 'S', 'M'],
                'colors' => [
                    [
                        'name' => 'Black',
                        'image' => 'product-colors/sAoVP3oWFaxhqdYkSXeNIf6C9kCesTrnLOkuPJBM.jpg',
                    ],
                ],
                'image' => 'products/3koqFsBbpdX0KCp43Wa0CkYp32aJoGWnWSEUd465.jpg',
                'created_at' => '2026-05-18 20:57:58',
                'updated_at' => '2026-05-18 20:57:58',
            ],
            [
                'name' => 'Shoes',
                'category_id' => $categoryKeys['sheos'] ?? null,
                'description' => 'Shoes',
                'price' => '0.03',
                'size' => null,
                'color' => null,
                'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                'colors' => [
                    [
                        'name' => 'Blue',
                        'image' => 'product-colors/iN2Xp1H0Uc8mIHHmgkfdIH3IUiA9iVeqtlUhN44i.webp',
                    ],
                ],
                'image' => 'products/vda9x2KF7z033gNsLWYUaaMDxyGMQcWK2h7gcBwZ.webp',
                'created_at' => '2026-05-18 20:59:01',
                'updated_at' => '2026-05-18 20:59:13',
            ],
        ];
    }

    private function slides(): array
    {
        return [
            [
                'title' => null,
                'subtitle' => null,
                'image' => 'slides/xfcBZwVHXqNya9qduKP9a8BTYVmtFtmQAbv7QB41.jpg',
                'link' => null,
                'is_active' => true,
                'position' => 0,
                'created_at' => '2026-05-18 11:43:51',
                'updated_at' => '2026-05-18 11:43:51',
            ],
            [
                'title' => null,
                'subtitle' => null,
                'image' => 'slides/AMdAPzFjXCZ4IpLaxkX4swo4NWzMApfkCGdpm1Ox.jpg',
                'link' => null,
                'is_active' => true,
                'position' => 1,
                'created_at' => '2026-05-18 11:44:10',
                'updated_at' => '2026-05-18 11:44:42',
            ],
        ];
    }
}
