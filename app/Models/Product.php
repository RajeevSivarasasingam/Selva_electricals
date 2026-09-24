<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = ['original_price', 'brand', 'certification', 'specification', 'slug', 'category_id', 'name', 'description', 'price', 'image', 'badge', 'featured', 'in_stock'];

    protected $casts = ['price' => 'decimal:2', 'featured' => 'boolean', 'in_stock' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
