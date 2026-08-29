<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductListResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §4, items 21–23.
 * Public, no auth. Only `listed` / `out_of_stock` products are visible
 * here — `draft` and `archived` never leave the vendor's own dashboard.
 */
class ProductController extends Controller
{
    use ApiResponds;

    private const VISIBLE_STATUSES = ['listed', 'out_of_stock'];

    /**
     * GET /api/products
     * Query params: q, category, vendor, sort, page, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with('shop')
            ->whereIn('status', self::VISIBLE_STATUSES);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        if ($vendorSlug = $request->query('vendor')) {
            $query->whereHas('shop', fn ($q) => $q->where('slug', $vendorSlug));
        }

        match ($request->query('sort')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'popular' => $query->orderByDesc('is_top_selling')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'), // 'newest' and unspecified
        };

        $perPage = min((int) $request->query('per_page', 24), 100) ?: 24;

        $products = $query->paginate($perPage)->withQueryString();

        return $this->respondPaginated($products, ProductListResource::class);
    }

    /**
     * GET /api/products/{id}
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::query()
            ->with(['shop', 'variants'])
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->find($id);

        if (! $product) {
            return $this->respondMessage('Product not found', 404);
        }

        return $this->respond(new ProductResource($product));
    }

    /**
     * GET /api/products/by-slug/{slug}
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $product = Product::query()
            ->with(['shop', 'variants'])
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->where('slug', $slug)
            ->first();

        if (! $product) {
            return $this->respondMessage('Product not found', 404);
        }

        return $this->respond(new ProductResource($product));
    }
}
