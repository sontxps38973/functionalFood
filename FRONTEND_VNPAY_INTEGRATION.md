# Frontend VNPay Integration Guide

## 🎯 Tóm tắt

Frontend cần tích hợp với 3 API endpoints của VNPay để xử lý thanh toán online.

## 📋 API Endpoints

### 1. **Create Payment** - Tạo payment URL
```
POST /api/v1/create-payment
```

### 2. **Return URL** - Xử lý khi user quay về từ VNPay
```
GET /payment/return
```

### 3. **IPN URL** - Callback tự động từ VNPay
```
GET /api/v1/vnpay-ipn
```

## 🔧 Frontend Implementation

### **1. Tích hợp vào Order Flow**

#### **Step 1: Thêm payment method selection**
```javascript
// Trong component checkout/order
const paymentMethods = [
  { id: 'cod', name: 'Thanh toán khi nhận hàng', icon: '💰' },
  { id: 'online_payment', name: 'Thanh toán online (VNPay)', icon: '💳' }
];

const [selectedPaymentMethod, setSelectedPaymentMethod] = useState('cod');
```

#### **Step 2: Modify placeOrder API call**
```javascript
// Trong function placeOrder
const placeOrder = async (orderData) => {
  try {
    const response = await fetch('/api/v1/user/orders/place-order', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        ...orderData,
        payment_method: selectedPaymentMethod // 'cod' hoặc 'online_payment'
      })
    });

    const result = await response.json();
    
    if (result.success) {
      if (selectedPaymentMethod === 'online_payment' && result.payment) {
        // Redirect to VNPay
        window.location.href = result.payment.payment_url;
      } else {
        // COD - Show success message
        showSuccessMessage('Đặt hàng thành công!');
        navigate('/orders');
      }
    }
  } catch (error) {
    console.error('Order error:', error);
  }
};
```

### **2. VNPay Payment Flow**

#### **Step 1: Create Payment Component**
```javascript
// components/VNPayPayment.js
import React, { useState } from 'react';

const VNPayPayment = ({ orderId, amount, onSuccess, onError }) => {
  const [loading, setLoading] = useState(false);

  const createPayment = async () => {
    setLoading(true);
    try {
      const response = await fetch('/api/v1/create-payment', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
          order_id: orderId,
          amount: amount,
          return_url: window.location.origin + '/payment/return',
          ipn_url: window.location.origin + '/api/v1/vnpay-ipn'
        })
      });

      const result = await response.json();
      
      if (result.success) {
        // Redirect to VNPay
        window.location.href = result.data.payment_url;
      } else {
        onError(result.message);
      }
    } catch (error) {
      onError('Lỗi tạo payment');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="vnpay-payment">
      <h3>Thanh toán qua VNPay</h3>
      <p>Số tiền: {amount.toLocaleString('vi-VN')} VND</p>
      <button 
        onClick={createPayment} 
        disabled={loading}
        className="btn btn-primary"
      >
        {loading ? 'Đang xử lý...' : 'Thanh toán ngay'}
      </button>
    </div>
  );
};

export default VNPayPayment;
```

#### **Step 2: Payment Return Handler**
```javascript
// pages/PaymentReturn.js
import React, { useEffect, useState } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';

const PaymentReturn = () => {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const [status, setStatus] = useState('loading');

  useEffect(() => {
    const handlePaymentReturn = async () => {
      try {
        // Get all VNPay parameters from URL
        const params = Object.fromEntries(searchParams.entries());
        
        // Call backend to verify payment
        const response = await fetch('/payment/return?' + searchParams.toString());
        const result = await response.json();
        
        if (result.success) {
          setStatus('success');
          // Show success message
          setTimeout(() => {
            navigate('/orders');
          }, 3000);
        } else {
          setStatus('failed');
        }
      } catch (error) {
        setStatus('error');
      }
    };

    handlePaymentReturn();
  }, [searchParams, navigate]);

  return (
    <div className="payment-return">
      {status === 'loading' && (
        <div className="loading">
          <h3>Đang xử lý thanh toán...</h3>
        </div>
      )}
      
      {status === 'success' && (
        <div className="success">
          <h3>✅ Thanh toán thành công!</h3>
          <p>Cảm ơn bạn đã mua hàng. Đơn hàng sẽ được xử lý sớm nhất.</p>
          <button onClick={() => navigate('/orders')}>
            Xem đơn hàng
          </button>
        </div>
      )}
      
      {status === 'failed' && (
        <div className="failed">
          <h3>❌ Thanh toán thất bại</h3>
          <p>Vui lòng thử lại hoặc chọn phương thức thanh toán khác.</p>
          <button onClick={() => navigate('/checkout')}>
            Quay lại thanh toán
          </button>
        </div>
      )}
      
      {status === 'error' && (
        <div className="error">
          <h3>⚠️ Lỗi xử lý</h3>
          <p>Đã xảy ra lỗi trong quá trình xử lý thanh toán.</p>
          <button onClick={() => navigate('/orders')}>
            Xem đơn hàng
          </button>
        </div>
      )}
    </div>
  );
};

export default PaymentReturn;
```

