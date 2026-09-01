<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Concerns\AppliesCoupon;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;

/**
 * Spec ref: §6, item 32.
 */
class CouponController extends Controller
{
    use ApiResponds, AppliesCoupon;

    public function check(ValidateCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::where('code', $request->validated('code'))->first();
        $subtotal = $request->validated('subtotal');

        $result = $this->evaluateCoupon($coupon, $subtotal !== null ? (float) $subtotal : null);

        return $this->respond([
            'valid' => $result['valid'],
            'code' => $coupon?->code ?? $request->validated('code'),
            'title' => $coupon?->title,
            'type' => $coupon?->type,
            'value' => $coupon !== null ? (float) $coupon->value : null,
            'discount_amount' => $result['discount'],
            'message' => $result['message'],
        ]);
    }
}
