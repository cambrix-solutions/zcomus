<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.4 — product list/card columns (GET /api/products -> data[]).
 * Requires `shop` to be eager-loaded (for vendor_name/vendor_slug).
 */
class ProductListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'brand' => $this->brand,
            'category_id' => $this->category_id,
            'price' => (float) $this->price,
            'compare_at_price' => $this->compare_at_price !== null ? (float) $this->compare_at_price : null,
            'image' => $this->image,
            'badge' => $this->badge,
            'stock' => $this->stock,
            'vendor_name' => $this->shop?->name,
            'vendor_slug' => $this->shop?->slug,
            'is_flash' => (bool) $this->is_flash,
            'is_trending' => (bool) $this->is_trending,
            'is_top_selling' => (bool) $this->is_top_selling,
        ];
    }
}
