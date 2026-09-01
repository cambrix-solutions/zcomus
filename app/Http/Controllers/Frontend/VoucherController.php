<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClaimVoucherRequest;
use App\Http\Resources\VoucherResource;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §10, items 52–53. (Item 54, POST /api/coupons/validate,
 * is CouponController::check() from Step 11 — shared with checkout.)
 */
class VoucherController extends Controller
{
    use ApiResponds;

    /**
     * GET /api/vouchers — the user's claimed wallet, not every public coupon.
     */
    public function index(Request $request): JsonResponse
    {
        $vouchers = $request->user()->vouchers()->with('coupon')->latest()->get();

        return $this->respond(VoucherResource::collection($vouchers)->toArray($request));
    }

    /**
     * POST /api/vouchers/claim   { code }   (Optional in spec)
     */
    public function claim(ClaimVoucherRequest $request): JsonResponse
    {
        $coupon = Coupon::where('code', $request->validated('code'))->first();
        $user = $request->user();

        if (! $coupon->is_active || ! now()->between($coupon->starts_at, $coupon->ends_at)) {
            return $this->respondMessage('This coupon is not currently active.', 422);
        }

        $voucher = $user->vouchers()->firstOrCreate(['coupon_id' => $coupon->id]);

        return $this->respond(new VoucherResource($voucher->load('coupon')), 'Voucher claimed.', 201);
    }
}
