# API Endpoints Documentation

## Base URL
```
http://localhost:8000/api/v1
```

## Public Routes (Không cần authentication)

### Categories
- `GET /public/categories` - Lấy danh sách danh mục
- `GET /public/categories/{id}` - Lấy chi tiết danh mục

### Products
- `GET /public/products` - Lấy danh sách sản phẩm
- `GET /public/products/{id}` - Lấy chi tiết sản phẩm
- `GET /public/products-search` - Tìm kiếm sản phẩm
- `GET /public/products-filter` - Lọc sản phẩm

### Coupons
- `GET /public/coupons/valid` - Lấy danh sách coupon có hiệu lực cho user

## Authentication Routes

### User Authentication
- `POST /auth/register` - Đăng ký tài khoản
- `POST /auth/login` - Đăng nhập
- `POST /auth/logout` - Đăng xuất (cần token)

### Password Reset
- `POST /password/request-otp` - Gửi OTP
- `POST /password/verify-otp` - Xác thực OTP
- `POST /password/reset` - Đặt lại mật khẩu

## User Routes (Cần authentication)

### Orders
- `POST /user/orders/apply-coupon` - Áp dụng mã giảm giá
- `POST /user/orders/place-order` - Đặt hàng
- `GET /user/orders` - Lấy danh sách đơn hàng
- `GET /user/orders/stats` - Thống kê đơn hàng
- `GET /user/orders/{id}` - Chi tiết đơn hàng
- `POST /user/orders/{id}/cancel` - Hủy đơn hàng

## Admin Routes (Cần admin token)

### Admin Authentication
- `POST /admin/auth/login` - Đăng nhập admin
- `POST /admin/auth/logout` - Đăng xuất admin

### Category Management
- `GET /admin/categories` - Danh sách danh mục
- `POST /admin/categories` - Tạo danh mục
- `GET /admin/categories/{id}` - Chi tiết danh mục
- `PUT /admin/categories/{id}` - Cập nhật danh mục
- `DELETE /admin/categories/{id}` - Xóa danh mục

### Product Management
- `GET /admin/products` - Danh sách sản phẩm
- `POST /admin/products` - Tạo sản phẩm
- `GET /admin/products/{id}` - Chi tiết sản phẩm
- `PUT /admin/products/{id}` - Cập nhật sản phẩm
- `DELETE /admin/products/{id}` - Xóa sản phẩm

### Coupon Management
- `GET /admin/coupons` - Danh sách mã giảm giá
- `POST /admin/coupons` - Tạo mã giảm giá
- `GET /admin/coupons/{id}` - Chi tiết mã giảm giá
- `PUT /admin/coupons/{id}` - Cập nhật mã giảm giá
- `DELETE /admin/coupons/{id}` - Xóa mã giảm giá
- `POST /admin/coupons/{id}/toggle-status` - Kích hoạt/vô hiệu hóa
- `GET /admin/coupons/{id}/stats` - Thống kê sử dụng

### Order Management
- `GET /admin/orders` - Danh sách đơn hàng
- `GET /admin/orders/{id}` - Chi tiết đơn hàng
- `PUT /admin/orders/{id}/status` - Cập nhật trạng thái đơn hàng
- `GET /admin/orders/stats` - Thống kê đơn hàng

## Request/Response Examples

### Apply Coupon
```json
POST /user/orders/apply-coupon
{
  "coupon_code": "SALE20",
  "payment_method": "cod",
  "subtotal": 500000,
  "shipping_fee": 30000,
  "tax": 25000,
  "items": [
    {
      "product_id": 1,
      "price": 200000,
      "quantity": 2
    }
  ]
}

Response:
{
  "message": "Áp mã thành công.",
  "product_discount": 100000,
  "shipping_discount": 0,
  "total_discount": 100000,
  "final_shipping_fee": 30000,
  "total": 455000,
  "coupon_id": 1,
  "coupon_type": "percent",
  "coupon_value": 20
}
```

### Place Order
```json
POST /user/orders/place-order
{
  "items": [
    {
      "variant_id": 1,
      "quantity": 2
    }
  ],
  "name": "Nguyễn Văn A",
  "phone": "0123456789",
  "address": "123 Đường ABC, Quận 1, TP.HCM",
  "email": "nguyenvana@email.com",
  "payment_method": "cod",
  "coupon_id": 1,
  "subtotal": 500000,
  "shipping_fee": 30000,
  "tax": 25000,
  "discount": 100000,
  "total": 455000,
  "notes": "Giao hàng giờ hành chính"
}

Response:
{
  "message": "Đặt hàng thành công",
  "order_id": 1,
  "order_number": "ORD202501010001",
  "order": {
    "id": 1,
    "order_number": "ORD202501010001",
    "total": 455000,
    "status": "pending",
    "payment_status": "pending",
    "payment_method": "cod",
    "created_at": "2025-01-01T10:00:00.000000Z"
  }
}
```

### Create Coupon (Admin)
```json
POST /admin/coupons
{
  "code": "FREESHIP",
  "description": "Miễn phí vận chuyển cho đơn hàng từ 500k",
  "type": "fixed",
  "value": 0,
  "scope": "shipping",
  "free_shipping": true,
  "min_order_value": 500000,
  "usage_limit": 50,
  "only_once_per_user": false,
  "first_time_only": false,
  "is_active": true
}
```

## Authentication Headers

### User Authentication
```
Authorization: Bearer {user_token}
Content-Type: application/json
Accept: application/json
```

### Admin Authentication
```
Authorization: Bearer {admin_token}
Content-Type: application/json
Accept: application/json
```

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "coupon_code": ["The coupon code field is required."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "message": "Đơn hàng không tồn tại."
}
```

### Server Error (500)
```json
{
  "message": "Lỗi khi đặt hàng. Vui lòng thử lại sau.",
  "error": "Database connection failed"
}
```

## Coupon Types

### 1. Product Discount Coupons
- `scope`: order, product, category
- `type`: percent, fixed
- `value`: giá trị giảm

### 2. Shipping Discount Coupons
- `scope`: shipping
- `free_shipping`: boolean
- `shipping_discount`: số tiền giảm cố định
- `shipping_discount_percent`: % giảm giá vận chuyển

### 3. Combined Coupons
- Kết hợp giảm giá sản phẩm + vận chuyển

## Order Statuses

- `pending`: Chờ xác nhận
- `confirmed`: Đã xác nhận
- `processing`: Đang xử lý
- `shipped`: Đã gửi hàng
- `delivered`: Đã giao hàng
- `cancelled`: Đã hủy
- `refunded`: Đã hoàn tiền

## Payment Statuses

- `pending`: Chờ thanh toán
- `paid`: Đã thanh toán
- `failed`: Thanh toán thất bại
- `refunded`: Đã hoàn tiền

## Payment Methods

- `cod`: Thanh toán khi nhận hàng
- `bank_transfer`: Chuyển khoản ngân hàng
- `online_payment`: Thanh toán trực tuyến 