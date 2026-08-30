<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = CartItemResource::collection($this->items)->toArray($request);

        return [
            'id' => $this->id,
            'items' => $items,
            'items_count' => $this->items->sum('qty'),
            'subtotal' => round($this->items->sum(fn ($item) => $item->qty * (float) $item->unit_price), 2),
        ];
    }
}
