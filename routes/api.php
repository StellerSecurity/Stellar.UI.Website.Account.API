<?php

use App\Http\Controllers\V1\DashboardController;
use App\Http\Controllers\V1\LoginController;
use App\Http\Controllers\V1\VpnDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Stellar Account dashboard (overview of all products)
Route::get('/dashboard', [DashboardController::class, 'index'])
    // 60 requests per minute per IP
    ->middleware('throttle:60,1')
    ->name('account.dashboard');

// VPN dashboard & device management
Route::prefix('v1/vpndashboardcontroller')
    // 10 requests per minute per IP
    ->middleware('throttle:30,1')
    ->group(function () {
        Route::get('dashboard', [VpnDashboardController::class, 'dashboard'])
            ->name('vpn.dashboard');

        Route::post('disconnect', [VpnDashboardController::class, 'disconnect'])
            ->name('vpn.disconnect');
    });

// Authentication + account creation + password reset
Route::prefix('v1/logincontroller')
    ->controller(LoginController::class)
    // 10 requests per minute per IP across all login routes
    ->middleware('throttle:30,1')
    ->group(function () {
        Route::post('auth', 'auth')->name('login.auth');

        Route::post('create', 'create')->name('login.create');

        Route::post('sendresetpasswordlink', 'sendresetpasswordlink')
            ->name('login.send_reset_link');

        Route::post('resetpasswordupdate', 'resetpasswordupdate')
            ->name('login.reset_password');
    });
