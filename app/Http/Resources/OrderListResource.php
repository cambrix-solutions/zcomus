<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.10 — GET /api/orders -> data[].
 * Requires `shop` and `items.product` eager-loaded.
 */
class OrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_code' => $this->order_code,
            'date' => $this->placed_at->toIso8601String(),
            'status' => $this->status,
            'total' => (float) $this->total,
            'payment_method' => $this->payment_method,
            'vendor_name' => $this->shop?->name,
            'items_count' => $this->items->sum('qty'),
            'thumbnail' => $this->items->first()?->product_image,
        ];
    }
}
