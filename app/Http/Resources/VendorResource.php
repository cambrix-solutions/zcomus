<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.5 — GET /api/vendors & GET /api/vendors/{slug}.
 *
 * GAP: `reviews` (number) isn't backed by any table in the spec's §19
 * schema — there's no reviews/ratings table at all. Hardcoded to 0
 * until that table exists; flagged here rather than silently guessing.
 */
class VendorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'logo' => $this->logo,
            'cover' => $this->cover,
            'products' => $this->products_count ?? 0, // withCount() in the controller
            'reviews' => 0, // GAP — no reviews table yet, see class docblock
            'member_since' => $this->created_at->year,
            'address' => $this->address,
            'phone' => $this->phone,
            'industry' => $this->industry,
            'description' => $this->description,
            'tagline' => $this->tagline,
            'accent_color' => $this->accent_color,
            'theme' => $this->theme,
            'announcement' => $this->announcement,
            'products_preview' => $this->whenLoaded(
                'products',
                fn () => ProductListResource::collection($this->products)
            ),
        ];
    }
}
