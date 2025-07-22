<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Functional Food API Documentation",
 *     description="API documentation for Functional Food E-commerce Platform",
 *     @OA\Contact(
 *         email="admin@functionalfood.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 * 
 * @OA\Tag(
 *     name="Authentication",
 *     description="User and Admin authentication endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Public",
 *     description="Public endpoints for categories, products, and coupons"
 * )
 * 
 * @OA\Tag(
 *     name="User",
 *     description="User authenticated endpoints for orders and profile"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Categories",
 *     description="Admin endpoints for category management"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Products",
 *     description="Admin endpoints for product management"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Users",
 *     description="Admin endpoints for user management"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Admins",
 *     description="Admin endpoints for admin management"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Coupons",
 *     description="Admin endpoints for coupon management"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Orders",
 *     description="Admin endpoints for order management"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Events",
 *     description="Admin endpoints for event management"
 * )
 * 
 * @OA\Tag(
 *     name="Password Reset",
 *     description="Password reset endpoints"
 * )
 * 
 * @OA\Tag(
 *     name="Admin - Reviews",
 *     description="Admin endpoints for review management"
 * )
 * 
 * @OA\Tag(
 *     name="Product Reviews",
 *     description="Endpoints for product reviews"
 * )
 */
class OpenApiSpec extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","password","password_confirmation"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="phone", type="string", example="0123456789"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function register() {}

    /**
     * @OA\Post(
     *     path="/api/v1/auth/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="user", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/auth/login",
     *     tags={"Authentication"},
     *     summary="Admin login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="admin", type="object"),
     *             @OA\Property(property="token", type="string")
     *         )
     *     )
     * )
     */
    public function adminLogin() {}

    /**
     * @OA\Get(
     *     path="/api/v1/public/categories",
     *     tags={"Public"},
     *     summary="Get all categories",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getCategories() {}

    /**
     * @OA\Get(
     *     path="/api/v1/public/products",
     *     tags={"Public"},
     *     summary="Get all products",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search products by name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getProducts() {}

    /**
     * @OA\Get(
     *     path="/api/v1/public/products-search",
     *     tags={"Public"},
     *     summary="Search products",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */

     public function getProductById() {
        // This method is intentionally left empty as a placeholder for the OpenAPI spec.
        // Actual implementation would go here to retrieve a product by its ID.
        // Example implementation could be:
        // return Product::find($id);
        /**
         * @OA\Get(
         *     path="/api/v1/public/products/{id}",
         *    tags={"Public"},
         *    summary="Get product by ID",
         *    @OA\Parameter(
         *        name="id",
         *       in="path",
         *       required=true,
         *      @OA\Schema(type="integer")
         *     ),
         *    @OA\Response(
         *       response=200,
         *      description="Product details",
         *    @OA\JsonContent(
         *           @OA\Property(property="data", type="object")
         *        )
         *    )
         *     )
         */
     }
    public function searchProducts() {}

    /**
     * @OA\Get(
     *     path="/api/v1/public/products-filter",
     *     tags={"Public"},
     *     summary="Filter products",
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Filter by category",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="min_price",
     *         in="query",
     *         description="Minimum price",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="max_price",
     *         in="query",
     *         description="Maximum price",
     *         required=false,
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Filtered products",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function filterProducts() {}

    /**
     * @OA\Get(
     *     path="/api/v1/public/coupons/valid",
     *     tags={"Public"},
     *     summary="Get valid coupons",
     *     @OA\Response(
     *         response=200,
     *         description="List of valid coupons",
     *         @OA\JsonContent(
     *             @OA\Property(property="coupons", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getValidCoupons() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/orders/apply-coupon",
     *     tags={"User"},
     *     summary="Apply coupon to order",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"coupon_code","payment_method","subtotal","items"},
     *             @OA\Property(property="coupon_code", type="string", example="SAVE20", description="Mã coupon"),
     *             @OA\Property(property="payment_method", type="string", enum={"cod","bank_transfer","online_payment"}, example="cod", description="Phương thức thanh toán"),
     *             @OA\Property(property="subtotal", type="number", example=100000, description="Tổng tiền hàng"),
     *             @OA\Property(property="shipping_fee", type="number", example=30000, description="Phí vận chuyển"),
     *             @OA\Property(property="tax", type="number", example=5000, description="Thuế"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="Danh sách sản phẩm",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1, description="ID sản phẩm"),
     *                     @OA\Property(property="price", type="number", example=50000, description="Giá sản phẩm"),
     *                     @OA\Property(property="quantity", type="integer", example=2, description="Số lượng")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon applied successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Áp mã thành công."),
     *             @OA\Property(property="product_discount", type="number", example=20000, description="Giảm giá sản phẩm"),
     *             @OA\Property(property="shipping_discount", type="number", example=0, description="Giảm giá vận chuyển"),
     *             @OA\Property(property="total_discount", type="number", example=20000, description="Tổng giảm giá"),
     *             @OA\Property(property="final_shipping_fee", type="number", example=30000, description="Phí vận chuyển cuối cùng"),
     *             @OA\Property(property="total", type="number", example=115000, description="Tổng tiền cuối cùng"),
     *             @OA\Property(property="coupon_id", type="integer", example=1, description="ID coupon"),
     *             @OA\Property(property="coupon_type", type="string", enum={"percent","fixed"}, example="percent", description="Loại coupon"),
     *             @OA\Property(property="coupon_value", type="number", example=20, description="Giá trị coupon"),
     *             @OA\Property(property="free_shipping", type="boolean", example=false, description="Miễn phí vận chuyển"),
     *             @OA\Property(property="shipping_discount_amount", type="number", example=0, description="Số tiền giảm vận chuyển"),
     *             @OA\Property(property="shipping_discount_percent", type="number", example=0, description="Phần trăm giảm vận chuyển")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or coupon not valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mã không hợp lệ hoặc đã hết hạn.", description="Các thông báo lỗi có thể gặp: Mã không hợp lệ hoặc đã hết hạn, Hạng thành viên của bạn không đủ điều kiện, Mã chỉ áp dụng cho đơn hàng đầu tiên, Bạn đã sử dụng mã này rồi, Mã đã hết lượt sử dụng, Mã chỉ áp dụng vào một số ngày nhất định, Mã chỉ áp dụng trong khung giờ quy định, Giá trị đơn hàng chưa đủ để áp mã, Giá trị đơn hàng vượt quá giới hạn áp dụng mã, Phương thức thanh toán không được áp dụng mã này, Không có sản phẩm phù hợp để áp mã, Không có sản phẩm thuộc danh mục áp dụng mã")
     *         )
     *     )
     * )
     */
    public function applyCoupon() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/orders/place-order",
     *     tags={"User"},
     *     summary="Place a new order",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items","name","phone","address","email","payment_method"},
     *             @OA\Property(property="items", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="phone", type="string", example="0123456789"),
     *             @OA\Property(property="address", type="string", example="123 Main St"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="payment_method", type="string", example="cod"),
     *             @OA\Property(property="coupon_code", type="string", example="SAVE20"),
     *             @OA\Property(property="note", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order placed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     )
     * )
     */
    public function placeOrder() {}

    /**
     * @OA\Get(
     *     path="/api/v1/user/orders",
     *     tags={"User"},
     *     summary="Get user orders",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="orders", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getUserOrders() {}

    /**
     * @OA\Get(
     *     path="/api/v1/user/orders/{id}",
     *     tags={"User"},
     *     summary="Get order detail",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="order", type="object")
     *         )
     *     )
     * )
     */
    public function getOrderDetail() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/orders/{id}/cancel",
     *     tags={"User"},
     *     summary="Cancel order",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function cancelOrder() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users",
     *     tags={"Admin - Users"},
     *     summary="Get all users (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by user status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active","inactive","suspended"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by name, email, or phone",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="customer_rank_id",
     *         in="query",
     *         description="Filter by customer rank",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             @OA\Property(property="users", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getUsers() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/stats",
     *     tags={"Admin - Users"},
     *     summary="Get user statistics (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="stats", type="object")
     *         )
     *     )
     * )
     */
    public function getUserStats() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/export",
     *     tags={"Admin - Users"},
     *     summary="Export users data (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Users data for export",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     )
     * )
     */
    public function exportUsers() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/users/customer-ranks",
     *     tags={"Admin - Users"},
     *     summary="Get customer ranks (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of customer ranks",
     *         @OA\JsonContent(
     *             @OA\Property(property="ranks", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getCustomerRanks() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/admins",
     *     tags={"Admin - Admins"},
     *     summary="Create new admin (Super Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password","password_confirmation","role"},
     *             @OA\Property(property="name", type="string", example="Admin User"),
     *             @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", example="password123"),
     *             @OA\Property(property="role", type="string", enum={"admin","super_admin"}, example="admin"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Admin created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="admin", type="object")
     *         )
     *     )
     * )
     */
    public function createAdmin() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/admins/profile",
     *     tags={"Admin - Admins"},
     *     summary="Get admin profile",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Admin profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="admin", type="object")
     *         )
     *     )
     * )
     */
    public function getAdminProfile() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/admins/change-password",
     *     tags={"Admin - Admins"},
     *     summary="Change admin password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string"),
     *             @OA\Property(property="new_password", type="string"),
     *             @OA\Property(property="new_password_confirmation", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function changeAdminPassword() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/events",
     *     tags={"Admin - Events"},
     *     summary="Get all events (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by event status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"draft","active","paused","ended"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by event name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="is_featured",
     *         in="query",
     *         description="Filter featured events",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of events",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getEvents() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/events",
     *     tags={"Admin - Events"},
     *     summary="Create new event (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","start_time","end_time","discount_type","discount_value"},
     *             @OA\Property(property="name", type="string", example="Flash Sale Weekend"),
     *             @OA\Property(property="description", type="string", example="Special weekend sale with great discounts"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-01-03T23:59:59Z"),
     *             @OA\Property(property="status", type="string", enum={"draft","active","paused","ended"}, example="draft"),
     *             @OA\Property(property="banner_image", type="string", example="banner.jpg"),
     *             @OA\Property(property="discount_type", type="string", enum={"percentage","fixed"}, example="percentage"),
     *             @OA\Property(property="discount_value", type="number", example=20),
     *             @OA\Property(property="is_featured", type="boolean", example=false),
     *             @OA\Property(property="sort_order", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function createEvent() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/events/{id}/change-status",
     *     tags={"Admin - Events"},
     *     summary="Change event status (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"draft","active","paused","ended"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event status changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     )
     * )
     */
    public function changeEventStatus() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/events/{eventId}/products",
     *     tags={"Admin - Events"},
     *     summary="Add product to event (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="eventId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id","event_price","original_price","discount_price","quantity_limit"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="event_price", type="number", example=80.00),
     *             @OA\Property(property="original_price", type="number", example=100.00),
     *             @OA\Property(property="discount_price", type="number", example=80.00),
     *             @OA\Property(property="quantity_limit", type="integer", example=50),
     *             @OA\Property(property="status", type="string", enum={"active","inactive","sold_out"}, example="active"),
     *             @OA\Property(property="sort_order", type="integer", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product added to event successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function addProductToEvent() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/coupons",
     *     tags={"Admin - Coupons"},
     *     summary="Get all coupons (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by coupon status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active","inactive"})
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by coupon type",
     *         required=false,
     *         @OA\Schema(type="string", enum={"percentage","fixed"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of coupons",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getCoupons() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/coupons",
     *     tags={"Admin - Coupons"},
     *     summary="Create new coupon (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"code","type","value","min_order_amount","max_uses","valid_from","valid_to"},
     *             @OA\Property(property="code", type="string", example="SAVE20"),
     *             @OA\Property(property="name", type="string", example="Save 20%"),
     *             @OA\Property(property="description", type="string", example="Get 20% off on orders above $50"),
     *             @OA\Property(property="type", type="string", enum={"percentage","fixed"}, example="percentage"),
     *             @OA\Property(property="value", type="number", example=20),
     *             @OA\Property(property="min_order_amount", type="number", example=50.00),
     *             @OA\Property(property="max_uses", type="integer", example=100),
     *             @OA\Property(property="valid_from", type="string", format="date-time"),
     *             @OA\Property(property="valid_to", type="string", format="date-time"),
     *             @OA\Property(property="free_shipping", type="boolean", example=false),
     *             @OA\Property(property="shipping_discount", type="number", example=0),
     *             @OA\Property(property="shipping_discount_percent", type="number", example=0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Coupon created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="coupon", type="object")
     *         )
     *     )
     * )
     */
    public function createCoupon() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/coupons/{id}/toggle-status",
     *     tags={"Admin - Coupons"},
     *     summary="Toggle coupon status (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon status toggled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="status", type="string")
     *         )
     *     )
     * )
     */
    public function toggleCouponStatus() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/coupons/{id}/stats",
     *     tags={"Admin - Coupons"},
     *     summary="Get coupon statistics (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="stats", type="object")
     *         )
     *     )
     * )
     */
    public function getCouponStats() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/orders",
     *     tags={"Admin - Orders"},
     *     summary="Get all orders (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","confirmed","processing","shipped","delivered","cancelled"})
     *     ),
     *     @OA\Parameter(
     *         name="payment_status",
     *         in="query",
     *         description="Filter by payment status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","paid","failed"})
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search by order number, customer name, or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="orders", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
     */
    public function getOrders() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/orders/stats",
     *     tags={"Admin - Orders"},
     *     summary="Get order statistics (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Order statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="stats", type="object")
     *         )
     *     )
     * )
     */
    public function getOrderStats() {}

    /**
     * @OA\Put(
     *     path="/api/v1/admin/orders/{id}/status",
     *     tags={"Admin - Orders"},
     *     summary="Update order status (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending","confirmed","processing","shipped","delivered","cancelled"}),
     *             @OA\Property(property="tracking_number", type="string"),
     *             @OA\Property(property="note", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="order", type="object")
     *         )
     *     )
     * )
     */
    public function updateOrderStatus() {}

    /**
     * @OA\Post(
     *     path="/api/v1/password/request-otp",
     *     tags={"Password Reset"},
     *     summary="Request OTP for password reset",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP sent to your email")
     *         )
     *     )
     * )
     */
    public function requestOtp() {}

    /**
     * @OA\Post(
     *     path="/api/v1/password/verify-otp",
     *     tags={"Password Reset"},
     *     summary="Verify OTP for password reset",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","otp"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="otp", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OTP verified successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="OTP verified successfully"),
     *             @OA\Property(property="reset_token", type="string")
     *         )
     *     )
     * )
     */
    public function verifyOtp() {}

    /**
     * @OA\Post(
     *     path="/api/v1/password/reset",
     *     tags={"Password Reset"},
     *     summary="Reset password with token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","reset_token","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="reset_token", type="string", example="token123"),
     *             @OA\Property(property="password", type="string", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     )
     * )
     */
    public function resetPassword() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/coupons/{coupon_id}/save",
     *     tags={"User"},
     *     summary="Save or receive a coupon for the user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="coupon_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Coupon saved",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Coupon already saved"
     *     )
     * )
     */
    public function saveUserCoupon() {}

    /**
     * @OA\Get(
     *     path="/api/v1/user/coupons",
     *     tags={"User"},
     *     summary="Get list of coupons the user currently has (not used)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user coupons",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function listUserCoupons() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/coupons/{user_coupon_id}/use",
     *     tags={"User"},
     *     summary="Mark a coupon as used for the user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="user_coupon_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon marked as used",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function useUserCoupon() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories",
     *     tags={"Admin - Categories"},
     *     summary="Get all categories (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function adminGetCategories() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/categories",
     *     tags={"Admin - Categories"},
     *     summary="Create a new category (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Beverages"),
     *             @OA\Property(property="description", type="string", example="All kinds of drinks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function adminCreateCategory() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/categories/{id}",
     *     tags={"Admin - Categories"},
     *     summary="Get category detail (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function adminShowCategory() {}

    /**
     * @OA\Put(
     *     path="/api/v1/admin/categories/{id}",
     *     tags={"Admin - Categories"},
     *     summary="Update a category (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Beverages"),
     *             @OA\Property(property="description", type="string", example="All kinds of drinks")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function adminUpdateCategory() {}

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/categories/{id}",
     *     tags={"Admin - Categories"},
     *     summary="Delete a category (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Category deleted successfully"
     *     )
     * )
     */
    public function adminDeleteCategory() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/products",
     *     tags={"Admin - Products"},
     *     summary="Get all products (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of products",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function adminGetProducts() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/products",
     *     summary="Thêm sản phẩm mới (Admin)",
     *     tags={"Admin - Products"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","status","product_type","price","category_id"},
     *                 @OA\Property(property="name", type="string", example="Sữa hạt óc chó"),
     *                 @OA\Property(property="description", type="string", example="Thức uống bổ dưỡng từ hạt óc chó"),
     *                 @OA\Property(property="status", type="integer", enum={0,1}, example=1, description="0: ẩn, 1: hiển thị"),
     *                 @OA\Property(property="product_type", type="string", enum={"simple","variable"}, example="simple"),
     *                 @OA\Property(property="price", type="number", format="float", example=50000),
     *                 @OA\Property(property="discount", type="number", format="float", example=10000),
     *                 @OA\Property(property="stock_quantity", type="integer", example=100),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary")
     *                 ),
     *                 @OA\Property(
     *                     property="variants",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="name", type="string", example="Socola"),
     *                         @OA\Property(property="price", type="number", format="float", example=30000),
     *                         @OA\Property(property="stock", type="integer", example=50),
     *                         @OA\Property(property="sku", type="string", example="SOC-001"),
     *                         @OA\Property(property="image", type="string", format="binary")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     responses={
     *         @OA\Response(
     *             response=201,
     *             description="Tạo sản phẩm thành công",
     *             @OA\JsonContent(
     *                 @OA\Property(property="data", type="object",
     *                     @OA\Property(property="id", type="integer", example=10),
     *                     @OA\Property(property="name", type="string", example="Sữa hạt óc chó"),
     *                     @OA\Property(property="slug", type="string", example="sua-hat-oc-cho"),
     *                     @OA\Property(property="description", type="string", example="Thức uống bổ dưỡng từ hạt óc chó"),
     *                     @OA\Property(property="status", type="integer", example=1),
     *                     @OA\Property(property="product_type", type="string", example="simple"),
     *                     @OA\Property(property="price", type="number", example=50000),
     *                     @OA\Property(property="discount", type="number", example=10000),
     *                     @OA\Property(property="stock_quantity", type="integer", example=100),
     *                     @OA\Property(property="category_id", type="integer", example=1),
     *                     @OA\Property(property="image", type="string", example="/storage/products/abcxyz.jpg"),
     *                     @OA\Property(property="images", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="variants", type="array", @OA\Items(type="object")),
     *                     @OA\Property(property="created_at", type="string", example="2024-07-01T10:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2024-07-01T10:00:00.000000Z")
     *                 )
     *             )
     *         ),
     *         @OA\Response(
     *             response=422,
     *             description="Lỗi validate hoặc thiếu trường bắt buộc"
     *         ),
     *         @OA\Response(
     *             response=401,
     *             description="Không có hoặc sai token admin"
     *         )
     *     }
     * )
     */
    public function adminCreateProduct() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/products/{id}",
     *     tags={"Admin - Products"},
     *     summary="Get product detail (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function adminShowProduct() {}

    /**
     * @OA\Put(
     *     path="/api/v1/admin/products/{id}",
     *     tags={"Admin - Products"},
     *     summary="Update a product (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","category_id","price"},
     *             @OA\Property(property="name", type="string", example="Green Tea"),
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="price", type="number", example=10.5),
     *             @OA\Property(property="description", type="string", example="A healthy green tea drink")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function adminUpdateProduct() {}

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/products/{id}",
     *     tags={"Admin - Products"},
     *     summary="Delete a product (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product deleted successfully"
     *     )
     * )
     */
    public function adminDeleteProduct() {}

    /**
     * @OA\Get(
     *     path="/api/v1/user/wishlist",
     *     tags={"User"},
     *     summary="Get user's wishlist",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of wishlist items",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getWishlist() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/wishlist",
     *     tags={"User"},
     *     summary="Add product to wishlist",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id"},
     *             @OA\Property(property="product_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Added to wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function addWishlist() {}

    /**
     * @OA\Delete(
     *     path="/api/v1/user/wishlist/{product_id}",
     *     tags={"User"},
     *     summary="Remove product from wishlist",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Removed from wishlist",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found in wishlist"
     *     )
     * )
     */
    public function removeWishlist() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/reviews",
     *     tags={"Admin - Reviews"},
     *     summary="Get all product reviews (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by review status (pending, approved, rejected)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","approved","rejected"})
     *     ),
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Filter by product ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of product reviews",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function adminGetReviews() {}

    /**
     * @OA\Put(
     *     path="/api/v1/admin/reviews/{review_id}/status",
     *     tags={"Admin - Reviews"},
     *     summary="Approve or reject a product review (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="review_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"action"},
     *             @OA\Property(property="action", type="string", enum={"approve","reject"}, example="approve"),
     *             @OA\Property(property="admin_note", type="string", example="Contains inappropriate language.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review status updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="review", type="object")
     *         )
     *     )
     * )
     */
    public function adminUpdateReviewStatus() {}

    /**
     * @OA\Get(
     *     path="/api/v1/user/products/{product_id}/reviews",
     *     tags={"Product Reviews"},
     *     summary="Get approved reviews for a product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of approved reviews",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getProductReviews() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/products/{product_id}/reviews",
     *     tags={"Product Reviews"},
     *     summary="Submit a review for a product (user must be authenticated and have purchased the product)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"rating"},
     *                 @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *                 @OA\Property(property="comment", type="string", example="Great product!"),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Upload multiple images"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review submitted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="images", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="image_path", type="string")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Review contains inappropriate content and was rejected"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="User has not purchased this product"
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="User has already reviewed this product"
     *     )
     * )
     */
    public function submitProductReview() {}

    /**
     * @OA\Put(
     *     path="/api/v1/user/products/{product_id}/reviews/{review_id}",
     *     tags={"Product Reviews"},
     *     summary="Update your review for a product (user must be authenticated)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="review_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"rating"},
     *                 @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=4),
     *                 @OA\Property(property="comment", type="string", example="Updated review comment."),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Upload multiple images"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="rating", type="integer"),
     *                 @OA\Property(property="comment", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="created_at", type="string"),
     *                 @OA\Property(property="images", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="image_path", type="string")
     *                 ))
     *             )
     *         )
     *     )
     * )
     */
    public function updateProductReview() {}

    /**
     * @OA\Delete(
     *     path="/api/v1/user/products/{product_id}/reviews/{review_id}",
     *     tags={"Product Reviews"},
     *     summary="Delete your review for a product (user must be authenticated)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="review_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted"
     *     )
     * )
     */
    public function deleteProductReview() {}

    /**
     * @OA\Post(
     *     path="/api/v1/reviews/{review_id}/report",
     *     tags={"Product Reviews"},
     *     summary="Report a product review (user must be authenticated)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="review_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reason"},
     *             @OA\Property(property="reason", type="string", example="Spam or inappropriate content.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Report submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="You have already reported this review"
     *     )
     * )
     */
    public function reportProductReview() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/overview",
     *     tags={"Admin - Stats"},
     *     summary="Get dashboard overview statistics (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Overview statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_revenue", type="number", example=1000000),
     *             @OA\Property(property="orders_today", type="integer", example=10),
     *             @OA\Property(property="orders_this_week", type="integer", example=50),
     *             @OA\Property(property="orders_this_month", type="integer", example=200),
     *             @OA\Property(property="products_sold", type="integer", example=500),
     *             @OA\Property(property="total_users", type="integer", example=1000)
     *         )
     *     )
     * )
     */
    public function statsOverview() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/revenue",
     *     tags={"Admin - Stats"},
     *     summary="Get revenue statistics by date range (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         description="Start date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         description="End date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Revenue statistics by date",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="date", type="string", example="2024-07-01"),
     *             @OA\Property(property="revenue", type="number", example=100000),
     *             @OA\Property(property="orders", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="total", type="number"),
     *                 @OA\Property(property="created_at", type="string")
     *             ))
     *         ))
     *     )
     * )
     */
    public function statsRevenue() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/revenue-summary",
     *     tags={"Admin - Stats"},
     *     summary="Get revenue summary by type (today/week/month/year)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Summary type (today, week, month, year)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"today","week","month","year"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Revenue summary",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="date", type="string"),
     *             @OA\Property(property="revenue", type="number"),
     *             @OA\Property(property="orders", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="order_number", type="string"),
     *                 @OA\Property(property="customer", type="string"),
     *                 @OA\Property(property="total", type="number"),
     *                 @OA\Property(property="created_at", type="string")
     *             ))
     *         ))
     *     )
     * )
     */
    public function statsRevenueSummary() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/orders-by-status",
     *     tags={"Admin - Stats"},
     *     summary="Get order count by status (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Order count by status",
     *         @OA\JsonContent(
     *             @OA\Property(property="pending", type="integer"),
     *             @OA\Property(property="confirmed", type="integer"),
     *             @OA\Property(property="processing", type="integer"),
     *             @OA\Property(property="shipped", type="integer"),
     *             @OA\Property(property="delivered", type="integer"),
     *             @OA\Property(property="cancelled", type="integer"),
     *             @OA\Property(property="refunded", type="integer")
     *         )
     *     )
     * )
     */
    public function statsOrdersByStatus() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/top-products",
     *     tags={"Admin - Stats"},
     *     summary="Get top selling products (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Top selling products",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="total_sold", type="integer"),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="image", type="string")
     *             )
     *         ))
     *     )
     * )
     */
    public function statsTopProducts() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/slow-products",
     *     tags={"Admin - Stats"},
     *     summary="Get slow selling products (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Slow selling products",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="total_sold", type="integer"),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="image", type="string")
     *             )
     *         ))
     *     )
     * )
     */
    public function statsSlowProducts() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/new-users",
     *     tags={"Admin - Stats"},
     *     summary="Get new registered users (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Type of period (week, month)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"week","month"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="New users statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="count", type="integer"),
     *             @OA\Property(property="users", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="created_at", type="string")
     *             ))
     *         )
     *     )
     * )
     */
    public function statsNewUsers() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/revenue-by-category",
     *     tags={"Admin - Stats"},
     *     summary="Get revenue by product category (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Revenue by category",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="revenue", type="number")
     *         ))
     *     )
     * )
     */
    public function statsRevenueByCategory() {}

    /**
     * @OA\Get(
     *     path="/api/v1/admin/statistics/top-customers",
     *     tags={"Admin - Stats"},
     *     summary="Get top customers by spending (Admin only)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Top customers",
     *         @OA\JsonContent(type="array", @OA\Items(
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="orders_count", type="integer"),
     *             @OA\Property(property="total_spent", type="number"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="email", type="string")
     *             )
     *         ))
     *     )
     * )
     */
    public function statsTopCustomers() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/change-password",
     *     tags={"User"},
     *     summary="Change password for authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password","new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="oldpass123"),
     *             @OA\Property(property="new_password", type="string", example="newpass456"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="newpass456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Đổi mật khẩu thành công.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or wrong current password",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mật khẩu hiện tại không đúng.")
     *         )
     *     )
     * )
     */
    public function changeUserPassword() {}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/users/{id}/toggle-status",
     *     tags={"Admin - Users"},
     *     summary="Khóa hoặc mở khóa tài khoản user (Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Khóa/mở khóa user thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Khóa user thành công."),
     *             @OA\Property(property="status", type="string", example="inactive")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy user"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Không thể tự khóa tài khoản của chính mình"
     *     )
     * )
     */
    public function toggleUserStatus() {}

    /**
     * @OA\Put(
     *     path="/api/v1/admin/users/{id}",
     *     tags={"Admin - Users"},
     *     summary="Cập nhật thông tin user (Admin)",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="email", type="string", example="user@email.com"),
     *             @OA\Property(property="phone", type="string", example="0123456789"),
     *             @OA\Property(property="customer_rank_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"active","inactive","suspended"}, example="active"),
     *             @OA\Property(property="password", type="string", example="newpassword"),
     *             @OA\Property(property="password_confirmation", type="string", example="newpassword")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cập nhật thông tin user thành công."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Không tìm thấy user"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi validate"
     *     )
     * )
     */
    public function updateUser() {}

    /**
     * @OA\Patch(
     *     path="/api/v1/user/profile",
     *     tags={"User"},
     *     summary="Cập nhật thông tin người dùng (user tự cập nhật)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","phone","address"},
     *             @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *             @OA\Property(property="phone", type="string", example="0123456789"),
     *             @OA\Property(property="address", type="string", example="123 Đường ABC, Quận 1, TP.HCM")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thông tin thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cập nhật thông tin thành công."),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     *                 @OA\Property(property="phone", type="string", example="0123456789"),
     *                 @OA\Property(property="address", type="string", example="123 Đường ABC, Quận 1, TP.HCM"),
     *                 @OA\Property(property="email", type="string", example="user@email.com"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="customer_rank_id", type="integer", example=1),
     *                 @OA\Property(property="created_at", type="string", example="2024-07-01T10:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2024-07-01T10:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa đăng nhập"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi validate"
     *     )
     * )
     */
    public function updateUserProfile() {}

    /**
     * @OA\Post(
     *     path="/api/v1/user/avatar",
     *     tags={"User"},
     *     summary="Cập nhật avatar người dùng (user tự cập nhật)",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"avatar"},
     *                 @OA\Property(
     *                     property="avatar",
     *                     type="string",
     *                     format="binary",
     *                     description="Ảnh đại diện (jpeg, png, jpg, gif, tối đa 2MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật avatar thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Cập nhật avatar thành công."),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Lỗi validate"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa đăng nhập"
     *     )
     * )
     */
    public function updateUserAvatar() {}
}
