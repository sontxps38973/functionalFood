# YÊU CẦU KHÁCH HÀNG - HỆ THỐNG E-COMMERCE THỰC PHẨM CHỨC NĂNG

## 📋 TỔNG QUAN DỰ ÁN

### Mục tiêu
Xây dựng hệ thống e-commerce chuyên biệt cho thực phẩm chức năng, hỗ trợ quản lý sản phẩm đa dạng, hệ thống khuyến mãi linh hoạt và trải nghiệm mua sắm tối ưu.

### Đối tượng sử dụng
- **Khách hàng**: Người tiêu dùng quan tâm đến sức khỏe và thực phẩm chức năng
- **Admin**: Quản lý hệ thống, sản phẩm, đơn hàng
- **Super Admin**: Quản lý tài khoản admin và cấu hình hệ thống

---

## 🛍️ YÊU CẦU CHỨC NĂNG KHÁCH HÀNG

### 1. Đăng ký & Đăng nhập
- **Đăng ký tài khoản** với thông tin cơ bản
- **Đăng nhập** bằng email/password
- **Xác thực email** qua OTP
- **Quên mật khẩu** với gửi OTP qua email
- **Đăng nhập bằng token** (Sanctum)

### 2. Quản lý Sản phẩm
- **Xem danh sách sản phẩm** với phân trang
- **Tìm kiếm sản phẩm** theo tên, mô tả
- **Lọc sản phẩm** theo:
  - Danh mục
  - Giá (từ - đến)
  - Đánh giá
  - Sản phẩm mới
  - Sản phẩm bán chạy
- **Sắp xếp** theo giá, tên, đánh giá, lượt xem
- **Xem chi tiết sản phẩm** với:
  - Thông tin đầy đủ
  - Hình ảnh
  - Biến thể (size, màu, vị)
  - Đánh giá và bình luận
  - Sản phẩm liên quan

### 3. Hỗ trợ Sản phẩm Đa dạng
- **Sản phẩm có variant**: Áo thun (size S/M/L, màu đỏ/xanh)
- **Sản phẩm không có variant**: Sách, thực phẩm đóng gói
- **Hiển thị tồn kho** cho từng loại sản phẩm
- **Kiểm tra tồn kho** khi đặt hàng

### 4. Giỏ hàng
- **Thêm sản phẩm** vào giỏ hàng
- **Cập nhật số lượng** trong giỏ hàng
- **Xóa sản phẩm** khỏi giỏ hàng
- **Xem tổng tiền** giỏ hàng
- **Lưu giỏ hàng** cho user đã đăng nhập

### 5. Hệ thống Khuyến mãi
- **Mã giảm giá đa dạng**:
  - Giảm giá theo phần trăm
  - Giảm giá theo số tiền cố định
  - Miễn phí vận chuyển
  - Giảm giá vận chuyển
- **Điều kiện áp dụng**:
  - Giá trị đơn hàng tối thiểu/tối đa
  - Hạng thành viên
  - Phương thức thanh toán
  - Thời gian áp dụng
  - Số lần sử dụng
- **Áp dụng mã** trước khi đặt hàng
- **Xem danh sách mã** có thể sử dụng

### 6. Đặt hàng
- **Đặt hàng linh hoạt**:
  - Sản phẩm có variant (cần chọn variant)
  - Sản phẩm không có variant (chỉ cần product_id)
- **Thông tin giao hàng**:
  - Tên người nhận
  - Số điện thoại
  - Địa chỉ giao hàng
  - Email
- **Phương thức thanh toán**:
  - Thanh toán khi nhận hàng (COD)
  - Chuyển khoản ngân hàng
  - Thanh toán trực tuyến
- **Tính toán tự động**:
  - Tổng tiền hàng
  - Phí vận chuyển
  - Thuế
  - Giảm giá
  - Tổng tiền cuối cùng

### 7. Quản lý Đơn hàng
- **Xem danh sách đơn hàng** với phân trang
- **Xem chi tiết đơn hàng**:
  - Thông tin đơn hàng
  - Danh sách sản phẩm
  - Trạng thái đơn hàng
  - Mã giảm giá đã áp dụng
