# API Documentation - Events Management

## Tổng quan
Module Events quản lý các sự kiện khuyến mãi, giảm giá sản phẩm trong hệ thống e-commerce. Bao gồm cả API public cho người dùng và API admin cho quản trị viên.

## Public Events API

### 1. Lấy danh sách sự kiện public
**Endpoint:** `GET /api/v1/events`

**Mô tả:** Lấy danh sách các sự kiện đang hoạt động (status = active) cho người dùng xem.

**Query Parameters:**
- `search` (string, optional): Tìm kiếm theo tên sự kiện
- `is_featured` (boolean, optional): Lọc sự kiện nổi bật
- `type` (string, optional): Loại sự kiện
  - `running`: Sự kiện đang diễn ra
  - `upcoming`: Sự kiện sắp diễn ra
  - `ended`: Sự kiện đã kết thúc
- `per_page` (integer, optional): Số lượng item trên trang (mặc định: 15)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Siêu sale tháng 12",
      "description": "Giảm giá lên đến 50% cho tất cả sản phẩm",
      "start_time": "2024-12-01T00:00:00.000000Z",
      "end_time": "2024-12-31T23:59:59.000000Z",
      "status": "active",
      "banner_image": "/storage/events/banner1.jpg",
      "discount_type": "percentage",
      "discount_value": 50,
      "is_featured": true,
      "sort_order": 1,
      "event_products": [
        {
          "id": 1,
          "product_id": 1,
          "event_price": 150000,
          "original_price": 300000,
          "discount_price": 150000,
          "quantity_limit": 100,
          "status": "active",
          "product": {
            "id": 1,
            "name": "Omega 3",
            "image": "/storage/products/omega3.jpg"
          }
        }
      ]
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 10
  }
}
```

### 2. Xem chi tiết sự kiện public
**Endpoint:** `GET /api/v1/events/{id}`

**Mô tả:** Lấy thông tin chi tiết của một sự kiện đang hoạt động.

**Path Parameters:**
- `id` (integer, required): ID của sự kiện

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Siêu sale tháng 12",
    "description": "Giảm giá lên đến 50% cho tất cả sản phẩm",
    "start_time": "2024-12-01T00:00:00.000000Z",
    "end_time": "2024-12-31T23:59:59.000000Z",
    "status": "active",
    "banner_image": "/storage/events/banner1.jpg",
    "discount_type": "percentage",
    "discount_value": 50,
    "is_featured": true,
    "sort_order": 1,
    "event_products": [
      {
        "id": 1,
        "product_id": 1,
        "event_price": 150000,
        "original_price": 300000,
        "discount_price": 150000,
        "quantity_limit": 100,
        "status": "active",
        "product": {
          "id": 1,
          "name": "Omega 3",
          "image": "/storage/products/omega3.jpg"
        }
      }
    ]
  }
}
```

## Admin Events API

### 1. Lấy danh sách sự kiện (Admin)
**Endpoint:** `GET /api/v1/admin/events`

**Mô tả:** Lấy danh sách tất cả sự kiện (bao gồm cả draft, active, paused, ended) cho admin quản lý.

**Authentication:** Bearer Token (Admin)

**Query Parameters:**
- `status` (string, optional): Lọc theo trạng thái (draft, active, paused, ended)
- `search` (string, optional): Tìm kiếm theo tên sự kiện
- `is_featured` (boolean, optional): Lọc sự kiện nổi bật
- `per_page` (integer, optional): Số lượng item trên trang (mặc định: 15)

**Response:** Tương tự như public API nhưng bao gồm tất cả trạng thái.

### 2. Tạo sự kiện mới (Admin)
**Endpoint:** `POST /api/v1/admin/events`

**Mô tả:** Tạo một sự kiện mới.

**Authentication:** Bearer Token (Admin)

**Request Body:**
```json
{
  "name": "Siêu sale tháng 12",
  "description": "Giảm giá lên đến 50% cho tất cả sản phẩm",
  "start_time": "2024-12-01T00:00:00.000000Z",
  "end_time": "2024-12-31T23:59:59.000000Z",
  "status": "draft",
  "banner_image": "/storage/events/banner1.jpg",
  "discount_type": "percentage",
  "discount_value": 50,
  "is_featured": true,
  "sort_order": 1
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `description`: nullable, string
- `start_time`: required, date
- `end_time`: required, date, after:start_time
- `status`: in:draft,active,paused,ended
- `banner_image`: nullable, string
- `discount_type`: required, in:percentage,fixed
- `discount_value`: required, numeric, min:0
- `is_featured`: boolean
- `sort_order`: integer

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Siêu sale tháng 12",
    "description": "Giảm giá lên đến 50% cho tất cả sản phẩm",
    "start_time": "2024-12-01T00:00:00.000000Z",
    "end_time": "2024-12-31T23:59:59.000000Z",
    "status": "draft",
    "banner_image": "/storage/events/banner1.jpg",
    "discount_type": "percentage",
    "discount_value": 50,
    "is_featured": true,
    "sort_order": 1,
    "event_products": []
  }
}
```

### 3. Xem chi tiết sự kiện (Admin)
**Endpoint:** `GET /api/v1/admin/events/{id}`

**Mô tả:** Lấy thông tin chi tiết của một sự kiện (bao gồm tất cả trạng thái).

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `id` (integer, required): ID của sự kiện

**Response:** Tương tự như public API.

