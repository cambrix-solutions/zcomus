<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.4 — product detail (GET /api/products/{id} or by-slug).
 * Card fields + detail extras. Requires `shop` and `variants` to be
 * eager-loaded.
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_merge(
            (new ProductListResource($this->resource))->toArray($request),
            [
                'images' => $this->images ?? [],
                'description' => $this->description,
                'short_description' => $this->short_description,
                'sku' => $this->sku,
                'warranty' => $this->warranty,
                'specs' => $this->specs ?? [],
                'colors' => $this->colors ?? [],
                'styles' => $this->styles ?? [],
                'sizes' => $this->sizes ?? [],
                'variants' => $this->variants->map(fn ($variant) => [
                    'key' => (string) $variant->id,
                    'color' => $variant->color,
                    'style' => $variant->style,
                    'size' => $variant->size,
                    'stock' => $variant->stock,
                    'price' => $variant->price !== null ? (float) $variant->price : null,
                ]),
                'vendor_bio' => $this->shop?->description,
            ]
        );
    }
}
