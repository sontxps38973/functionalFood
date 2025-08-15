<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VnpayService;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    private $vnpayService;

    public function __construct(VnpayService $vnpayService)
    {
        $this->vnpayService = $vnpayService;
    }

    /**
     * Tạo payment URL cho order
     */
    public function createPayment(Request $request)
    {
        try {
            $request->validate([
                'order_id' => 'required|integer|exists:orders,id',
                'amount' => 'required|numeric|min:1000',
                'order_info' => 'nullable|string',
                'bank_code' => 'nullable|string',
                'expire_date' => 'nullable|string',
                'billing' => 'nullable|array',
                'invoice' => 'nullable|array',
            ]);

            // Get order
            $order = Order::findOrFail($request->order_id);

            // Prepare payment data
            $paymentData = [
                'amount' => $request->amount,
                'order_info' => $request->order_info ?: "Thanh toan don hang #{$order->order_number}",
                'txn_ref' => $order->order_number,
                'locale' => 'vn',
                'order_type' => 'billpayment',
                'bank_code' => $request->bank_code,
                'expire_date' => $request->expire_date,
                'billing' => $request->billing,
                'invoice' => $request->invoice,
            ];

            // Create payment URL
            $result = $this->vnpayService->createPaymentUrl($paymentData);

            if ($result['code'] === '00') {
                // Update order with payment reference
                $order->update([
                    'payment_reference' => $result['data']['txn_ref'],
                    'payment_method' => 'vnpay'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment URL created successfully',
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Payment creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xử lý return URL từ VNPay
     */
    public function vnpayReturn(Request $request)
    {
        try {
            Log::info('VNPay Return URL called', $request->all());

            // Verify payment response
            $verification = $this->vnpayService->verifyPaymentResponse($request->all());

            if (!$verification['valid']) {
                Log::error('VNPay Return - Invalid response', $verification);
                return view('payment.error', [
                    'message' => 'Invalid payment response',
                    'error' => $verification['message']
                ]);
            }

            $responseData = $verification['data'];
            $orderNumber = $responseData['vnp_TxnRef'] ?? null;
            $responseCode = $responseData['vnp_ResponseCode'] ?? null;
            $transactionId = $responseData['vnp_TransactionNo'] ?? null;
            $amount = $responseData['vnp_Amount'] ?? null;

            // Find order
            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                Log::error('VNPay Return - Order not found', ['order_number' => $orderNumber]);
                return view('payment.error', [
                    'message' => 'Order not found',
                    'error' => 'Order number: ' . $orderNumber
                ]);
            }

            // Process payment result
            if ($responseCode === '00') {
                // Payment successful
                $order->update([
                    'status' => 'paid',
                    'payment_transaction_id' => $transactionId,
                    'paid_at' => now(),
                    'payment_error' => null
                ]);

                Log::info('VNPay Return - Payment successful', [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'amount' => $amount
                ]);

                return view('payment.success', [
                    'order' => $order,
                    'transaction_id' => $transactionId,
                    'amount' => $amount ? $amount / 100 : null
                ]);

            } else {
                // Payment failed
                $order->update([
                    'status' => 'payment_failed',
                    'payment_error' => "VNPay Error: {$responseCode}"
                ]);

                Log::error('VNPay Return - Payment failed', [
                    'order_id' => $order->id,
                    'response_code' => $responseCode,
                    'response_data' => $responseData
                ]);

                return view('payment.error', [
                    'message' => 'Payment failed',
                    'error' => "VNPay Error Code: {$responseCode}",
                    'order' => $order
                ]);
            }

        } catch (\Exception $e) {
            Log::error('VNPay Return - Exception: ' . $e->getMessage());
            return view('payment.error', [
                'message' => 'Payment processing error',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Xử lý IPN (Instant Payment Notification) từ VNPay
     */
    public function vnpayIpn(Request $request)
    {
        try {
            Log::info('VNPay IPN received', $request->all());

            // Verify payment response
            $verification = $this->vnpayService->verifyPaymentResponse($request->all());

            if (!$verification['valid']) {
                Log::error('VNPay IPN - Invalid response', $verification);
                return response('Invalid response', 400);
            }

            $responseData = $verification['data'];
            $orderNumber = $responseData['vnp_TxnRef'] ?? null;
            $responseCode = $responseData['vnp_ResponseCode'] ?? null;
            $transactionId = $responseData['vnp_TransactionNo'] ?? null;
            $amount = $responseData['vnp_Amount'] ?? null;

            // Find order
            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                Log::error('VNPay IPN - Order not found', ['order_number' => $orderNumber]);
                return response('Order not found', 404);
            }

            // Process payment result
            if ($responseCode === '00') {
                // Payment successful
                $order->update([
                    'status' => 'paid',
                    'payment_transaction_id' => $transactionId,
                    'paid_at' => now(),
                    'payment_error' => null
                ]);

                Log::info('VNPay IPN - Payment successful', [
                    'order_id' => $order->id,
                    'transaction_id' => $transactionId,
                    'amount' => $amount
                ]);

                return response('OK', 200);

            } else {
                // Payment failed
                $order->update([
                    'status' => 'payment_failed',
                    'payment_error' => "VNPay Error: {$responseCode}"
                ]);

                Log::error('VNPay IPN - Payment failed', [
                    'order_id' => $order->id,
                    'response_code' => $responseCode,
                    'response_data' => $responseData
                ]);

                return response('Payment failed', 400);
            }

        } catch (\Exception $e) {
            Log::error('VNPay IPN - Exception: ' . $e->getMessage());
            return response('Internal error', 500);
        }
    }

    /**
     * Test payment URL creation (no authentication required)
     */
    public function testPayment(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:1000',
                'order_info' => 'nullable|string',
                'bank_code' => 'nullable|string',
                'billing' => 'nullable|array',
                'invoice' => 'nullable|array',
            ]);

            // Prepare test payment data
            $paymentData = [
                'amount' => $request->amount,
                'order_info' => $request->order_info ?: 'Test payment',
                'txn_ref' => 'TEST_' . time(),
                'locale' => 'vn',
                'order_type' => 'billpayment',
                'bank_code' => $request->bank_code,
                'billing' => $request->billing,
                'invoice' => $request->invoice,
            ];

            // Create payment URL
            $result = $this->vnpayService->createPaymentUrl($paymentData);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('VNPay Test Payment Error: ' . $e->getMessage());
            return response()->json([
                'code' => '99',
                'message' => 'Error creating test payment: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
}
