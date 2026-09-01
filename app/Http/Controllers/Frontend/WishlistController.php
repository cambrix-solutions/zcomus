<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWishlistRequest;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §8, items 43–46.
 */
class WishlistController extends Controller
{
    use ApiResponds;

    /**
     * GET /api/wishlist — data[] is product list card columns (§20.4),
     * reusing ProductListResource from Step 6 rather than a new shape.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 24), 100) ?: 24;

        $products = Product::query()
            ->whereIn('id', $request->user()->wishlist()->pluck('product_id'))
            ->with('shop')
            ->paginate($perPage)
            ->withQueryString();

        return $this->respondPaginated($products, ProductListResource::class);
    }

    /**
     * POST /api/wishlist   { product_id }
     * Idempotent — adding an already-wishlisted product isn't an error.
     */
    public function store(StoreWishlistRequest $request): JsonResponse
    {
        $request->user()->wishlist()->firstOrCreate([
            'product_id' => $request->validated('product_id'),
        ]);

        return $this->respondMessage('Added to wishlist.', 201);
    }

    /**
     * DELETE /api/wishlist/{productId}
     */
    public function destroy(Request $request, int $productId): JsonResponse
    {
        $request->user()->wishlist()->where('product_id', $productId)->delete();

        return $this->respondMessage('Removed from wishlist.');
    }

    /**
     * DELETE /api/wishlist (Optional)
     */
    public function clear(Request $request): JsonResponse
    {
        $request->user()->wishlist()->delete();

        return $this->respondMessage('Wishlist cleared.');
    }
}
