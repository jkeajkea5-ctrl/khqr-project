<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::create([
            'name' => 'iPhone 17 Pro',
            'description' => '128GB, Blue Titanium',
            'price' => 1200,
            'size' => '6.7 inch',
            'color' => 'Blue Titanium',
            'sizes' => ['XS','S','M','L','XL'],
            'colors' => [
                ['name' => 'Blue Titanium', 'image' => 'https://i.ebayimg.com/images/g/EDEAAeSwLb9owuv8/s-l1600.webp'],
                ['name' => 'Natural Titanium', 'image' => 'https://i.ebayimg.com/images/g/EDEAAeSwLb9owuv8/s-l1600.webp'],
            ],
            'image' => 'https://i.ebayimg.com/images/g/EDEAAeSwLb9owuv8/s-l1600.webp',
        ]);

        Product::create([
            'name' => 'Samsung Galaxy S26',
            'description' => '256GB, Phantom Black',
            'price' => 1100,
            'size' => '6.8 inch',
            'color' => 'Phantom Black',
            'sizes' => ['S','M','L','XL'],
            'colors' => [
                ['name' => 'Phantom Black', 'image' => 'https://images.samsung.com/is/image/samsung/p6pim/levant/feature/galaxy-s-series.jpg'],
                ['name' => 'Silver', 'image' => 'https://images.samsung.com/is/image/samsung/p6pim/levant/feature/galaxy-s-series.jpg'],
            ],
            'image' => 'https://images.samsung.com/is/image/samsung/p6pim/levant/feature/galaxy-s-series.jpg',
        ]);

        Product::create([
            'name' => 'AirPods Pro 3',
            'description' => 'Noise cancelling wireless earbuds',
            'price' => 250,
            'size' => 'One size',
            'color' => 'White',
            'sizes' => ['One size'],
            'colors' => [
                ['name' => 'White', 'image' => 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/airpods-pro-2nd-gen'],
            ],
            'image' => 'https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/airpods-pro-2nd-gen',
        ]);
    }
}
