<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán thất bại</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .error-icon {
            width: 80px;
            height: 80px;
            background: #ff6b6b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .error-details {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .error-details h3 {
            margin-top: 0;
            color: #c53030;
        }
        .error-message {
            color: #e53e3e;
            font-weight: bold;
            margin: 10px 0;
        }
        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        .order-details h3 {
            margin-top: 0;
            color: #333;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .value {
            color: #333;
        }
        .btn {
            background: #ff6b6b;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #ee5a24;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-retry {
            background: #28a745;
        }
        .btn-retry:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">✗</div>
        <h1>Thanh toán thất bại!</h1>
        <p class="message">
            Rất tiếc, quá trình thanh toán của bạn đã gặp lỗi. Vui lòng thử lại hoặc liên hệ hỗ trợ.
        </p>

        @if(isset($error))
        <div class="error-details">
            <h3>Chi tiết lỗi</h3>
            <div class="error-message">{{ $error }}</div>
        </div>
        @endif

        @if(isset($order))
        <div class="order-details">
            <h3>Thông tin đơn hàng</h3>
            <div class="detail-row">
                <span class="label">Mã đơn hàng:</span>
                <span class="value">{{ $order->order_number }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Tổng tiền:</span>
                <span class="value">{{ number_format($order->total) }} VND</span>
            </div>
            <div class="detail-row">
                <span class="label">Trạng thái:</span>
                <span class="value" style="color: #ff6b6b; font-weight: bold;">Thanh toán thất bại</span>
            </div>
        </div>
        @endif

        <div>
            <a href="/checkout" class="btn btn-retry">Thử lại thanh toán</a>
            <a href="/" class="btn">Về trang chủ</a>
            <a href="/orders" class="btn btn-secondary">Xem đơn hàng</a>
        </div>
    </div>
</body>
</html>
