<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §20.15. `$this->resource` is a UserVoucher — requires
 * `coupon` eager-loaded. `id` is the user's claim id (this specific
 * voucher instance in their wallet), not the coupon id — two users
 * who both claimed "SAVE10" have different rows here.
 */
class VoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->coupon->title,
            'code' => $this->coupon->code,
            'type' => $this->coupon->type,
            'value' => (float) $this->coupon->value,
            'rule' => $this->coupon->rule,
            'expires' => $this->coupon->ends_at->toIso8601String(),
            'used' => $this->used_at !== null,
        ];
    }
}
