<?php

use App\Http\Controllers\Frontend\CartController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
| Spec ref: ZCOMUS_CUSTOMER_API_SPEC §5.
| GET/POST/PATCH/DELETE on /api/cart are "Guest or Auth" — no auth
| middleware at all; CartController resolves the right cart internally
| (see ResolvesCart). Only /api/cart/merge requires a logged-in user.
*/
Route::prefix('api/cart')->group(function () {
    Route::get('/', [CartController::class, 'show'])->name('api.cart.show');
    Route::post('/', [CartController::class, 'store'])->name('api.cart.store');
    Route::patch('/{productId}', [CartController::class, 'update'])
        ->whereNumber('productId')
        ->name('api.cart.update');
    Route::delete('/{productId}', [CartController::class, 'destroyItem'])
        ->whereNumber('productId')
        ->name('api.cart.destroyItem');
    Route::delete('/', [CartController::class, 'clear'])->name('api.cart.clear');
});

Route::middleware(['auth:web'])
    ->post('/api/cart/merge', [CartController::class, 'merge'])
    ->name('api.cart.merge');
