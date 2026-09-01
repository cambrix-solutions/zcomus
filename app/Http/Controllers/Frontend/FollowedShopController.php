<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\FollowShopRequest;
use App\Http\Resources\VendorResource;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §12, items 59–61.
 */
class FollowedShopController extends Controller
{
    use ApiResponds;

    /**
     * GET /api/followed-shops — data[] is vendor list columns (§20.5),
     * reusing VendorResource from Step 6.
     */
    public function index(Request $request): JsonResponse
    {
        $shopIds = $request->user()->followedShops()->pluck('shop_id');

        $shops = Shop::query()
            ->whereIn('id', $shopIds)
            ->withCount(['products' => fn ($q) => $q->whereIn('status', ['listed', 'out_of_stock'])])
            ->get();

        return $this->respond(VendorResource::collection($shops)->toArray($request));
    }

    /**
     * POST /api/followed-shops   { shop_id } or { vendor_id }
     */
    public function store(FollowShopRequest $request): JsonResponse
    {
        $request->user()->followedShops()->firstOrCreate([
            'shop_id' => $request->validated('shop_id'),
        ]);

        return $this->respondMessage('Following shop.', 201);
    }

    /**
     * DELETE /api/followed-shops/{vendorId}
     */
    public function destroy(Request $request, int $vendorId): JsonResponse
    {
        $request->user()->followedShops()->where('shop_id', $vendorId)->delete();

        return $this->respondMessage('Unfollowed shop.');
    }
}
