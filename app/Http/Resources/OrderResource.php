<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.10 — GET /api/orders/{id}. All list fields plus
 * subtotal/shipping_fee/discount/notes/shipping/items/tracking.
 * Requires shop, items.product, trackingEvents eager-loaded.
 */
class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return array_merge(
            (new OrderListResource($this->resource))->toArray($request),
            [
                'subtotal' => (float) $this->subtotal,
                'shipping_fee' => (float) $this->shipping_fee,
                'discount' => (float) $this->discount,
                'notes' => $this->notes,
                'shipping' => [
                    'name' => $this->shipping_name,
                    'phone' => $this->shipping_phone,
                    'line1' => $this->shipping_line1,
                    'city' => $this->shipping_city,
                ],
                'items' => OrderItemResource::collection($this->items),
                'tracking' => $this->trackingEvents->map(fn ($event) => [
                    'status' => $event->status,
                    'label' => $event->label,
                    'happened_at' => $event->happened_at->toIso8601String(),
                ]),
            ]
        );
    }
}
