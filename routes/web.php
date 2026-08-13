<?php
use App\Http\Controllers\Frontend\CustomerDashboardController;
use App\Http\Controllers\Frontend\VendorDashboardController;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
| Routes accessible to everyone, no authentication required.
*/
Route::get('/', function () {
    return view('welcome');
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
| Additional Route Files
|--------------------------------------------------------------------------
| Breeze/Jetstream auth routes + admin-specific route file.
*/
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
