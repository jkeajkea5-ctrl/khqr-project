<?php

namespace App\Models;

use App\Support\MediaPath;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
   protected $fillable = ['name','category_id','description','price','image','size','color','sizes','colors'];

   protected $casts = [
       'sizes' => 'array',
   ];

   public function getImageAttribute($value): ?string
   {
       return MediaPath::resolve($value);
   }

   public function setImageAttribute($value): void
   {
       $this->attributes['image'] = MediaPath::normalize($value);
   }

   public function getColorsAttribute($value): ?array
   {
       $colors = $this->decodeColors($value);

       if ($colors === []) {
           return null;
       }

       return array_map(function (array $color): array {
           $color['image'] = MediaPath::resolve($color['image'] ?? null);

           return $color;
       }, $colors);
   }

   public function setColorsAttribute($value): void
   {
       $colors = $this->normalizeColors($value);

       $this->attributes['colors'] = $colors === []
           ? null
           : json_encode($colors, JSON_UNESCAPED_SLASHES);
   }

   public function category(): BelongsTo
   {
       return $this->belongsTo(Category::class);
   }

   private function decodeColors(mixed $value): array
   {
       if (is_array($value)) {
           return $value;
       }

       if (!is_string($value) || trim($value) === '') {
           return [];
       }

       $decoded = json_decode($value, true);

       return is_array($decoded) ? $decoded : [];
   }

   private function normalizeColors(mixed $value): array
   {
       $colors = $this->decodeColors($value);

       $normalized = [];

       foreach ($colors as $color) {
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

           $normalized[] = [
               'name' => $name,
               'image' => $image,
           ];
       }

       return $normalized;
   }
}
