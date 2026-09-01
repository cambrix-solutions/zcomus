<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec ref: §11, items 55–58.
 */
class NotificationController extends Controller
{
    use ApiResponds;

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 24), 100) ?: 24;

        $notifications = $request->user()->notifications()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return $this->respondPaginated($notifications, NotificationResource::class);
    }

    /**
     * POST /api/notifications/{id}/read
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $notification = $request->user()->notifications()->find($id);

        if (! $notification) {
            return $this->respondMessage('Notification not found.', 404);
        }

        $notification->update(['read_at' => $notification->read_at ?? now()]);

        return $this->respond(new NotificationResource($notification->fresh()));
    }

    /**
     * POST /api/notifications/read-all (Optional)
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return $this->respondMessage('All notifications marked read.');
    }

    /**
     * DELETE /api/notifications/{id} (Optional)
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $request->user()->notifications()->where('id', $id)->delete();

        if (! $deleted) {
            return $this->respondMessage('Notification not found.', 404);
        }

        return $this->respondMessage('Notification dismissed.');
    }
}
