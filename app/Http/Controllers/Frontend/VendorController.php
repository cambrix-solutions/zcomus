<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §4, items 24–25.
 * Public, no auth.
 */
class VendorController extends Controller
{
    use ApiResponds;

    private const VISIBLE_PRODUCT_STATUSES = ['listed', 'out_of_stock'];

    /**
     * GET /api/vendors
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 24), 100) ?: 24;

        $shops = Shop::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->whereIn('status', self::VISIBLE_PRODUCT_STATUSES)])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return $this->respondPaginated($shops, VendorResource::class);
    }

    /**
     * GET /api/vendors/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        $shop = Shop::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->withCount(['products' => fn ($q) => $q->whereIn('status', self::VISIBLE_PRODUCT_STATUSES)])
            ->with(['products' => function ($q) {
                $q->whereIn('status', self::VISIBLE_PRODUCT_STATUSES)
                    ->latest()
                    ->limit(8);
            }])
            ->first();

        if (! $shop) {
            return $this->respondMessage('Vendor not found', 404);
        }

        return $this->respond(new VendorResource($shop));
    }
}
