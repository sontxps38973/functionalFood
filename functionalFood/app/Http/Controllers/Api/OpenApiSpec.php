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
}
