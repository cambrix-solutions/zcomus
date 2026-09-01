<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Coupon;

/**
 * Used by both CouponController::validate() and
 * CheckoutController::store() — the discount a coupon produces must
 * be computed identically in both places, or "validate" could show
 * one number and checkout could charge a different one.
 */
trait AppliesCoupon
{
    /**
     * @return array{valid: bool, message: string, discount: float}
     */
    protected function evaluateCoupon(?Coupon $coupon, ?float $subtotal): array
    {
        if (! $coupon) {
            return ['valid' => false, 'message' => 'Invalid coupon code.', 'discount' => 0.0];
        }

        if (! $coupon->is_active || ! now()->between($coupon->starts_at, $coupon->ends_at)) {
            return ['valid' => false, 'message' => 'This coupon is not currently active.', 'discount' => 0.0];
        }

        if ($coupon->min_order !== null && $subtotal !== null && $subtotal < (float) $coupon->min_order) {
            $min = number_format((float) $coupon->min_order, 2);

            return ['valid' => false, 'message' => "Minimum order of \${$min} required.", 'discount' => 0.0];
        }

        $discount = match ($coupon->type) {
            'percent' => $subtotal !== null ? round($subtotal * ((float) $coupon->value / 100), 2) : 0.0,
            'fixed' => $subtotal !== null ? min((float) $coupon->value, $subtotal) : (float) $coupon->value,
            default => 0.0,
        };

        return ['valid' => true, 'message' => 'Coupon applied.', 'discount' => $discount];
    }
}
