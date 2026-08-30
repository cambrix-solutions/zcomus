<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.6. Requires `product`, `product.shop`, and `variant`
 * to be eager-loaded by the controller.
 *
 * ADDITION beyond the spec's example: `variant`. The spec's sample
 * response only shows a plain product with no variant, but
 * POST /api/cart accepts an optional variant_id — the frontend needs
 * some way to see which variant ended up in the cart, so this adds it
 * rather than silently dropping that information on read.
 */
class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $stock = $this->variant?->stock ?? $this->product->stock;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'name' => $this->product->name,
            'slug' => $this->product->slug,
            'image' => $this->product->image,
            'qty' => $this->qty,
            'unit_price' => (float) $this->unit_price,
            'line_total' => round($this->qty * (float) $this->unit_price, 2),
            'stock' => $stock,
            'vendor_name' => $this->product->shop?->name,
            'vendor_slug' => $this->product->shop?->slug,
            'variant' => $this->variant ? [
                'id' => $this->variant->id,
                'color' => $this->variant->color,
                'style' => $this->variant->style,
                'size' => $this->variant->size,
            ] : null,
        ];
    }
}
