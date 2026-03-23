<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\PointController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Webhook\MokaPosController;
use App\Http\Controllers\Webhook\WooCommerceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('google', [AuthController::class, 'googleLogin']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
            Route::post('resend-otp', [AuthController::class, 'resendOtp']);
        });
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        Route::post('device/token', function (Request $request) {
            $request->validate(['fcm_token' => 'required|string']);
            $request->user()->update(['fcm_token' => $request->fcm_token]);
            return response()->json(['message' => 'FCM token updated.']);
        });

        Route::prefix('brands')->group(function () {
            Route::get('/', [BrandController::class, 'index']);
            Route::post('{brand}/join', [BrandController::class, 'join']);
            Route::get('{brand}/profile', [BrandController::class, 'profile']);
        });

        Route::prefix('points')->group(function () {
            Route::get('/', [PointController::class, 'allBalance']);
            Route::get('{brand}/balance', [PointController::class, 'balance']);
            Route::get('{brand}/history', [PointController::class, 'history']);
            Route::post('earn/instore', [PointController::class, 'earnInstore']);
        });

        Route::prefix('rewards')->group(function () {
            Route::get('{brand}', [RewardController::class, 'index']);
            Route::post('{reward}/redeem', [RewardController::class, 'redeem']);
            Route::get('my/redemptions', [RewardController::class, 'myRedemptions']);
        });

    });

    Route::prefix('webhooks')->group(function () {
        Route::post('woocommerce/{brandSlug}', [WooCommerceController::class, 'handle'])
            ->middleware('throttle:webhook');
        Route::post('mokapos/{brandSlug}', [MokaPosController::class, 'handle'])
            ->middleware('throttle:webhook');
    });

});
