<?php

use App\Http\Controllers\Frontend\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order Routes
|--------------------------------------------------------------------------
| Spec ref: ZCOMUS_CUSTOMER_API_SPEC §7.
| All Auth-only except GET /api/tracking/{code}, which is deliberately
| public — "track by order code without full account context" is the
| whole point of that endpoint (item 40, spec marks it Auth or Public).
*/
Route::get('/api/tracking/{code}', [OrderController::class, 'trackByCode'])->name('api.tracking.byCode');

Route::middleware(['auth:web'])->group(function () {
    Route::get('/api/orders', [OrderController::class, 'index'])->name('api.orders.index');
    Route::get('/api/orders/{id}', [OrderController::class, 'show'])
        ->whereNumber('id')
        ->name('api.orders.show');
    Route::get('/api/orders/{id}/tracking', [OrderController::class, 'tracking'])
        ->whereNumber('id')
        ->name('api.orders.tracking');
    Route::post('/api/orders/{id}/reorder', [OrderController::class, 'reorder'])
        ->whereNumber('id')
        ->name('api.orders.reorder');
    Route::post('/api/orders/{id}/issues', [OrderController::class, 'reportIssue'])
        ->whereNumber('id')
        ->name('api.orders.issues.store');
});
