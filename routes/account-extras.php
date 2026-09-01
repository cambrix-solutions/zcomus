<?php

use App\Http\Controllers\Frontend\AddressController;
use App\Http\Controllers\Frontend\FollowedShopController;
use App\Http\Controllers\Frontend\NotificationController;
use App\Http\Controllers\Frontend\VoucherController;
use App\Http\Controllers\Frontend\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account Extras Routes
|--------------------------------------------------------------------------
| Spec ref: ZCOMUS_CUSTOMER_API_SPEC §8–12.
| All Auth-only, all role-agnostic (any authenticated user manages
| their own wishlist/addresses/vouchers/notifications/follows) — same
| reasoning as Step 5's account.php.
|
| §13 (Recently viewed) intentionally NOT built — the spec itself
| marks both endpoints Optional and explicitly suggests client-side
| localStorage for phase 1 ("Can stay client-side localStorage in
| phase 1."). Building a backend for something the spec says to skip
| would be scope creep, not thoroughness.
*/
Route::middleware(['auth:web'])->group(function () {
    Route::prefix('api/wishlist')->name('api.wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/', [WishlistController::class, 'store'])->name('store');
        Route::delete('/{productId}', [WishlistController::class, 'destroy'])
            ->whereNumber('productId')->name('destroy');
        Route::delete('/', [WishlistController::class, 'clear'])->name('clear');
    });

    Route::prefix('api/addresses')->name('api.addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::put('/{id}', [AddressController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('/{id}', [AddressController::class, 'destroy'])->whereNumber('id')->name('destroy');
        Route::put('/{id}/default', [AddressController::class, 'setDefault'])
            ->whereNumber('id')->name('setDefault');
    });

    Route::get('/api/vouchers', [VoucherController::class, 'index'])->name('api.vouchers.index');
    Route::post('/api/vouchers/claim', [VoucherController::class, 'claim'])->name('api.vouchers.claim');

    Route::prefix('api/notifications')->name('api.notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markRead'])
            ->whereNumber('id')->name('markRead');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('markAllRead');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });

    Route::prefix('api/followed-shops')->name('api.followedShops.')->group(function () {
        Route::get('/', [FollowedShopController::class, 'index'])->name('index');
        Route::post('/', [FollowedShopController::class, 'store'])->name('store');
        Route::delete('/{vendorId}', [FollowedShopController::class, 'destroy'])
            ->whereNumber('vendorId')->name('destroy');
    });
});