- **Hủy đơn hàng** (nếu chưa xử lý)
- **Theo dõi trạng thái** đơn hàng
- **Xem thống kê** đơn hàng cá nhân

### 8. Hệ thống Hạng thành viên
- **Tự động nâng hạng** dựa trên tổng chi tiêu
- **Ưu đãi theo hạng**:
  - Giảm giá đặc biệt
  - Mã giảm giá riêng
  - Phí vận chuyển ưu đãi
- **Xem thông tin hạng** hiện tại

### 9. Flash Sale/Event
- **Xem danh sách sự kiện** đang diễn ra
- **Sản phẩm trong event** với giá đặc biệt
- **Giới hạn số lượng** mua trong event
- **Thời gian event** có hiệu lực

---

## 👨‍💼 YÊU CẦU CHỨC NĂNG ADMIN

### 1. Quản lý Sản phẩm
- **CRUD sản phẩm**:
  - Tạo, sửa, xóa sản phẩm
  - Upload hình ảnh
  - Quản lý thông tin chi tiết
- **Quản lý variant**:
  - Thêm/sửa/xóa biến thể
  - Quản lý tồn kho từng variant
  - Upload hình ảnh variant
- **Quản lý danh mục**:
  - Tạo danh mục sản phẩm
  - Phân cấp danh mục
- **Quản lý tồn kho**:
  - Cập nhật số lượng
  - Cảnh báo hết hàng
  - Lịch sử nhập/xuất

### 2. Quản lý Đơn hàng
- **Xem tất cả đơn hàng** với filter:
  - Trạng thái đơn hàng
  - Trạng thái thanh toán
  - Tìm kiếm theo tên, email, số điện thoại
- **Cập nhật trạng thái** đơn hàng:
  - Pending → Confirmed → Processing → Shipped → Delivered
  - Cancelled
- **Thêm thông tin**:
  - Mã vận chuyển
  - Ghi chú
- **Xem thống kê** đơn hàng

### 3. Quản lý Khuyến mãi
- **Tạo mã giảm giá** với nhiều loại:
  - Giảm giá sản phẩm (% hoặc tiền)
  - Giảm giá vận chuyển
  - Miễn phí vận chuyển
- **Cài đặt điều kiện**:
  - Giá trị đơn hàng
  - Hạng thành viên
  - Phương thức thanh toán
  - Thời gian áp dụng
  - Số lần sử dụng
- **Quản lý event/flash sale**:
  - Tạo event
  - Thêm sản phẩm vào event
  - Cài đặt giá đặc biệt
  - Giới hạn số lượng

### 4. Quản lý Khách hàng
- **Xem danh sách khách hàng** với filter:
  - Trạng thái tài khoản
  - Hạng thành viên
  - Tìm kiếm theo tên, email, số điện thoại
- **Xem chi tiết khách hàng**:
  - Thông tin cá nhân
  - Lịch sử đơn hàng
  - Thống kê mua hàng
- **Cập nhật thông tin** khách hàng
- **Quản lý hạng thành viên**
- **Export dữ liệu** khách hàng

### 5. Quản lý Admin
- **Tạo tài khoản admin** mới
- **Phân quyền admin**:
  - Super Admin: Quản lý tất cả
  - Admin: Quản lý sản phẩm, đơn hàng, khách hàng
- **Quản lý profile** admin
- **Đổi mật khẩu** admin

### 6. Báo cáo & Thống kê
- **Thống kê đơn hàng**:
  - Tổng đơn hàng
  - Doanh thu
  - Đơn hàng theo trạng thái
- **Thống kê khách hàng**:
  - Số lượng khách hàng mới
  - Hạng thành viên
- **Thống kê sản phẩm**:
  - Sản phẩm bán chạy
  - Tồn kho
- **Thống kê khuyến mãi**:
  - Mã giảm giá được sử dụng
  - Hiệu quả khuyến mãi

---

