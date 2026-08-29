<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.1 — the `user` object returned by login / register / GET /api/user.
 *
 * DEVIATION FROM SPEC: §20.1 originally defined a single `role` string.
 * Per your team's decision to move to multi-role, this now returns
 * `roles` (array of slugs, e.g. ["customer", "vendor"]) instead.
 * Any frontend code written against the old single `role` field needs
 * updating to check `roles.includes('vendor')` instead of `role === 'vendor'`.
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('slug')->values(), []),
            'phone' => $this->phone,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'preferred_payment' => $this->preferred_payment,
            'shop' => $this->whenLoaded('shop', fn () => $this->shop ? [
                'id' => $this->shop->id,
                'name' => $this->shop->name,
                'slug' => $this->shop->slug,
            ] : null, null),
        ];
    }
}
