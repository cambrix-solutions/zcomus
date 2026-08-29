<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Spec ref: §3 (Profile & account preferences), items 13–15.
 * Open to any authenticated role — customer, vendor, or support —
 * since this is always "my own account", never someone else's.
 */
class ProfileController extends Controller
{
    use ApiResponds;

    /**
     * GET /api/user — also serves as GET /api/user from §2 (shared endpoint).
     */
    public function show(Request $request): JsonResponse
    {
        return $this->respond(new UserResource($request->user()->loadMissing('shop')));
    }

    /**
     * PUT /api/profile
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->update($request->validated());

        return $this->respond(new UserResource($user->fresh()->loadMissing('shop')));
    }

    /**
     * DELETE /api/profile (Optional in spec)
     *
     * ASSUMPTION: the spec doesn't define soft-delete vs hard-delete,
     * and there's no `deleted_at`/`is_active` column on `users` yet.
     * This does a hard delete + logs the session out, since that's the
     * only option the current schema supports. If you'd rather keep the
     * row (e.g. for order history integrity — orders.user_id would
     * cascade-delete otherwise, per the Step 3 migration), swap this
     * for a soft delete: add `SoftDeletes` to User, change orders'
     * user_id FK to nullOnDelete, and change this method to
     * $user->delete() under a `deleted_at` column instead of a real
     * row removal.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        Auth::guard('web')->logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->respondMessage('Account deleted');
    }
}