## 🔧 YÊU CẦU KỸ THUẬT

### 1. Công nghệ
- **Backend**: Laravel 10+ với PHP 8+
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel Sanctum
- **API Documentation**: L5-Swagger (OpenAPI)
- **File Storage**: Local/Cloud storage

### 2. Bảo mật
- **JWT Token** cho API authentication
- **Validation** đầy đủ cho tất cả input
- **Rate limiting** cho API
- **CORS** configuration
- **SQL Injection** protection
- **XSS** protection

### 3. Performance
- **Database indexing** cho các trường tìm kiếm
- **Eager loading** để tránh N+1 query
- **Pagination** cho danh sách dài
- **Caching** cho dữ liệu tĩnh
- **Image optimization** cho sản phẩm

### 4. Scalability
- **Modular architecture** dễ mở rộng
- **API versioning** (v1, v2...)
- **Database migration** system
- **Seeder** cho dữ liệu mẫu
- **Environment configuration**

---

## 📱 YÊU CẦU GIAO DIỆN

### 1. Responsive Design
- **Mobile-first** approach
- **Tablet** optimization
- **Desktop** experience
- **Cross-browser** compatibility

### 2. User Experience
- **Intuitive navigation**
- **Fast loading** times
- **Clear call-to-action** buttons
- **Error handling** với thông báo rõ ràng
- **Loading states** cho các action

### 3. Accessibility
- **WCAG 2.1** compliance
- **Keyboard navigation**
- **Screen reader** support
- **Color contrast** đạt chuẩn

---

## 🚀 YÊU CẦU TRIỂN KHAI

### 1. Development
- **Git version control**
- **Code review** process
- **Testing** (Unit, Feature)
- **Documentation** đầy đủ

### 2. Deployment
- **Environment** separation (dev, staging, prod)
- **Database backup** strategy
- **SSL certificate** cho production
- **Monitoring** và logging

### 3. Maintenance
- **Regular updates** cho dependencies
- **Security patches**
- **Performance monitoring**
- **User feedback** collection

---

## 📊 YÊU CẦU BÁO CÁO

### 1. Business Metrics
- **Doanh thu** theo ngày/tháng/năm
- **Số lượng đơn hàng**
- **Giá trị đơn hàng trung bình**
- **Tỷ lệ chuyển đổi**

### 2. User Analytics
- **Số lượng user mới**
- **User retention**
- **Most viewed products**
- **Search keywords**

### 3. Operational Metrics
- **Order fulfillment time**
- **Customer satisfaction**
- **Return rate**
- **Support tickets**

---

## 🔄 YÊU CẦU TÍCH HỢP

### 1. Payment Gateway
- **VNPay**
- **Momo**
- **ZaloPay**
- **Bank transfer**

### 2. Shipping
- **Giao hàng nhanh**
- **Giao hàng tiêu chuẩn**
- **Pickup tại cửa hàng**
- **Tracking number**

### 3. Communication
- **Email notifications**
- **SMS notifications**
- **Push notifications** (future)
- **Chat support** (future)

---

## 📋 YÊU CẦU PHÁP LÝ

### 1. Data Protection
- **GDPR compliance**
- **Personal data** protection
- **Data retention** policy
- **User consent** management

### 2. E-commerce Regulations
- **Consumer protection** laws
- **Return policy** compliance
- **Warranty** information
- **Terms of service**

---

## 🎯 KPI & SUCCESS METRICS

### 1. Technical KPIs
- **API response time** < 500ms
- **Uptime** > 99.9%
- **Error rate** < 1%
- **Page load time** < 3s

### 2. Business KPIs
- **Conversion rate** > 2%
- **Average order value** tăng 15%
- **Customer retention** > 60%
- **Support response time** < 24h

### 3. User Experience KPIs
- **User satisfaction** > 4.5/5
- **Task completion rate** > 90%
- **Bounce rate** < 40%
- **Mobile usage** > 60%

---

*Tài liệu này sẽ được cập nhật theo yêu cầu thay đổi từ khách hàng và phát triển của dự án.* 