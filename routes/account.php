<?php

use App\Http\Controllers\Frontend\AccountPreferenceController;
use App\Http\Controllers\Frontend\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Account Routes
|--------------------------------------------------------------------------
| Spec ref: ZCOMUS_CUSTOMER_API_SPEC §3 (Profile & account preferences).
|
| Deliberately NOT inside the customer/vendor/support check_role groups
| in web.php — "my profile" and "my notification/payment preferences"
| are the same shape and same behavior regardless of role, so any
| authenticated user (customer, vendor, or support) hits the same
| controller, and it always acts on $request->user() — never a route
| param — so there's no risk of one role reaching into another's data.
*/
Route::middleware(['auth:web', 'verified'])
    ->group(function () {
        Route::get('/api/user', [ProfileController::class, 'show'])->name('api.user');
        Route::put('/api/profile', [ProfileController::class, 'update'])->name('api.profile.update');
        Route::delete('/api/profile', [ProfileController::class, 'destroy'])->name('api.profile.destroy');

        Route::prefix('api/account')->name('api.account.')->group(function () {
            Route::get('/notification-preferences', [AccountPreferenceController::class, 'showNotifications'])
                ->name('notifications.show');
            Route::put('/notification-preferences', [AccountPreferenceController::class, 'updateNotifications'])
                ->name('notifications.update');

            Route::get('/payment-preferences', [AccountPreferenceController::class, 'showPayment'])
                ->name('payment.show');
            Route::put('/payment-preferences', [AccountPreferenceController::class, 'updatePayment'])
                ->name('payment.update');
        });
    });
