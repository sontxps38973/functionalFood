
# API Documentation - Product API với Event Pricing

## Get Product Detail
- **URL:** `GET /api/v1/public/products/{product}`
- **Description:** Lấy chi tiết sản phẩm với thông tin giá cả sự kiện đầy đủ
- **Authentication:** Không bắt buộc

## Get Products List
- **URL:** `GET /api/v1/public/products`
- **Description:** Lấy danh sách sản phẩm với thông tin giá cả sự kiện
- **Authentication:** Không bắt buộc
- **Parameters:**
  - `per_page` (optional): Số lượng items per page (default: 10)
  - `page` (optional): Trang hiện tại
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Sản phẩm A",
      "slug": "san-pham-a",
      "description": "Mô tả sản phẩm",
      "status": 1,
      "product_type": "simple",
      "category": {
        "id": 1,
        "name": "Danh mục A",
        "slug": "danh-muc-a"
      },
      
      // Giá cả cơ bản
      "base_price": 1000000,
      "base_discount": 0,
      
      // Thông tin sự kiện
      "event_price": 500000,
      "original_price": 1000000,
      "event_info": {
        "id": 1,
        "name": "Siêu Sale Tháng 12",
        "description": "Giảm giá lên đến 50%",
        "start_time": "2024-12-01T00:00:00.000000Z",
        "end_time": "2024-12-31T23:59:59.000000Z",
        "discount_type": "percentage",
        "discount_value": 50,
        "banner_image": "events/banner1.jpg"
      },
      
      // Giá hiển thị (ưu tiên sự kiện)
      "display_price": 500000,
      "display_discount_price": 500000,
      
      "stock_quantity": 100,
      "image": "http://localhost:8000/storage/products/product1.jpg",
      
      // Thông tin bổ sung về sự kiện
      "has_active_event": true,
      "event_discount_percentage": 50.0
    }
  ],
  "links": {...},
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 10,
    "total": 50
  }
}
```

## Search Products
- **URL:** `GET /api/v1/public/products-search`
- **Description:** Tìm kiếm sản phẩm với thông tin giá cả sự kiện
- **Authentication:** Không bắt buộc
- **Parameters:**
  - `q` (required): Từ khóa tìm kiếm
  - `per_page` (optional): Số lượng items per page
- **Response:** Tương tự như Get Products List

## Filter Products
- **URL:** `GET /api/v1/public/products-filter`
- **Description:** Lọc sản phẩm với thông tin giá cả sự kiện
- **Authentication:** Không bắt buộc
- **Parameters:**
  - `category_id` (optional): ID danh mục
  - `min_price` (optional): Giá tối thiểu
  - `max_price` (optional): Giá tối đa
  - `status` (optional): Trạng thái sản phẩm
  - `product_type` (optional): Loại sản phẩm
  - `min_views` (optional): Lượt xem tối thiểu
  - `min_rating` (optional): Đánh giá tối thiểu
  - `sort_by` (optional): Sắp xếp theo (price_asc, price_desc, name_asc, name_desc, most_viewed, rating_desc)
- **Response:** Tương tự như Get Products List
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Sản phẩm A",
    "slug": "san-pham-a",
    "description": "Mô tả sản phẩm",
    "status": 1,
    "product_type": "simple",
    "category": {
      "id": 1,
      "name": "Danh mục A",
      "slug": "danh-muc-a"
    },
    
    // Giá cả cơ bản
    "base_price": 1000000,
    "base_discount": 0,
    
    // Thông tin sự kiện
    "event_price": 500000,
    "original_price": 1000000,
    "event_info": {
      "id": 1,
      "name": "Siêu Sale Tháng 12",
      "description": "Giảm giá lên đến 50%",
      "start_time": "2024-12-01T00:00:00.000000Z",
      "end_time": "2024-12-31T23:59:59.000000Z",
      "discount_type": "percentage",
      "discount_value": 50,
      "banner_image": "events/banner1.jpg"
    },
    
    // Giá hiển thị (ưu tiên sự kiện)
    "display_price": 500000,
    "display_discount_price": 500000,
    
    "stock_quantity": 100,
    "image": "http://localhost:8000/storage/products/product1.jpg",
    
    "images": [...],
    "variants": [...],
    "reviews": [...],
    "related_products": [...],
    
    "average_rating": 4.5,
    "views_count": 150,
    
    // Thông tin bổ sung về sự kiện
    "has_active_event": true,
    "event_discount_percentage": 50.0
  }
}
```

### Các trường hợp giá cả:

#### 1. Sản phẩm không trong sự kiện:
```json
{
  "event_price": null,
  "original_price": null,
  "event_info": null,
  "display_price": 1000000,
  "has_active_event": false,
  "event_discount_percentage": null
}
```

#### 2. Sản phẩm đang trong sự kiện:
```json
{
  "event_price": 500000,
  "original_price": 1000000,
  "event_info": {
    "id": 1,
    "name": "Siêu Sale Tháng 12",
    "description": "Giảm giá lên đến 50%",
    "start_time": "2024-12-01T00:00:00.000000Z",
    "end_time": "2024-12-31T23:59:59.000000Z",
    "discount_type": "percentage",
    "discount_value": 50,
    "banner_image": "events/banner1.jpg"
  },
  "display_price": 500000,
  "has_active_event": true,
  "event_discount_percentage": 50.0
}
```

#### 3. Sản phẩm trong nhiều sự kiện:
- API sẽ tự động chọn sự kiện có discount cao nhất
- Nếu discount bằng nhau, ưu tiên sự kiện sắp kết thúc sớm nhất
- Chỉ trả về thông tin của sự kiện được chọn

### Logic ưu tiên sự kiện:
1. **Discount cao nhất:** Sự kiện có `discount_price` cao nhất
2. **Thời gian kết thúc:** Nếu discount bằng nhau, ưu tiên sự kiện có `end_time` sớm nhất
3. **Chỉ sự kiện active:** Chỉ xét các sự kiện có `status = 'active'` và đang trong thời gian diễn ra
