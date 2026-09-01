<?php

use App\Http\Controllers\Frontend\RecentlyViewedController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Recently Viewed Routes
|--------------------------------------------------------------------------
| Spec ref: ZCOMUS_CUSTOMER_API_SPEC §13, items 62-63.
| Marked Optional in the spec — built anyway since a signed-in customer
| switching devices would otherwise lose their view history entirely
| (client-side localStorage doesn't follow the account).
*/
Route::middleware(['auth:web'])->group(function () {
    Route::get('/api/recently-viewed', [RecentlyViewedController::class, 'index'])
        ->name('api.recentlyViewed.index');
    Route::post('/api/recently-viewed', [RecentlyViewedController::class, 'store'])
        ->name('api.recentlyViewed.store');
});
