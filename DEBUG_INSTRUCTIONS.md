# Hướng Dẫn Debug Vấn Đề Category Update

## Các Bước Thực Hiện

### 1. **Chạy Script Test Database**
```bash
php test_category_debug.php
```

Script này sẽ:
- Kiểm tra kết nối database
- Tìm category ID 1
- Test update category
- Test CategoryResource
- Kiểm tra database trực tiếp
- Restore tên gốc

### 2. **Chạy Script Test Đơn Giản** (Khuyến nghị)
```bash
php test_simple_debug.php
```

Script này sẽ:
- Test update category trực tiếp
- Test CategoryResource
- Kiểm tra null values
- Tạo response JSON mẫu

### 3. **Chạy Script Test API Endpoint** (Nâng cao)
```bash
php test_api_endpoint.php
```

Script này sẽ:
- Tạo mock request
- Test controller method trực tiếp
- Kiểm tra response data
- Phát hiện null values

### 4. **Kiểm Tra Logs**
```bash
# Xem logs real-time
tail -f storage/logs/laravel.log | grep -E "(Category|category)"

# Xem logs của ngày hôm nay
grep "$(date +%Y-%m-%d)" storage/logs/laravel.log | grep -E "(Category|category)"
```

### 5. **Test API Thực Tế**
```bash
# Test với curl
curl -v -X PUT "http://127.0.0.1:8000/api/v1/admin/admin-categories/1" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name": "Test Category Debug"}'
```

### 6. **Kiểm Tra Database Trực Tiếp**
```bash
php artisan tinker

# Trong tinker:
>>> $category = App\Models\Category::find(1);
>>> dd($category->toArray());

# Test update
>>> $category->update(['name' => 'Test Name']);
>>> $category->refresh();
>>> dd($category->toArray());

# Test resource
>>> $resource = new App\Http\Resources\CategoryResource($category);
>>> dd($resource->toArray(request()));
```

## Các File Đã Cập Nhật

### 1. **CategoryController.php**
- ✅ Thêm debug logging chi tiết
- ✅ Thêm error handling với try-catch
- ✅ Thêm kiểm tra slug trùng lặp
- ✅ Thêm kiểm tra category tồn tại
- ✅ Thêm kiểm tra wasChanged()
- ✅ Thêm response message

### 2. **CategoryResource.php**
- ✅ Thêm debug logging
- ✅ Thêm null check cho resource
- ✅ Thêm error message khi resource null
- ✅ Thêm created_at và updated_at

### 3. **UpdateCategoryRequest.php**
- ✅ Thêm validation min:2 cho name
- ✅ Thêm custom error messages tiếng Việt

## Kết Quả Mong Đợi

### Nếu Script Chạy Thành Công:
```
=== DEBUG CATEGORY UPDATE ===

1. Kiểm tra kết nối database...
✅ Database connected successfully

2. Kiểm tra category ID 1...
✅ Category found:
   ID: 1
   Name: Thực phẩm chức năng
   Slug: thuc-pham-chuc-nang
   Created: 2024-01-01 00:00:00
   Updated: 2024-01-01 00:00:00

3. Test update category...
   Old name: Thực phẩm chức năng
   New name: Test Category 2024-01-01 12:00:00
   Update result: true
   After refresh:
   ID: 1
   Name: Test Category 2024-01-01 12:00:00
   Slug: test-category-2024-01-01-120000
   Updated: 2024-01-01 12:00:00

4. Test CategoryResource...
   Resource data:
   id: 1
   name: Test Category 2024-01-01 12:00:00
   slug: test-category-2024-01-01-120000
   created_at: 2024-01-01T00:00:00.000000Z
   updated_at: 2024-01-01T12:00:00.000000Z

5. Kiểm tra database trực tiếp...
✅ Database record found:
   ID: 1
   Name: Test Category 2024-01-01 12:00:00
   Slug: test-category-2024-01-01-120000
   Created: 2024-01-01 00:00:00
   Updated: 2024-01-01 12:00:00

6. Restore original name...
✅ Original name restored

=== DEBUG COMPLETED ===
```

### Nếu Có Vấn Đề:
- Script sẽ hiển thị lỗi cụ thể
- Logs sẽ có thông tin chi tiết
- Có thể xác định chính xác điểm gây lỗi

## Các Bước Tiếp Theo

1. **Chạy script test** và xem kết quả
2. **Kiểm tra logs** để tìm thông tin debug
3. **Xác định nguyên nhân** gây ra null values
4. **Khắc phục vấn đề** dựa trên kết quả debug
5. **Test lại** để đảm bảo đã fix

## Liên Hệ Hỗ Trợ

Nếu vẫn gặp vấn đề, hãy cung cấp:
- Output của script test
- Logs từ Laravel
- Response từ API endpoint
- Thông tin về môi trường
