<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;

class PaymentController extends Controller
{
    /**
     * Create a new payment transaction
     */
    public function createPayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|exists:orders,id',
                'amount' => 'required|numeric|min:1000', // Minimum 1000 VND
                'return_url' => 'nullable|url',
                'ipn_url' => 'nullable|url',
            ]);

            $order = Order::findOrFail($request->order_id);

            // Check if order is pending
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is not in pending status'
                ], 400);
            }

            // Set default URLs if not provided
            $returnUrl = $request->return_url ?: config('app.url') . '/payment/return';
            $ipnUrl = $request->ipn_url ?: config('app.url') . '/api/v1/vnpay-ipn';

            // VNPay configuration
            $vnp_TmnCode = config('services.vnpay.tmn_code');
            $vnp_HashSecret = config('services.vnpay.hash_secret');
            $vnp_Url = config('services.vnpay.payment_url');
            $vnp_Returnurl = $returnUrl;
            $vnp_IpnUrl = $ipnUrl;

            $vnp_TxnRef = $order->id . '_' . time(); // Unique transaction reference
            $vnp_OrderInfo = 'Thanh toan don hang #' . $order->id;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $request->amount * 100; // VNPay expects amount in VND (no decimal)
            $vnp_Locale = 'vn';
            $vnp_CurrCode = 'VND';
            $vnp_TxnType = 'pay';

            // Fix IP Address for VNPay Error 99 - Enhanced IP handling
            $vnp_IpAddr = $request->ip();
            
            // Log original IP for debugging
            Log::info('VNPay Payment - Original IP from request: ' . $vnp_IpAddr);
            
            // Handle various localhost/empty IP scenarios
            if (empty($vnp_IpAddr) || 
                $vnp_IpAddr === '127.0.0.1' || 
                $vnp_IpAddr === '::1' || 
                $vnp_IpAddr === 'localhost' ||
                $vnp_IpAddr === '0.0.0.0') {
                
                // Try to get real IP from various sources
                $realIp = $request->header('X-Forwarded-For') ?: 
                          $request->header('X-Real-IP') ?: 
                          $request->header('CF-Connecting-IP') ?: 
                          '203.205.254.157'; // Fallback to public IP
                
                $vnp_IpAddr = $realIp;
                Log::info('VNPay Payment - Using fallback IP: ' . $vnp_IpAddr);
            }
            
            // Ensure IP is not empty
            if (empty($vnp_IpAddr)) {
                $vnp_IpAddr = '203.205.254.157'; // Final fallback
                Log::warning('VNPay Payment - Using final fallback IP: ' . $vnp_IpAddr);
            }

            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => $vnp_CurrCode,
                "vnp_IpAddr" => $vnp_IpAddr, // Fixed IP address
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_IpnUrl" => $vnp_IpnUrl,
                "vnp_TxnRef" => $vnp_TxnRef,
                "vnp_TxnType" => $vnp_TxnType,
            );
            
            // Log final IP address being sent to VNPay
            Log::info('VNPay Payment - Final IP address: ' . $vnp_IpAddr);
            Log::info('VNPay Payment - Input data for hash: ' . json_encode($inputData));

            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            $vnp_Url = $vnp_Url . "?" . $query;
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

            // Update order with payment info
            $order->update([
                'payment_method' => 'vnpay',
                'payment_reference' => $vnp_TxnRef,
                'status' => 'processing'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment URL created successfully',
                'data' => [
                    'payment_url' => $vnp_Url,
                    'transaction_ref' => $vnp_TxnRef,
                    'order_id' => $order->id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Payment creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment'
            ], 500);
        }
    }

    /**
     * Handle VNPay return (user redirects back from VNPay)
     */
    public function vnpayReturn(Request $request)
    {
        try {
            $vnp_HashSecret = config('services.vnpay.hash_secret');

            $inputData = array();
            $returnData = array();
            $data = $request->all();

            foreach ($data as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }

            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            unset($inputData['vnp_SecureHashType']);
            ksort($inputData);
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            // Extract order ID from transaction reference
            $vnp_TxnRef = $inputData['vnp_TxnRef'];
            $orderId = explode('_', $vnp_TxnRef)[0];

            $order = Order::find($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            if ($secureHash == $vnp_SecureHash) {
                if ($inputData['vnp_ResponseCode'] == '00') {
                    // Payment successful
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_transaction_id' => $inputData['vnp_TransactionNo'] ?? null
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Payment successful',
                        'data' => [
                            'order_id' => $order->id,
                            'transaction_id' => $inputData['vnp_TransactionNo'] ?? null,
                            'amount' => $inputData['vnp_Amount'] / 100, // Convert back from VND
                            'bank_code' => $inputData['vnp_BankCode'] ?? null,
                            'payment_time' => $inputData['vnp_PayDate'] ?? null
                        ]
                    ]);
                } else {
                    // Payment failed
                    $order->update([
                        'status' => 'payment_failed',
                        'payment_error' => $inputData['vnp_ResponseCode'] ?? 'Unknown error'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Payment failed',
                        'data' => [
                            'order_id' => $order->id,
                            'error_code' => $inputData['vnp_ResponseCode'] ?? null,
                            'error_message' => $this->getVnpayErrorMessage($inputData['vnp_ResponseCode'] ?? '')
                        ]
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('VNPay return error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment return'
            ], 500);
        }
    }

    /**
     * Handle VNPay IPN (Instant Payment Notification)
     */
    public function vnpayIpn(Request $request)
    {
        try {
            $vnp_HashSecret = config('services.vnpay.hash_secret');

            $inputData = array();
            $data = $request->all();

            foreach ($data as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }

            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            unset($inputData['vnp_SecureHashType']);
            ksort($inputData);
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

            // Extract order ID from transaction reference
            $vnp_TxnRef = $inputData['vnp_TxnRef'];
            $orderId = explode('_', $vnp_TxnRef)[0];

            $order = Order::find($orderId);

            if (!$order) {
                Log::error('IPN: Order not found - ' . $orderId);
                return response('Order not found', 404);
            }

            if ($secureHash == $vnp_SecureHash) {
                if ($inputData['vnp_ResponseCode'] == '00') {
                    // Payment successful
                    $order->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'payment_transaction_id' => $inputData['vnp_TransactionNo'] ?? null
                    ]);

                    Log::info('IPN: Payment successful for order ' . $orderId);
                    return response('OK', 200);
                } else {
                    // Payment failed
                    $order->update([
                        'status' => 'payment_failed',
                        'payment_error' => $inputData['vnp_ResponseCode'] ?? 'Unknown error'
                    ]);

                    Log::info('IPN: Payment failed for order ' . $orderId . ' - Code: ' . $inputData['vnp_ResponseCode']);
                    return response('OK', 200);
                }
            } else {
                Log::error('IPN: Invalid signature for order ' . $orderId);
                return response('Invalid signature', 400);
            }
        } catch (\Exception $e) {
            Log::error('IPN error: ' . $e->getMessage());
            return response('Error processing IPN', 500);
        }
    }

    /**
     * Get VNPay error message by response code
     */
    private function getVnpayErrorMessage($responseCode)
    {
        $errorMessages = [
            '00' => 'Giao dịch thành công',
            '01' => 'Giao dịch chưa hoàn tất',
            '02' => 'Giao dịch bị lỗi',
            '04' => 'Giao dịch đảo (Khách hàng đã bị trừ tiền tại Ngân hàng nhưng GD chưa thành công ở VNPAY)',
            '05' => 'VNPAY đang xử lý',
            '06' => 'VNPAY đã gửi yêu cầu hoàn tiền sang Ngân hàng',
            '07' => 'Giao dịch bị nghi ngờ gian lận',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản bị khóa',
        ];

        return $errorMessages[$responseCode] ?? 'Mã lỗi không xác định';
    }
}
