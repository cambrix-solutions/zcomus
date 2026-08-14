<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Guest Routes
|--------------------------------------------------------------------------
| Routes accessible only to unauthenticated admins (login, password
| reset flow).
*/
Route::middleware(['guest:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

/*
|--------------------------------------------------------------------------
| Admin Authenticated Routes
|--------------------------------------------------------------------------
| Routes accessible only to authenticated admins (dashboard, password
| confirmation, logout).
*/
Route::middleware(['auth:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])->name('password.confirm.store');
        Route::put('password', [PasswordController::class, 'update'])->name('password.update');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
