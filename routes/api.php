<?php

use App\Http\Controllers\V1\DashboardController;
use App\Http\Controllers\V1\LoginController;
use App\Http\Controllers\V1\VpnDashboardController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rate limiting
|--------------------------------------------------------------------------
|
| Centralized rate limiters for login and dashboards.
| Login: tied to user identifier + IP.
| Dashboards: tied to bearer token when possible, otherwise IP.
|
*/

RateLimiter::for('login', function (Request $request) {
    $identifier = (string) ($request->input('email')
        ?? $request->input('username')
        ?? 'guest');

    return [
        // 5 requests per minute per identifier + IP
        Limit::perMinute(5)->by($identifier . '|' . $request->ip()),
    ];
});

RateLimiter::for('account-dashboard', function (Request $request) {
    $key = 'account-dashboard:' . ($request->bearerToken() ?: $request->ip());

    return [
        // 60 requests per minute per token/IP
        Limit::perMinute(60)->by($key),
    ];
});

// vpn-dashboard limiter is defined in bootstrap/app.php (vpn-dashboard)

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Stellar Account dashboard (overview of all products)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('throttle:account-dashboard')
    ->name('account.dashboard');

// VPN dashboard & device management
Route::prefix('v1/vpndashboardcontroller')
    ->middleware('throttle:vpn-dashboard')
    ->group(function () {
        Route::get('dashboard', [VpnDashboardController::class, 'dashboard'])
            ->name('vpn.dashboard');

        Route::post('disconnect', [VpnDashboardController::class, 'disconnect'])
            ->name('vpn.disconnect');
    });

// Authentication + account creation + password reset
Route::prefix('v1/logincontroller')
    ->controller(LoginController::class)
    ->middleware('throttle:login')
    ->group(function () {
        // POST /api/v1/logincontroller/auth
        Route::post('auth', 'auth')->name('login.auth');

        // POST /api/v1/logincontroller/create
        Route::post('create', 'create')->name('login.create');

        // POST /api/v1/logincontroller/sendresetpasswordlink
        Route::post('sendresetpasswordlink', 'sendresetpasswordlink')
            ->name('login.send_reset_link');

        // POST /api/v1/logincontroller/resetpasswordupdate
        Route::post('resetpasswordupdate', 'resetpasswordupdate')
            ->name('login.reset_password');
    });
