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
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\UserCouponController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\ReviewReportController;
use App\Http\Controllers\Api\ProductReviewAdminController;
use App\Http\Controllers\Api\StatsController;


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
        Route::post('update-avatar', [UserAuthController::class, 'updateAvatar'])->middleware('auth:sanctum');
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
        // User coupon routes
        Route::post('coupons/{coupon_id}/save', [UserCouponController::class, 'saveCoupon']);
        Route::get('coupons', [UserCouponController::class, 'listCoupons']);
        Route::post('coupons/{user_coupon_id}/use', [UserCouponController::class, 'useCoupon']);
        // Wishlist routes
        Route::get('wishlist', [WishlistController::class, 'index']);
        Route::post('wishlist', [WishlistController::class, 'store']);
        Route::delete('wishlist/{product_id}', [WishlistController::class, 'destroy']);
        // Product review routes
        Route::get('products/{product_id}/reviews', [ProductReviewController::class, 'index']);
        Route::post('products/{product_id}/reviews', [ProductReviewController::class, 'store']);
        Route::put('products/{product_id}/reviews/{review_id}', [ProductReviewController::class, 'update']);
        Route::delete('products/{product_id}/reviews/{review_id}', [ProductReviewController::class, 'destroy']);
        // Review report (user)
        Route::post('reviews/{review_id}/report', [ReviewReportController::class, 'report']);
        // Đổi mật khẩu user
        Route::post('change-password', [UserController::class, 'changePassword']);
        Route::patch('profile', [UserController::class, 'updateProfile']);
        // Địa chỉ giao hàng (user CRUD)
        Route::apiResource('addresses', \App\Http\Controllers\Api\UserAddressController::class);
    });

    // Public Post API
    Route::get('posts', [\App\Http\Controllers\Api\PostController::class, 'index']);
    Route::get('posts/{id}', [\App\Http\Controllers\Api\PostController::class, 'show']);
    
    // Public Event API
    Route::get('events', [EventController::class, 'publicIndex']);
    Route::get('events/{id}', [EventController::class, 'publicShow']);

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
                Route::get('/customer-ranks', [UserController::class, 'getCustomerRanks']);
                Route::get('/', [UserController::class, 'index']);
                Route::get('/stats', [UserController::class, 'getStats']);
                Route::get('/export', [UserController::class, 'export']);
                Route::get('/{id}', [UserController::class, 'show']);
                Route::put('/{id}', [UserController::class, 'update']);
                Route::delete('/{id}', [UserController::class, 'destroy']);
                Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
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
                Route::get('/', [EventController::class, 'index']);
                Route::post('/', [EventController::class, 'store']);
                Route::get('/{id}', [EventController::class, 'show']);
                Route::put('/{id}', [EventController::class, 'update']);
                Route::delete('/{id}', [EventController::class, 'destroy']);
                Route::post('/{id}/change-status', [EventController::class, 'changeStatus']);
                // Event products
                Route::get('/{eventId}/products', [EventController::class, 'products']);
                Route::post('/{eventId}/products', [EventController::class, 'addProduct']);
                Route::put('/{eventId}/products/{eventProductId}', [EventController::class, 'updateProduct']);
                Route::delete('/{eventId}/products/{eventProductId}', [EventController::class, 'removeProduct']);
            });

            Route::prefix('statistics')->group(function () {
                Route::get('/overview', [StatsController::class, 'overview']);
                Route::get('/revenue', [StatsController::class, 'revenue']);
                Route::get('/orders-by-status', [StatsController::class, 'ordersByStatus']);
                Route::get('/top-products', [StatsController::class, 'topProducts']);
                Route::get('/new-users', [StatsController::class, 'newUsers']);
                Route::get('/visits', [StatsController::class, 'visits']);
                Route::get('/revenue-by-category', [StatsController::class, 'revenueByCategory']);
                Route::get('/top-customers', [StatsController::class, 'topCustomers']);
                Route::get('/revenue-summary', [StatsController::class, 'revenueSummary']);
                Route::get('/slow-products', [StatsController::class, 'slowProducts']);

            });

            // Review report (admin)
            Route::get('review-reports', [ReviewReportController::class, 'index']);
            Route::put('review-reports/{report_id}/resolve', [ReviewReportController::class, 'resolve']);
            // Product review (admin)
            Route::get('reviews', [ProductReviewAdminController::class, 'index']);
            Route::put('reviews/{review_id}/status', [ProductReviewAdminController::class, 'updateStatus']);
            // Banned words management (admin)
            Route::get('banned-words', [\App\Http\Controllers\Api\BannedWordController::class, 'index']);
            Route::post('banned-words', [\App\Http\Controllers\Api\BannedWordController::class, 'store']);
            Route::put('banned-words/{id}', [\App\Http\Controllers\Api\BannedWordController::class, 'update']);
            Route::delete('banned-words/{id}', [\App\Http\Controllers\Api\BannedWordController::class, 'destroy']);
            // Xem địa chỉ giao hàng của user (admin chỉ được xem)
            Route::get('user-addresses', [\App\Http\Controllers\Api\UserAddressController::class, 'index']);
            Route::get('user-addresses/{id}', [\App\Http\Controllers\Api\UserAddressController::class, 'show']);
        });

        // Admin Post API
        Route::get('posts', [\App\Http\Controllers\Api\AdminPostController::class, 'index']);
        Route::post('posts', [\App\Http\Controllers\Api\AdminPostController::class, 'store']);
        Route::get('posts/{id}', [\App\Http\Controllers\Api\AdminPostController::class, 'show']);
        Route::put('posts/{id}', [\App\Http\Controllers\Api\AdminPostController::class, 'update']);
        Route::delete('posts/{id}', [\App\Http\Controllers\Api\AdminPostController::class, 'destroy']);
        Route::patch('posts/{id}/toggle-status', [\App\Http\Controllers\Api\AdminPostController::class, 'toggleStatus']);
    });
});