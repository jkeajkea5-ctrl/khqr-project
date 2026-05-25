<?php

use App\Support\MediaPath;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            DB::table('products')
                ->select('id', 'image', 'colors')
                ->orderBy('id')
                ->chunkById(100, function ($products): void {
                    foreach ($products as $product) {
                        $image = MediaPath::normalize($product->image);
                        $colors = $this->normalizeColors($product->colors);

                        $payload = [];

                        if ($image !== $product->image) {
                            $payload['image'] = $image;
                        }

                        if ($colors !== $product->colors) {
                            $payload['colors'] = $colors;
                        }

                        if ($payload !== []) {
                            DB::table('products')->where('id', $product->id)->update($payload);
                        }
                    }
                });
        }

        if (Schema::hasTable('slides')) {
            DB::table('slides')
                ->select('id', 'image')
                ->orderBy('id')
                ->chunkById(100, function ($slides): void {
                    foreach ($slides as $slide) {
                        $image = MediaPath::normalize($slide->image);

                        if ($image !== $slide->image) {
                            DB::table('slides')->where('id', $slide->id)->update([
                                'image' => $image,
                            ]);
                        }
                    }
                });
        }
    }

    public function down(): void
    {
        //
    }

    private function normalizeColors(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($decoded)) {
            return null;
        }

        $colors = [];

        foreach ($decoded as $color) {
            if (!is_array($color)) {
                continue;
            }

            $name = isset($color['name']) ? trim((string) $color['name']) : null;
            $image = MediaPath::normalize($color['image'] ?? null);

            if ($name === '') {
                $name = null;
            }

            if (!$name && !$image) {
                continue;
            }

            $colors[] = [
                'name' => $name,
                'image' => $image,
            ];
        }

        return $colors === [] ? null : json_encode($colors, JSON_UNESCAPED_SLASHES);
    }
};
