<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\ForgotPasswordController;

Route::prefix('v1')->group(function () {
    // Category CRUD
    Route::apiResource('categories', CategoryController::class);

    // Product CRUD
    Route::apiResource('products', ProductController::class);

    // Product search
    Route::get('products-search', [ProductController::class, 'search']);

    // Product filter
    Route::get('products-filter', [ProductController::class, 'filter']);
});

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [UserAuthController::class, 'register']);
    Route::post('/login', [UserAuthController::class, 'login']);
    Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Password reset routes
Route::prefix('password')->group(function () {
    Route::post('/request-otp', [ForgotPasswordController::class, 'requestOtp']);
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/reset', [ForgotPasswordController::class, 'resetPassword']);
});