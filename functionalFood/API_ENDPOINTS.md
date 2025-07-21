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

### User Management
- `GET /admin/users` - Danh sách user
- `GET /admin/users/{id}` - Chi tiết user
- `PUT /admin/users/{id}` - Cập nhật user
- `DELETE /admin/users/{id}` - Xóa user
- `POST /admin/users/{id}/toggle-status` - Khóa/mở khóa tài khoản user

## Thêm sản phẩm mới (Admin)

**Endpoint:**  
`POST /api/v1/admin/products`

**Yêu cầu:**  
- Header: `Authorization: Bearer <admin_token>`
- Content-Type: `multipart/form-data`

### Tham số gửi lên
| Tên trường                | Kiểu dữ liệu         | Bắt buộc | Mô tả                                                                 |
|---------------------------|----------------------|----------|-----------------------------------------------------------------------|
| name                      | string               | Có       | Tên sản phẩm                                                          |
| description               | string               | Không    | Mô tả sản phẩm                                                        |
| status                    | integer (0 hoặc 1)   | Có       | Trạng thái (0: ẩn, 1: hiển thị)                                       |
| product_type              | string               | Có       | Loại sản phẩm: `simple` (đơn), `variable` (có biến thể)               |
| price                     | number               | Có       | Giá sản phẩm gốc                                                      |
| discount                  | number               | Không    | Giá giảm (nếu có, nhỏ hơn price)                                      |
| stock_quantity            | integer              | Không    | Số lượng tồn kho (nếu là sản phẩm đơn)                                |
| category_id               | integer              | Có       | ID danh mục sản phẩm                                                  |
| images[]                  | file (image)         | Không    | Mảng ảnh sản phẩm (jpeg, png, jpg, gif, max 2MB/ảnh)                  |
| variants                  | array                | Không    | Mảng biến thể (nếu là sản phẩm variable)                              |
| variants[].name           | string               | Có*      | Tên biến thể (bắt buộc nếu có biến thể)                               |
| variants[].price          | number               | Có*      | Giá biến thể                                                          |
| variants[].stock          | integer              | Không    | Số lượng tồn kho biến thể                                             |
| variants[].sku            | string               | Không    | Mã SKU biến thể                                                       |
| variants[].image          | file (image)         | Không    | Ảnh riêng cho biến thể (jpeg, png, jpg, gif, max 2MB)                 |

> \* Các trường trong `variants[]` là bắt buộc nếu có biến thể.

### Ví dụ request (cURL)
```bash
curl -X POST http://localhost:8000/api/v1/admin/products \
  -H "Authorization: Bearer <admin_token>" \
  -F "name=Sữa hạt óc chó" \
  -F "description=Thức uống bổ dưỡng từ hạt óc chó" \
  -F "status=1" \
  -F "product_type=simple" \
  -F "price=50000" \
  -F "discount=10000" \
  -F "stock_quantity=100" \
  -F "category_id=1" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg"
```

**Với biến thể:**
```bash
curl -X POST http://localhost:8000/api/v1/admin/products \
  -H "Authorization: Bearer <admin_token>" \
  -F "name=Bánh quy mix vị" \
  -F "status=1" \
  -F "product_type=variable" \
  -F "price=0" \
  -F "category_id=2" \
  -F "variants[0][name]=Socola" \
  -F "variants[0][price]=30000" \
  -F "variants[0][stock]=50" \
  -F "variants[0][sku]=SOC-001" \
  -F "variants[0][image]=@/path/to/socola.jpg" \
  -F "variants[1][name]=Dâu" \
  -F "variants[1][price]=32000" \
  -F "variants[1][stock]=30" \
  -F "variants[1][sku]=DAU-001" \
  -F "variants[1][image]=@/path/to/dau.jpg"
```

### Response mẫu (201 Created)
```json
{
  "data": {
    "id": 10,
    "name": "Sữa hạt óc chó",
    "slug": "sua-hat-oc-cho",
    "description": "Thức uống bổ dưỡng từ hạt óc chó",
    "status": 1,
    "product_type": "simple",
    "price": 50000,
    "discount": 10000,
    "stock_quantity": 100,
    "category_id": 1,
    "image": "/storage/products/abcxyz.jpg",
    "images": [
      {
        "id": 1,
        "image_path": "/storage/products/abcxyz.jpg",
        "alt_text": "Sữa hạt óc chó",
        "is_main": true
      }
    ],
    "variants": [],
    "created_at": "2024-07-01T10:00:00.000000Z",
    "updated_at": "2024-07-01T10:00:00.000000Z"
  }
}
```

**Nếu có biến thể, trường `variants` sẽ chứa danh sách các biến thể.**

### Lỗi thường gặp
- 422: Thiếu trường bắt buộc, sai kiểu dữ liệu, ảnh quá lớn, giá trị discount lớn hơn price, v.v.
- 401: Không có hoặc sai token admin.

### Lưu ý
- Bắt buộc xác thực Bearer Token (admin)
- Gửi dữ liệu dạng `multipart/form-data` khi upload ảnh

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

---

## Khóa/Mở khóa tài khoản user (Admin)

**Endpoint:**  
`POST /api/v1/admin/users/{id}/toggle-status`

**Yêu cầu:**  
- Header: `Authorization: Bearer <admin_token>`
- Content-Type: `application/json`

**Mô tả:**  
Chỉ admin/super admin mới có quyền thực hiện. Endpoint này sẽ chuyển trạng thái user giữa `active` và `inactive`.

### Request
Không cần body, chỉ cần `{id}` user trên URL.

### Response mẫu
```json
{
  "message": "Khóa user thành công.",
  "status": "inactive"
}
```
Hoặc:
```json
{
  "message": "Kích hoạt user thành công.",
  "status": "active"
}
```

### Lỗi thường gặp
- 403: Không có quyền
- 404: Không tìm thấy user
- 422: Không thể tự khóa tài khoản của chính mình 