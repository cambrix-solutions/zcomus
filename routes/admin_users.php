<?php

use App\Http\Controllers\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin: User Role Management
|--------------------------------------------------------------------------
| Not in the customer API spec — built for testing Step 7's multi-role
| refactor. Self-contained (own prefix/middleware/name) rather than
| nested inside admin.php's existing group, since I haven't seen that
| file's internals — safe to merge into admin.php later if you'd
| rather keep all admin routes in one place.
|
| ASSUMPTION: the admin guard is named 'admin'. If config/auth.php uses
| a different guard name for the Admin model, change it below.
*/
Route::middleware(['auth:admin'])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('/', [UserRoleController::class, 'index'])->name('index');
        Route::get('/{user}', [UserRoleController::class, 'show'])->name('show');

        Route::post('/{user}/roles', [UserRoleController::class, 'assign'])->name('roles.assign');
        Route::put('/{user}/roles', [UserRoleController::class, 'sync'])->name('roles.sync');
        Route::delete('/{user}/roles/{role}', [UserRoleController::class, 'remove'])->name('roles.remove');
    });
