<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Middleware\CheckAdminToken;
use App\Http\Controllers\Api\CouponController;

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

// Order routes
Route::middleware('auth:sanctum')->prefix('orders')->group(function () {
    Route::post('/apply-coupon', [OrderController::class, 'applyCoupon']);
    Route::post('/placeOrder', [OrderController::class, 'placeOrder']); 
});

// admin authentication routes
Route::prefix('admin/auth')->group(function () {
    Route::post('/login', [UserAuthController::class, 'adminLogin']);
    Route::middleware(CheckAdminToken::class)->post('/logout', [UserAuthController::class, 'adminLogout']);
});


// Password reset routes
Route::prefix('password')->group(function () {
    Route::post('/request-otp', [ForgotPasswordController::class, 'requestOtp']);
    Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/reset', [ForgotPasswordController::class, 'resetPassword']);
});

Route::prefix('coupons')->group(function () {
    Route::get('/', [CouponController::class, 'index']);
    Route::post('/', [CouponController::class, 'store']);
    Route::put('/{id}', [CouponController::class, 'update']);
    Route::delete('/{id}', [CouponController::class, 'destroy']);
});

// ->middleware('auth:sanctum')