<?php

use App\Http\Controllers\Frontend\CustomerDashboardController;
use App\Http\Controllers\Frontend\SupportDashboardController;
use App\Http\Controllers\Frontend\VendorDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Routes accessible to everyone, no authentication required.
*/
Route::get('/', function () {
    return response()->json([
        'message' => 'Home Page - Public Access',
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
| Routes for authenticated users with the "customer" role.
*/
Route::middleware(['auth:web', 'verified', 'check_role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');
    });

/*
|--------------------------------------------------------------------------
| Vendor Routes
|--------------------------------------------------------------------------
| Routes for authenticated users with the "vendor" role.
*/
Route::middleware(['auth:web', 'verified', 'check_role:vendor'])
    ->prefix('vendor')
    ->name('vendor.')
    ->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'index'])->name('dashboard');
    });
/*
|--------------------------------------------------------------------------
| Support Routes
|--------------------------------------------------------------------------
| Routes for authenticated users with the "vendor" role.
*/
Route::middleware(['auth:web', 'verified', 'check_role:support'])
    ->prefix('support')
    ->name('support.')
    ->group(function () {
        Route::get('/dashboard', [SupportDashboardController::class, 'index'])->name('dashboard');
    });



/*
|--------------------------------------------------------------------------
| Additional Route Files
|--------------------------------------------------------------------------
| Breeze/Jetstream auth routes + admin-specific route file.
*/
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/google.php';
require __DIR__ . '/account.php';
