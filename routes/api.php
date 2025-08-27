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
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;


Route::prefix('v1')->group(function () {
    // Public routes
    Route::prefix('public')->group(function () {
        // Category CRUD
        Route::apiResource('public-categories', CategoryController::class)
            ->only(['index', 'show'])
            ->names('public-categories');
        
        // Product CRUD
        Route::apiResource('products', ProductController::class)
            ->only(['index', 'show'])
            ->names('public-products');
        

        
        // Product search & filter
        Route::get('products-search', [ProductController::class, 'search'])->name('public-products.search');
        Route::get('products-filter', [ProductController::class, 'filter'])->name('public-products.filter');
        
        // Public coupon routes
        Route::get('coupons/valid', [CouponController::class, 'getValidCoupons'])->name('public-coupons.valid');
        
        // Public Event API
        Route::get('events', [EventController::class, 'publicIndex'])->name('public-events.index');
        Route::get('events/{id}', [EventController::class, 'publicShow'])->name('public-events.show');
    });

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('update-avatar', [UserAuthController::class, 'updateAvatar'])->middleware('auth:sanctum')->name('auth.update-avatar');
        Route::post('/register', [UserAuthController::class, 'register'])->name('auth.register');
        Route::post('/login', [UserAuthController::class, 'login'])->name('auth.login');
        Route::post('/logout', [UserAuthController::class, 'logout'])->middleware('auth:sanctum')->name('auth.logout');
    });

    // Password reset routes
    Route::prefix('password')->group(function () {
        Route::post('/request-otp', [ForgotPasswordController::class, 'requestOtp'])->name('password.request-otp');
        Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify-otp');
        Route::post('/reset', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');
    });

    // User authenticated routes
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {
        // Order routes
        Route::prefix('orders')->group(function () {
            Route::post('/apply-coupon', [OrderController::class, 'applyCoupon'])->name('user-orders.apply-coupon');
            Route::post('/place-order', [OrderController::class, 'placeOrder'])->name('user-orders.place-order');
            Route::get('/', [OrderController::class, 'getOrders'])->name('user-orders.index');
            Route::get('/stats', [OrderController::class, 'getOrderStats'])->name('user-orders.stats');
            Route::get('/{id}', [OrderController::class, 'getOrderDetail'])->name('user-orders.show');
            Route::post('/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('user-orders.cancel');
        });
        // User coupon routes
        Route::post('coupons/{coupon_id}/save', [UserCouponController::class, 'saveCoupon'])->name('user-coupons.save');
        Route::get('coupons', [UserCouponController::class, 'listCoupons'])->name('user-coupons.index');
        Route::post('coupons/{user_coupon_id}/use', [UserCouponController::class, 'useCoupon'])->name('user-coupons.use');
        // Wishlist routes
        Route::get('wishlist', [WishlistController::class, 'index'])->name('user-wishlist.index');
        Route::post('wishlist', [WishlistController::class, 'store'])->name('user-wishlist.store');
        Route::delete('wishlist/{product_id}', [WishlistController::class, 'destroy'])->name('user-wishlist.destroy');
        // Product review routes
        Route::get('products/{product_id}/reviews', [ProductReviewController::class, 'index'])->name('user-reviews.index');
        Route::post('products/{product_id}/reviews', [ProductReviewController::class, 'store'])->name('user-reviews.store');
        Route::put('products/{product_id}/reviews/{review_id}', [ProductReviewController::class, 'update'])->name('user-reviews.update');
        Route::delete('products/{product_id}/reviews/{review_id}', [ProductReviewController::class, 'destroy'])->name('user-reviews.destroy');
        // Review report (user)
        Route::post('reviews/{review_id}/report', [ReviewReportController::class, 'report'])->name('user-reviews.report');
        // Đổi mật khẩu user
        Route::post('change-password', [UserController::class, 'changePassword'])->name('user.change-password');
        Route::patch('profile', [UserController::class, 'updateProfile'])->name('user.update-profile');
        // Địa chỉ giao hàng (user CRUD)
        Route::apiResource('addresses', \App\Http\Controllers\Api\UserAddressController::class)
            ->names('user-addresses');
    });

    // Public Post API
    Route::get('posts', [\App\Http\Controllers\Api\PostController::class, 'index'])->name('public-posts.index');
    Route::get('posts/{id}', [\App\Http\Controllers\Api\PostController::class, 'show'])->name('public-posts.show');

    // Payment routes
    Route::post('/create-payment', [PaymentController::class, 'createPayment']);
    Route::get('/vnpay-return', [PaymentController::class, 'vnpayReturn']);
    Route::get('/vnpay-ipn', [PaymentController::class, 'vnpayIpn']);
    
    // Test route for VNPay payment (no authentication required)
    Route::post('/test-vnpay-payment', [PaymentController::class, 'testPayment']);
    
    // Test route for debugging (no authentication required)
    Route::get('/test-health', function() {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is working',
            'timestamp' => now(),
            'cors_enabled' => true,
            'cors_origin' => request()->header('Origin'),
            'cors_method' => request()->method()
        ]);
    });
    
    // CORS test route
    Route::options('/test-cors', function() {
        return response('', 200);
    });
    
    Route::post('/test-order', function(Request $request) {
        try {
            return response()->json([
                'status' => 'success',
                'message' => 'Test order endpoint working',
                'data' => $request->all(),
                'timestamp' => now()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });

    // Admin routes
    Route::prefix('admin')->group(function () {
        // Admin authentication
        Route::prefix('auth')->group(function () {
            Route::post('/login', [UserAuthController::class, 'adminLogin'])->name('admin-auth.login');
            Route::middleware(CheckAdminToken::class)->post('/logout', [UserAuthController::class, 'adminLogout'])->name('admin-auth.logout');
        });

        // Admin authenticated routes
        Route::middleware(CheckAdminToken::class)->group(function () {
            // Category management
            Route::apiResource('admin-categories', CategoryController::class)
                ->names('admin-categories');
            
            // Product management
            Route::apiResource('products', ProductController::class)
                ->names('admin-products');
            
            // User management
            Route::prefix('users')->group(function () {
                Route::get('/customer-ranks', [UserController::class, 'getCustomerRanks'])->name('admin-users.customer-ranks');
                Route::get('/', [UserController::class, 'index'])->name('admin-users.index');
                Route::get('/stats', [UserController::class, 'getStats'])->name('admin-users.stats');
                Route::get('/export', [UserController::class, 'export'])->name('admin-users.export');
                Route::get('/{id}', [UserController::class, 'show'])->name('admin-users.show');
                Route::put('/{id}', [UserController::class, 'update'])->name('admin-users.update');
                Route::delete('/{id}', [UserController::class, 'destroy'])->name('admin-users.destroy');
                Route::post('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin-users.toggle-status');
            });
            
            // Admin management
            Route::prefix('admins')->group(function () {
                Route::get('/', [AdminController::class, 'index'])->name('admin-admins.index');
                Route::post('/', [AdminController::class, 'store'])->name('admin-admins.store');
                Route::get('/stats', [AdminController::class, 'getStats'])->name('admin-admins.stats');
                Route::get('/profile', [AdminController::class, 'getProfile'])->name('admin-admins.profile');
                Route::put('/profile', [AdminController::class, 'updateProfile'])->name('admin-admins.update-profile');
                Route::post('/change-password', [AdminController::class, 'changePassword'])->name('admin-admins.change-password');
                Route::get('/{id}', [AdminController::class, 'show'])->name('admin-admins.show');
                Route::put('/{id}', [AdminController::class, 'update'])->name('admin-admins.update');
                Route::delete('/{id}', [AdminController::class, 'destroy'])->name('admin-admins.destroy');
                Route::post('/{id}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admin-admins.toggle-status');
            });
            
            // Coupon management
            Route::prefix('coupons')->group(function () {
                Route::get('/', [CouponController::class, 'index'])->name('admin-coupons.index');
                Route::post('/', [CouponController::class, 'store'])->name('admin-coupons.store');
                Route::get('/{id}', [CouponController::class, 'show'])->name('admin-coupons.show');
                Route::put('/{id}', [CouponController::class, 'update'])->name('admin-coupons.update');
                Route::delete('/{id}', [CouponController::class, 'destroy'])->name('admin-coupons.destroy');
                Route::post('/{id}/toggle-status', [CouponController::class, 'toggleStatus'])->name('admin-coupons.toggle-status');
                Route::get('/{id}/stats', [CouponController::class, 'getStats'])->name('admin-coupons.stats');
            });
            
            // Order management
            Route::prefix('orders')->group(function () {
                Route::get('/', [OrderController::class, 'adminGetOrders'])->name('admin-orders.index');
                Route::get('/{id}', [OrderController::class, 'adminGetOrderDetail'])->name('admin-orders.show');
                Route::put('/{id}/status', [OrderController::class, 'updateOrderStatus'])->name('admin-orders.update-status');
                Route::get('/stats', [OrderController::class, 'adminGetOrderStats'])->name('admin-orders.stats');
            });

            // Event management
            Route::prefix('events')->group(function () {
                Route::get('/', [EventController::class, 'index'])->name('admin-events.index');
                Route::post('/', [EventController::class, 'store'])->name('admin-events.store');
                Route::get('/{id}', [EventController::class, 'show'])->name('admin-events.show');
                Route::put('/{id}', [EventController::class, 'update'])->name('admin-events.update');
                Route::delete('/{id}', [EventController::class, 'destroy'])->name('admin-events.destroy');
                Route::post('/{id}/change-status', [EventController::class, 'changeStatus'])->name('admin-events.change-status');
                // Event products
                Route::get('/{eventId}/products', [EventController::class, 'products'])->name('admin-events.products');
                Route::post('/{eventId}/products', [EventController::class, 'addProduct'])->name('admin-events.add-product');
                Route::put('/{eventId}/products/{eventProductId}', [EventController::class, 'updateProduct'])->name('admin-events.update-product');
                Route::delete('/{eventId}/products/{eventProductId}', [EventController::class, 'removeProduct'])->name('admin-events.remove-product');
            });

            Route::prefix('statistics')->group(function () {
                Route::get('/overview', [StatsController::class, 'overview'])->name('admin-statistics.overview');
                Route::get('/revenue', [StatsController::class, 'revenue'])->name('admin-statistics.revenue');
                Route::get('/orders-by-status', [StatsController::class, 'ordersByStatus'])->name('admin-statistics.orders-by-status');
                Route::get('/top-products', [StatsController::class, 'topProducts'])->name('admin-statistics.top-products');
                Route::get('/new-users', [StatsController::class, 'newUsers'])->name('admin-statistics.new-users');
                Route::get('/visits', [StatsController::class, 'visits'])->name('admin-statistics.visits');
                Route::get('/revenue-by-category', [StatsController::class, 'revenueByCategory'])->name('admin-statistics.revenue-by-category');
                Route::get('/top-customers', [StatsController::class, 'topCustomers'])->name('admin-statistics.top-customers');
                Route::get('/revenue-summary', [StatsController::class, 'revenueSummary'])->name('admin-statistics.revenue-summary');
                Route::get('/slow-products', [StatsController::class, 'slowProducts'])->name('admin-statistics.slow-products');

            });

            // Review report (admin)
            Route::get('review-reports', [ReviewReportController::class, 'index'])->name('admin-review-reports.index');
            Route::put('review-reports/{report_id}/resolve', [ReviewReportController::class, 'resolve'])->name('admin-review-reports.resolve');
            // Product review (admin)
            Route::get('reviews', [ProductReviewAdminController::class, 'index'])->name('admin-reviews.index');
            Route::put('reviews/{review_id}/status', [ProductReviewAdminController::class, 'updateStatus'])->name('admin-reviews.update-status');
            // Banned words management (admin)
            Route::get('banned-words', [\App\Http\Controllers\Api\BannedWordController::class, 'index'])->name('admin-banned-words.index');
            Route::post('banned-words', [\App\Http\Controllers\Api\BannedWordController::class, 'store'])->name('admin-banned-words.store');
            Route::put('banned-words/{id}', [\App\Http\Controllers\Api\BannedWordController::class, 'update'])->name('admin-banned-words.update');
            Route::delete('banned-words/{id}', [\App\Http\Controllers\Api\BannedWordController::class, 'destroy'])->name('admin-banned-words.destroy');
            // Xem địa chỉ giao hàng của user (admin chỉ được xem)
            Route::get('user-addresses', [\App\Http\Controllers\Api\UserAddressController::class, 'index'])->name('admin-user-addresses.index');
            Route::get('user-addresses/{id}', [\App\Http\Controllers\Api\UserAddressController::class, 'show'])->name('admin-user-addresses.show');
        });

        // Admin Post API
        Route::get('posts', [\App\Http\Controllers\Api\AdminPostController::class, 'index'])->name('admin-posts.index');
        Route::post('posts', [\App\Http\Controllers\Api\AdminPostController::class, 'store'])->name('admin-posts.store');
        Route::get('posts/{id}', [\App\Http\Controllers\Api\AdminPostController::class, 'show'])->name('admin-posts.show');
        Route::put('posts/{id}', [\App\Http\Controllers\Api\AdminPostController::class, 'update'])->name('admin-posts.update');
        Route::delete('posts/{id}', [\App\Http\Controllers\Api\AdminPostController::class, 'destroy'])->name('admin-posts.destroy');
        Route::patch('posts/{id}/toggle-status', [\App\Http\Controllers\Api\AdminPostController::class, 'toggleStatus'])->name('admin-posts.toggle-status');
    });
}); // Close v1 prefix group