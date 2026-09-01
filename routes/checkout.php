<?php

use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\CouponController;
use App\Http\Controllers\Frontend\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Checkout, Coupons & Payments Routes
|--------------------------------------------------------------------------
| Spec ref: ZCOMUS_CUSTOMER_API_SPEC §6.
*/

// Guest or Auth
Route::post('/api/coupons/validate', [CouponController::class, 'check'])->name('api.coupons.validate');

// Auth only — guest checkout explicitly marked "Optional later" in the spec, not built.
Route::middleware(['auth:web'])->group(function () {
    Route::post('/api/checkout', [CheckoutController::class, 'store'])->name('api.checkout.store');
    Route::post('/api/payments/initiate', [PaymentController::class, 'initiate'])->name('api.payments.initiate');
    Route::get('/api/payments/{id}/status', [PaymentController::class, 'status'])
        ->whereNumber('id')
        ->name('api.payments.status');
});

// Server-to-server — see PaymentController::webhook() docblock: NOT
// signature-verified yet, lock this down before it's exposed publicly.
Route::post('/api/payments/webhook/{provider}', [PaymentController::class, 'webhook'])
    ->name('api.payments.webhook');
