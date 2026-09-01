<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecordRecentlyViewedRequest;
use App\Http\Resources\ProductListResource;
use App\Models\Product;
use App\Models\RecentlyView;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §13, items 62–63.
 */
class RecentlyViewedController extends Controller
{
    use ApiResponds;

    /** Hard cap per user — prevents unbounded growth; also just makes sense as a "recent" list. */
    private const MAX_PER_USER = 50;

    /**
     * GET /api/recently-viewed — data[] is product list card columns
     * (§20.4), reusing ProductListResource from Step 6.
     */
    public function index(Request $request): JsonResponse
    {
        $productIds = RecentlyView::where('user_id', $request->user()->id)
            ->orderByDesc('viewed_at')
            ->limit(self::MAX_PER_USER)
            ->pluck('product_id');

        // Re-fetch as Products (not RecentlyView rows) so
        // ProductListResource gets the shape it actually expects, and
        // preserve the recency order rather than whatever order the
        // products table happens to return.
        $products = Product::whereIn('id', $productIds)->with('shop')->get();
        $ordered = $productIds->map(fn ($id) => $products->firstWhere('id', $id))->filter()->values();

        return $this->respond(ProductListResource::collection($ordered)->toArray($request));
    }

    /**
     * POST /api/recently-viewed   { product_id }
     * Upsert — viewing the same product again just bumps viewed_at,
     * doesn't create a second row.
     */
    public function store(RecordRecentlyViewedRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        RecentlyView::updateOrCreate(
            ['user_id' => $userId, 'product_id' => $request->validated('product_id')],
            ['viewed_at' => now()]
        );

        // Prune anything beyond the cap, oldest first.
        $excessIds = RecentlyView::where('user_id', $userId)
            ->orderByDesc('viewed_at')
            ->pluck('id')
            ->slice(self::MAX_PER_USER);

        if ($excessIds->isNotEmpty()) {
            RecentlyView::whereIn('id', $excessIds)->delete();
        }

        return $this->respondMessage('Recorded.', 201);
    }
}