### **3. Order Status Updates**

#### **Step 1: Add payment status to order display**
```javascript
// components/OrderCard.js
const OrderCard = ({ order }) => {
  const getPaymentStatus = (status, paymentMethod) => {
    if (paymentMethod === 'cod') {
      return { text: 'Thanh toán khi nhận hàng', color: 'info' };
    }
    
    switch (status) {
      case 'pending':
        return { text: 'Chờ thanh toán', color: 'warning' };
      case 'processing':
        return { text: 'Đang xử lý thanh toán', color: 'primary' };
      case 'paid':
        return { text: 'Đã thanh toán', color: 'success' };
      case 'payment_failed':
        return { text: 'Thanh toán thất bại', color: 'danger' };
      default:
        return { text: 'Không xác định', color: 'secondary' };
    }
  };

  const paymentStatus = getPaymentStatus(order.status, order.payment_method);

  return (
    <div className="order-card">
      <div className="order-header">
        <h4>Đơn hàng #{order.id}</h4>
        <span className={`badge badge-${paymentStatus.color}`}>
          {paymentStatus.text}
        </span>
      </div>
      
      {order.payment_method === 'online_payment' && order.status === 'payment_failed' && (
        <div className="payment-retry">
          <button onClick={() => retryPayment(order.id)}>
            Thử thanh toán lại
          </button>
        </div>
      )}
    </div>
  );
};
```

#### **Step 2: Real-time order status updates**
```javascript
// hooks/useOrderStatus.js
import { useState, useEffect } from 'react';

export const useOrderStatus = (orderId) => {
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchOrder = async () => {
    try {
      const response = await fetch(`/api/v1/user/orders/${orderId}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
      });
      const result = await response.json();
      setOrder(result.data);
    } catch (error) {
      console.error('Error fetching order:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchOrder();
    
    // Poll for status updates every 30 seconds
    const interval = setInterval(fetchOrder, 30000);
    
    return () => clearInterval(interval);
  }, [orderId]);

  return { order, loading, refetch: fetchOrder };
};
```

### **4. Error Handling**

#### **Step 1: Payment Error Component**
```javascript
// components/PaymentError.js
const PaymentError = ({ error, onRetry, onCancel }) => {
  const getErrorMessage = (errorCode) => {
    const errorMessages = {
      '99': 'Giao dịch thất bại trong quá trình xử lý',
      '01': 'Giao dịch chưa hoàn tất',
      '02': 'Giao dịch bị lỗi',
      '04': 'Giao dịch đảo (Khách hàng đã bị trừ tiền nhưng GD chưa thành công)',
      '05': 'VNPAY đang xử lý',
      '06': 'VNPAY đã gửi yêu cầu hoàn tiền sang Ngân hàng',
      '07': 'Giao dịch bị nghi ngờ gian lận',
      '09': 'Giao dịch không thành công do: Thẻ/Tài khoản bị khóa'
    };
    
    return errorMessages[errorCode] || 'Lỗi không xác định';
  };

  return (
    <div className="payment-error">
      <div className="error-icon">❌</div>
      <h3>Thanh toán thất bại</h3>
      <p>{getErrorMessage(error)}</p>
      
      <div className="error-actions">
        <button onClick={onRetry} className="btn btn-primary">
          Thử lại
        </button>
        <button onClick={onCancel} className="btn btn-secondary">
          Hủy bỏ
        </button>
      </div>
    </div>
  );
};
```

### **5. UI/UX Improvements**

#### **Step 1: Loading States**
```javascript
// components/PaymentLoading.js
const PaymentLoading = () => {
  return (
    <div className="payment-loading">
      <div className="spinner"></div>
      <h3>Đang chuyển hướng đến VNPay...</h3>
      <p>Vui lòng không đóng trình duyệt</p>
    </div>
  );
};
```

#### **Step 2: Payment Method Selection**
```javascript
// components/PaymentMethodSelector.js
const PaymentMethodSelector = ({ selected, onSelect }) => {
  return (
    <div className="payment-methods">
      <h3>Chọn phương thức thanh toán</h3>
      
      <div className="payment-option" onClick={() => onSelect('cod')}>
        <input 
          type="radio" 
          name="payment" 
          value="cod" 
          checked={selected === 'cod'}
          onChange={() => onSelect('cod')}
        />
        <div className="option-content">
          <span className="icon">💰</span>
          <div>
            <h4>Thanh toán khi nhận hàng (COD)</h4>
            <p>Thanh toán bằng tiền mặt khi nhận hàng</p>
          </div>
        </div>
      </div>
      
      <div className="payment-option" onClick={() => onSelect('online_payment')}>
        <input 
          type="radio" 
          name="payment" 
          value="online_payment" 
          checked={selected === 'online_payment'}
          onChange={() => onSelect('online_payment')}
        />
        <div className="option-content">
          <span className="icon">💳</span>
          <div>
            <h4>Thanh toán online (VNPay)</h4>
            <p>Thanh toán qua thẻ ATM, thẻ quốc tế, ví điện tử</p>
          </div>
        </div>
      </div>
    </div>
  );
};
```

## 🎨 CSS Styling

### **Payment Components Styling**
```css
/* Payment Method Selector */
.payment-methods {
  margin: 20px 0;
}

