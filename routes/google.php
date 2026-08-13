<?php

use App\Http\Controllers\Frontend\Auth\GoogleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Google OAuth Routes (Customer Only)
|--------------------------------------------------------------------------
| Handles "Continue with Google" for customers. Vendors and admins are
| provisioned manually and are not part of this flow.
*/

Route::middleware(['guest:web'])
    ->prefix('customer/auth/google')
    ->name('customer.google.')
    ->group(function () {
        Route::get('/', [GoogleController::class, 'redirect'])
            ->name('redirect');

        Route::get('/callback', [GoogleController::class, 'callback'])
            ->name('callback');
    });
