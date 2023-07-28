<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OnboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "Api" middleware group. Make something great!
|
*/

Route::group([
    'middleware' => ['assign.guard:api'],
    'prefix' => 'users',
    'as' => 'user.',
], function () {

    Route::post('auth/login', [AuthController::class, 'login'])->name('login');
    Route::post('auth/register', [AuthController::class, 'register'])->name('register');

    Route::group([
        'middleware' => ['jwt.auth'],
    ], function () {

        Route::group([
            'prefix' => 'auth',
            'as' => 'auth.',
        ], function () {
            Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
            Route::post('resend-otp', [AuthController::class, 'reSendOtp'])->name('resend-otp');
            Route::get('me', [AuthController::class, 'me'])->name('me');
            Route::post('change-password', [AuthController::class, 'changePassword'])->name('change-password');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        });

        Route::group([
            'prefix' => 'onboard',
            'as' => 'onboard.',
        ], function () {
            Route::patch('', [OnboardController::class, 'updateProfile'])->name('update');
        });

    });

});




