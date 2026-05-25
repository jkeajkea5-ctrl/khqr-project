<?php

namespace App\Models;

use App\Models\Support\AppModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends AppModel
{
    protected $table = 'catagories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
