<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shop_id',
    'category_id',
    'name',
    'slug',
    'brand',
    'price',
    'compare_at_price',
    'image',
    'images',
    'description',
    'short_description',
    'sku',
    'stock',
    'badge',
    'warranty',
    'specs',
    'colors',
    'styles',
    'sizes',
    'status',
    'is_flash',
    'is_trending',
    'is_top_selling',
])]
class Product extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'images' => 'array',
            'specs' => 'array',
            'colors' => 'array',
            'styles' => 'array',
            'sizes' => 'array',
            'stock' => 'integer',
            'is_flash' => 'boolean',
            'is_trending' => 'boolean',
            'is_top_selling' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
