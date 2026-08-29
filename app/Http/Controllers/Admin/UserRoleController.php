<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ApiResponds;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignRoleRequest;
use App\Http\Requests\Admin\SyncRolesRequest;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Not part of the customer API spec (§17 explicitly puts Admin APIs
 * out of scope / a separate doc) — built fresh for testing the
 * multi-role refactor from Step 7. Reuses UserResource so the shape
 * an admin sees for `roles` matches exactly what the customer-facing
 * GET /api/user returns.
 *
 * ASSUMPTION: guard name is 'admin' (config/auth.php), matching the
 * separate Admin model/table already in the project. Adjust the
 * 'auth:admin' middleware in routes/admin_users.php if that's wrong.
 */
class UserRoleController extends Controller
{
    use ApiResponds;

    /**
     * GET /admin/users
     * Query params: role (filter, e.g. ?role=vendor), page, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()->with('roles');

        if ($roleSlug = $request->query('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('slug', $roleSlug));
        }

        $perPage = min((int) $request->query('per_page', 24), 100) ?: 24;
        $users = $query->orderBy('name')->paginate($perPage)->withQueryString();

        return $this->respondPaginated($users, UserResource::class);
    }

    /**
     * GET /admin/users/{user}
     */
    public function show(User $user): JsonResponse
    {
        return $this->respond(new UserResource($user->loadMissing(['roles', 'shop'])));
    }

    /**
     * POST /admin/users/{user}/roles   { "role": "vendor" }
     * Adds a role without touching any roles the user already has.
     */
    public function assign(AssignRoleRequest $request, User $user): JsonResponse
    {
        $user->assignRole($request->validated('role'), Auth::guard('admin')->id());

        return $this->respond(new UserResource($user->fresh()->loadMissing(['roles', 'shop'])));
    }

    /**
     * PUT /admin/users/{user}/roles   { "roles": ["customer", "vendor"] }
     * Replaces the user's entire role set with exactly this list —
     * anything not included gets removed.
     */
    public function sync(SyncRolesRequest $request, User $user): JsonResponse
    {
        $adminId = Auth::guard('admin')->id();

        $roleIds = Role::whereIn('slug', $request->validated('roles'))
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [$id => ['assigned_by' => $adminId]]);

        $user->roles()->sync($roleIds);

        return $this->respond(new UserResource($user->fresh()->loadMissing(['roles', 'shop'])));
    }

    /**
     * DELETE /admin/users/{user}/roles/{role}
     */
    public function remove(User $user, string $role): JsonResponse
    {
        if (! Role::where('slug', $role)->exists()) {
            return $this->respondMessage('Role not found', 404);
        }

        $user->removeRole($role);

        return $this->respond(new UserResource($user->fresh()->loadMissing(['roles', 'shop'])));
    }
}
