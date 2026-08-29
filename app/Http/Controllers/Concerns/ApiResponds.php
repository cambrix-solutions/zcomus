<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Spec ref: §16 conventions #3 (envelope) and #6 (pagination).
 *
 * Every API controller should `use ApiResponds` instead of building
 * response()->json([...]) by hand, so the envelope shape never drifts
 * between controllers.
 */
trait ApiResponds
{
    protected function respond(mixed $data, string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $status);
    }

    protected function respondMessage(string $message, int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    /**
     * @param  class-string<JsonResource>  $resourceClass
     */
    protected function respondPaginated(
        LengthAwarePaginator $paginator,
        string $resourceClass,
        string $message = 'OK'
    ): JsonResponse {
        return response()->json([
            'message' => $message,
            'data' => $resourceClass::collection($paginator->getCollection())->toArray(request()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