### 4. Cập nhật sự kiện (Admin)
**Endpoint:** `PUT /api/v1/admin/events/{id}`

**Mô tả:** Cập nhật thông tin sự kiện.

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `id` (integer, required): ID của sự kiện

**Request Body:** Tương tự như tạo mới, nhưng tất cả fields đều optional.

**Response:** Tương tự như tạo mới.

### 5. Xóa sự kiện (Admin)
**Endpoint:** `DELETE /api/v1/admin/events/{id}`

**Mô tả:** Xóa một sự kiện.

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `id` (integer, required): ID của sự kiện

**Response:**
```json
{
  "message": "Xóa sự kiện thành công."
}
```

### 6. Thay đổi trạng thái sự kiện (Admin)
**Endpoint:** `POST /api/v1/admin/events/{id}/change-status`

**Mô tả:** Thay đổi trạng thái của sự kiện.

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `id` (integer, required): ID của sự kiện

**Request Body:**
```json
{
  "status": "active"
}
```

**Validation Rules:**
- `status`: required, in:draft,active,paused,ended

**Response:**
```json
{
  "message": "Cập nhật trạng thái thành công.",
  "status": "active"
}
```

## Event Products Management (Admin)

### 1. Lấy danh sách sản phẩm trong sự kiện
**Endpoint:** `GET /api/v1/admin/events/{eventId}/products`

**Mô tả:** Lấy danh sách sản phẩm thuộc một sự kiện.

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `eventId` (integer, required): ID của sự kiện

**Query Parameters:**
- `status` (string, optional): Lọc theo trạng thái sản phẩm (active, inactive, sold_out)
- `per_page` (integer, optional): Số lượng item trên trang (mặc định: 20)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "product_id": 1,
      "event_price": 150000,
      "original_price": 300000,
      "discount_price": 150000,
      "quantity_limit": 100,
      "status": "active",
      "sort_order": 1,
      "product": {
        "id": 1,
        "name": "Omega 3",
        "image": "/storage/products/omega3.jpg"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 20,
    "total": 5
  }
}
```

### 2. Thêm sản phẩm vào sự kiện
**Endpoint:** `POST /api/v1/admin/events/{eventId}/products`

**Mô tả:** Thêm một sản phẩm vào sự kiện.

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `eventId` (integer, required): ID của sự kiện

**Request Body:**
```json
{
  "product_id": 1,
  "event_price": 150000,
  "original_price": 300000,
  "discount_price": 150000,
  "quantity_limit": 100,
  "status": "active",
  "sort_order": 1
}
```

**Validation Rules:**
- `product_id`: required, exists:products,id
- `event_price`: required, numeric, min:0
- `original_price`: required, numeric, min:0
- `discount_price`: required, numeric, min:0
- `quantity_limit`: required, integer, min:0
- `status`: in:active,inactive,sold_out
- `sort_order`: integer

**Response:**
```json
{
  "data": {
    "id": 1,
    "event_id": 1,
    "product_id": 1,
    "event_price": 150000,
    "original_price": 300000,
    "discount_price": 150000,
    "quantity_limit": 100,
    "status": "active",
    "sort_order": 1,
    "product": {
      "id": 1,
      "name": "Omega 3",
      "image": "/storage/products/omega3.jpg"
    }
  }
}
```

### 3. Cập nhật sản phẩm trong sự kiện
**Endpoint:** `PUT /api/v1/admin/events/{eventId}/products/{eventProductId}`

**Mô tả:** Cập nhật thông tin sản phẩm trong sự kiện.

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `eventId` (integer, required): ID của sự kiện
- `eventProductId` (integer, required): ID của event product

**Request Body:** Tương tự như thêm mới, nhưng tất cả fields đều optional.

**Response:** Tương tự như thêm mới.

### 4. Xóa sản phẩm khỏi sự kiện
**Endpoint:** `DELETE /api/v1/admin/events/{eventId}/products/{eventProductId}`

**Mô tả:** Xóa một sản phẩm khỏi sự kiện.

**Authentication:** Bearer Token (Admin)

**Path Parameters:**
- `eventId` (integer, required): ID của sự kiện
- `eventProductId` (integer, required): ID của event product

**Response:**
```json
{
  "message": "Xóa sản phẩm khỏi sự kiện thành công."
}
```

## Trạng thái sự kiện

- **draft**: Bản nháp, chưa công khai
- **active**: Đang hoạt động, hiển thị cho người dùng
- **paused**: Tạm dừng, không hiển thị cho người dùng
- **ended**: Đã kết thúc, không hiển thị cho người dùng

## Trạng thái sản phẩm trong sự kiện

- **active**: Đang bán trong sự kiện
- **inactive**: Tạm dừng bán trong sự kiện
- **sold_out**: Hết hàng trong sự kiện

## Lưu ý

1. **Public API** chỉ hiển thị sự kiện có trạng thái `active`
2. **Admin API** có thể quản lý tất cả trạng thái sự kiện
3. **Event Products** là mối quan hệ giữa sự kiện và sản phẩm, cho phép thiết lập giá riêng cho từng sản phẩm trong sự kiện
4. **Discount Type**: 
   - `percentage`: Giảm giá theo phần trăm
   - `fixed`: Giảm giá theo số tiền cố định
5. **Sort Order**: Thứ tự hiển thị của sự kiện (số nhỏ hiển thị trước)
