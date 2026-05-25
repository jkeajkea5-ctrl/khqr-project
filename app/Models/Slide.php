<?php

namespace App\Models;

use App\Models\Support\AppModel;
use App\Support\MediaPath;

class Slide extends AppModel
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
