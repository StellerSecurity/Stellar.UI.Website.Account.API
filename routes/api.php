<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use App\Http\Controllers\V1\LoginController;

/*
|--------------------------------------------------------------------------
| Rate limiting for login / password reset
|--------------------------------------------------------------------------
*/

RateLimiter::for('login', function (Request $request) {
    $identifier = (string) ($request->input('email')
        ?? $request->input('username')
        ?? 'guest');

    return [
        // 5 requests pr. minut pr. IP + user identifier
        Limit::perMinute(5)->by($identifier.'|'.$request->ip()),
    ];
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1/logincontroller')
    ->controller(LoginController::class)
    ->middleware('throttle:login')
    ->group(function () {
        // POST /api/v1/logincontroller/auth
        Route::post('auth', 'auth');

        // POST /api/v1/logincontroller/create
        Route::post('create', 'create');

        // POST /api/v1/logincontroller/sendresetpasswordlink
        Route::post('sendresetpasswordlink', 'sendresetpasswordlink');

        // POST /api/v1/logincontroller/resetpasswordupdate
        Route::post('resetpasswordupdate', 'resetpasswordupdate');
    });