.payment-option {
  display: flex;
  align-items: center;
  padding: 15px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  margin: 10px 0;
  cursor: pointer;
  transition: all 0.3s ease;
}

.payment-option:hover {
  border-color: #007bff;
}

.payment-option input[type="radio"] {
  margin-right: 15px;
}

.option-content {
  display: flex;
  align-items: center;
  flex: 1;
}

.option-content .icon {
  font-size: 24px;
  margin-right: 15px;
}

/* Payment Loading */
.payment-loading {
  text-align: center;
  padding: 40px;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #007bff;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Payment Return */
.payment-return {
  text-align: center;
  padding: 40px;
}

.payment-return .success {
  color: #28a745;
}

.payment-return .failed {
  color: #dc3545;
}

.payment-return .error {
  color: #ffc107;
}

/* Payment Error */
.payment-error {
  text-align: center;
  padding: 40px;
  background: #f8f9fa;
  border-radius: 8px;
}

.error-icon {
  font-size: 48px;
  margin-bottom: 20px;
}

.error-actions {
  margin-top: 20px;
}

.error-actions button {
  margin: 0 10px;
}
```

## 📱 Mobile Responsive

### **Mobile Payment Flow**
```javascript
// Responsive payment handling
const isMobile = window.innerWidth <= 768;

const handlePayment = async () => {
  if (isMobile) {
    // On mobile, open VNPay in new window/tab
    const paymentWindow = window.open('', '_blank');
    // Show loading in current window
    setShowLoading(true);
  } else {
    // On desktop, redirect in same window
    window.location.href = paymentUrl;
  }
};
```

## 🔒 Security Considerations

### **1. Token Validation**
```javascript
// Always validate token before payment
const validateToken = () => {
  const token = localStorage.getItem('token');
  if (!token) {
    navigate('/login');
    return false;
  }
  return true;
};
```

### **2. Amount Validation**
```javascript
// Validate amount on frontend
const validateAmount = (amount) => {
  if (amount < 1000) {
    throw new Error('Số tiền tối thiểu là 1,000 VND');
  }
  if (amount > 100000000) {
    throw new Error('Số tiền tối đa là 100,000,000 VND');
  }
  return true;
};
```

## 📋 Checklist cho Frontend

- [ ] **Payment Method Selection**
  - [ ] Thêm radio buttons cho COD và Online Payment
  - [ ] Styling cho payment options
  - [ ] Validation khi chọn payment method

- [ ] **Order Placement Integration**
  - [ ] Modify placeOrder API call
  - [ ] Handle payment URL redirect
  - [ ] Error handling cho payment creation

- [ ] **Payment Return Page**
  - [ ] Create PaymentReturn component
  - [ ] Handle VNPay return parameters
  - [ ] Show success/failure messages
  - [ ] Navigation sau payment

- [ ] **Order Status Updates**
  - [ ] Add payment status display
  - [ ] Real-time status polling
  - [ ] Payment retry functionality

- [ ] **Error Handling**
  - [ ] Payment error messages
  - [ ] Retry mechanisms
  - [ ] User-friendly error display

- [ ] **UI/UX**
  - [ ] Loading states
  - [ ] Mobile responsive
  - [ ] Payment flow indicators

- [ ] **Security**
  - [ ] Token validation
  - [ ] Amount validation
  - [ ] Secure payment handling

## 🚀 **Status:** ✅ **READY FOR IMPLEMENTATION**

**Frontend có thể bắt đầu tích hợp ngay với các API endpoints đã sẵn sàng!**
