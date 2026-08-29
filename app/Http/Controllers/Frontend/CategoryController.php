<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

/**
 * Spec ref: §4, item 20 — GET /api/categories.
 * Public, no auth.
 */
class CategoryController extends Controller
{
    use ApiResponds;

    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['products' => fn ($q) => $q->whereIn('status', ['listed', 'out_of_stock'])])
            ->orderBy('sort_order')
            ->get();

        return $this->respond(CategoryResource::collection($categories)->toArray(request()));
    }
}
