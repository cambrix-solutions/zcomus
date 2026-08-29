<?php

use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\VendorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catalog Routes
|--------------------------------------------------------------------------
| Spec ref: ZCOMUS_CUSTOMER_API_SPEC §4 (Catalog / browse).
| Fully public — no auth, no verified, no check_role. Anyone (including
| guests) can browse categories, products, and vendor storefronts.
*/
Route::prefix('api')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');

    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/by-slug/{slug}', [ProductController::class, 'showBySlug'])->name('api.products.showBySlug');
    Route::get('/products/{id}', [ProductController::class, 'show'])
        ->whereNumber('id')
        ->name('api.products.show');

    Route::get('/vendors', [VendorController::class, 'index'])->name('api.vendors.index');
    Route::get('/vendors/{slug}', [VendorController::class, 'show'])->name('api.vendors.show');
});
