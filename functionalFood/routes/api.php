<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserAuthController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Middleware\CheckAdminToken;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::prefix('public')->group(function () {
        // Category CRUD
        Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
        
        // Product CRUD
        Route::apiResource('products', ProductController::class)->only(['index', 'show']);
        
        // Product search & filter
        Route::get('products-search', [ProductController::class, 'search']);
        Route::get('products-filter', [ProductController::class, 'filter']);
        
        // Public coupon routes
        Route::get('coupons/valid', [CouponController::class, 'getValidCoupons']);
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

    // User authenticated routes
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {
        // Order routes
        Route::prefix('orders')->group(function () {
            Route::post('/apply-coupon', [OrderController::class, 'applyCoupon']);
            Route::post('/place-order', [OrderController::class, 'placeOrder']);
            Route::get('/', [OrderController::class, 'getOrders']);
            Route::get('/stats', [OrderController::class, 'getOrderStats']);
            Route::get('/{id}', [OrderController::class, 'getOrderDetail']);
            Route::post('/{id}/cancel', [OrderController::class, 'cancelOrder']);
        });
    });

    // Admin routes
    Route::prefix('admin')->group(function () {
        // Admin authentication
        Route::prefix('auth')->group(function () {
            Route::post('/login', [UserAuthController::class, 'adminLogin']);
            Route::middleware(CheckAdminToken::class)->post('/logout', [UserAuthController::class, 'adminLogout']);
        });

        // Admin authenticated routes
        Route::middleware(CheckAdminToken::class)->group(function () {
            // Category management
            Route::apiResource('categories', CategoryController::class);
            
            // Product management
            Route::apiResource('products', ProductController::class);
            
            // User management
            Route::prefix('users')->group(function () {
                Route::get('/', [UserController::class, 'index']);
                Route::get('/stats', [UserController::class, 'getStats']);
                Route::get('/{id}', [UserController::class, 'show']);
                Route::put('/{id}', [UserController::class, 'update']);
                Route::delete('/{id}', [UserController::class, 'destroy']);
                Route::get('/export', [UserController::class, 'export']);
                Route::get('/customer-ranks', [UserController::class, 'getCustomerRanks']);
            });
            
            // Admin management
            Route::prefix('admins')->group(function () {
                Route::get('/', [AdminController::class, 'index']);
                Route::post('/', [AdminController::class, 'store']);
                Route::get('/stats', [AdminController::class, 'getStats']);
                Route::get('/profile', [AdminController::class, 'getProfile']);
                Route::put('/profile', [AdminController::class, 'updateProfile']);
                Route::post('/change-password', [AdminController::class, 'changePassword']);
                Route::get('/{id}', [AdminController::class, 'show']);
                Route::put('/{id}', [AdminController::class, 'update']);
                Route::delete('/{id}', [AdminController::class, 'destroy']);
                Route::post('/{id}/toggle-status', [AdminController::class, 'toggleStatus']);
            });
            
            // Coupon management
            Route::prefix('coupons')->group(function () {
                Route::get('/', [CouponController::class, 'index']);
                Route::post('/', [CouponController::class, 'store']);
                Route::get('/{id}', [CouponController::class, 'show']);
                Route::put('/{id}', [CouponController::class, 'update']);
                Route::delete('/{id}', [CouponController::class, 'destroy']);
                Route::post('/{id}/toggle-status', [CouponController::class, 'toggleStatus']);
                Route::get('/{id}/stats', [CouponController::class, 'getStats']);
            });
            
            // Order management
            Route::prefix('orders')->group(function () {
                Route::get('/', [OrderController::class, 'adminGetOrders']);
                Route::get('/{id}', [OrderController::class, 'adminGetOrderDetail']);
                Route::put('/{id}/status', [OrderController::class, 'updateOrderStatus']);
                Route::get('/stats', [OrderController::class, 'adminGetOrderStats']);
            });

            // Event management
            Route::prefix('events')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\EventController::class, 'index']);
                Route::post('/', [\App\Http\Controllers\Api\EventController::class, 'store']);
                Route::get('/{id}', [\App\Http\Controllers\Api\EventController::class, 'show']);
                Route::put('/{id}', [\App\Http\Controllers\Api\EventController::class, 'update']);
                Route::delete('/{id}', [\App\Http\Controllers\Api\EventController::class, 'destroy']);
                Route::post('/{id}/change-status', [\App\Http\Controllers\Api\EventController::class, 'changeStatus']);
                // Event products
                Route::get('/{eventId}/products', [\App\Http\Controllers\Api\EventController::class, 'products']);
                Route::post('/{eventId}/products', [\App\Http\Controllers\Api\EventController::class, 'addProduct']);
                Route::put('/{eventId}/products/{eventProductId}', [\App\Http\Controllers\Api\EventController::class, 'updateProduct']);
                Route::delete('/{eventId}/products/{eventProductId}', [\App\Http\Controllers\Api\EventController::class, 'removeProduct']);
            });
        });
    });
});