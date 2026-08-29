<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Spec ref: §16 convention #3 — { "message": "OK", "data": {} }
 *
 * Every API controller should `use ApiResponds` and call these instead
 * of building response()->json([...]) by hand, so the envelope shape
 * never drifts between controllers.
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
}
