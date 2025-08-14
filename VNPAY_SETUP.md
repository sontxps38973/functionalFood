# VNPay Payment Integration Setup

## Cấu hình Environment Variables

Thêm các biến môi trường sau vào file `.env`:

```env
# VNPay Configuration
VNPAY_TMN_CODE=your_tmn_code_here
VNPAY_HASH_SECRET=your_hash_secret_here
VNPAY_PAYMENT_URL=https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
VNPAY_QUERY_URL=https://sandbox.vnpayment.vn/merchant_webapi/api/transaction
VNPAY_REFUND_URL=https://sandbox.vnpayment.vn/merchant_webapi/api/transaction
```

## Các API Endpoints

### 1. Tạo thanh toán
**POST** `/api/v1/create-payment`

**Request Body:**
```json
{
    "order_id": 123,
    "amount": 100000,
    "return_url": "https://your-domain.com/payment/return",
    "ipn_url": "https://your-domain.com/api/v1/vnpay-ipn"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Payment URL created successfully",
    "data": {
        "payment_url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?...",
        "transaction_ref": "123_1234567890",
        "order_id": 123
    }
}
```

### 2. Xử lý return từ VNPay
**GET** `/api/v1/vnpay-return`

Endpoint này được VNPay gọi khi người dùng quay lại từ trang thanh toán.

### 3. Xử lý IPN từ VNPay
**GET** `/api/v1/vnpay-ipn`

Endpoint này được VNPay gọi để thông báo kết quả thanh toán.

## Cách sử dụng

1. **Tạo đơn hàng** với status = 'pending'
2. **Gọi API create-payment** với order_id và amount
3. **Redirect user** đến payment_url từ response
4. **VNPay sẽ gọi return_url** khi user hoàn tất thanh toán
5. **VNPay sẽ gọi ipn_url** để thông báo kết quả

## Trạng thái đơn hàng

- `pending`: Chờ thanh toán
- `processing`: Đang xử lý thanh toán
- `paid`: Đã thanh toán thành công
- `payment_failed`: Thanh toán thất bại

## Lưu ý

- Đảm bảo return_url và ipn_url có thể truy cập được từ internet
- IPN URL phải trả về HTTP 200 để VNPay biết đã nhận được thông báo
- Luôn verify signature từ VNPay để đảm bảo an toàn
- Test với sandbox trước khi deploy production
