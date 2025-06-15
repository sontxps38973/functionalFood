# 🍀 Functional Food API

Đây là dự án API Laravel quản lý sản phẩm và danh mục cho trang web bán **thực phẩm chức năng**.

---

## 🚀 Tính năng

- Quản lý **danh mục** (CRUD)
- Quản lý **sản phẩm** (CRUD)
- **Tìm kiếm** sản phẩm theo tên
- **Lọc** sản phẩm theo danh mục, giá, tên
- **Phân trang**
- **Tài liệu API** bằng Swagger

---

## ⚙️ Yêu cầu hệ thống

- PHP >= 8.1
- MySQL hoặc MariaDB
- Composer
- Laravel 12
- Laragon / XAMPP / Homestead / Valet

---

## 🧪 Thiết lập dự án

Sau khi clone dự án về:

```bash
git clone https://github.com/sontxps38973/functionalFood.git
cd functionalFood
```

### ✅ Chạy script tự động

> **Chỉ 1 dòng duy nhất để thiết lập toàn bộ hệ thống**

```bash
./setup.sh
```

**Script này sẽ tự động:**

- Tạo file `.env`
- Cài đặt các gói Composer
- Tạo `APP_KEY`
- Tạo database nếu chưa có
- Chạy migration và seeder
- Tạo tài liệu Swagger
- Xóa cache cũ

---
## ▶️ Cách khởi chạy dự án

Sau khi chạy `setup.sh` xong (Lưu ý chỉ chạy setup 1 lần, từ lần thứ 2 trở đi chỉ cần làm theo hướng dẫn bên dưới), để khởi động server:

```bash
php artisan serve
```

Truy cập:
```
http://localhost:8000
```

Xem Swagger Docs tại:
```
http://localhost:8000/api/documentation
```



## ✍️ Tác giả

- **Tên**: Trịnh Xuân Sơn
- **Email**: strinh741@gmail.com
- **GitHub**: [@your-username](https://github.com/your-username)

---


