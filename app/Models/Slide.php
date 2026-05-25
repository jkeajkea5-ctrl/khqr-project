<?php

namespace App\Models;

use App\Support\MediaPath;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $fillable = ['title', 'subtitle', 'image', 'link', 'is_active', 'position'];

    public function getImageAttribute($value): ?string
    {
        return MediaPath::resolve($value);
    }

    public function setImageAttribute($value): void
    {
        $this->attributes['image'] = MediaPath::normalize($value);
    }
}
