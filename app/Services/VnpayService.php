<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VnpayService
{
    private $vnp_Url;
    private $vnp_TmnCode;
    private $vnp_HashSecret;
    private $vnp_ReturnUrl;
    private $vnp_IpnUrl;

    public function __construct()
    {
        $this->vnp_Url = config('services.vnpay.payment_url');
        $this->vnp_TmnCode = config('services.vnpay.tmn_code');
        $this->vnp_HashSecret = config('services.vnpay.hash_secret');
        $this->vnp_ReturnUrl = config('app.url') . '/payment/return';
        $this->vnp_IpnUrl = config('app.url') . '/api/v1/vnpay-ipn';
    }

    /**
     * Tạo payment URL theo hướng dẫn chính thức VNPay
     */
    public function createPaymentUrl(array $paymentData): array
    {
        try {
            // Validate required fields
            $this->validatePaymentData($paymentData);

            // Get client IP address
            $vnp_IpAddr = $this->getClientIp();

            // Prepare input data
            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $this->vnp_TmnCode,
                "vnp_Amount" => $paymentData['amount'] * 100, // VNPay expects amount * 100
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $paymentData['locale'] ?? 'vn',
                "vnp_OrderInfo" => $paymentData['order_info'],
                "vnp_OrderType" => $paymentData['order_type'] ?? 'billpayment',
                "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
                "vnp_IpnUrl" => $this->vnp_IpnUrl,
                "vnp_TxnRef" => $paymentData['txn_ref'],
            ];

            // Add optional billing information
            if (isset($paymentData['billing'])) {
                $inputData = array_merge($inputData, $this->prepareBillingData($paymentData['billing']));
            }

            // Add optional invoice information
            if (isset($paymentData['invoice'])) {
                $inputData = array_merge($inputData, $this->prepareInvoiceData($paymentData['invoice']));
            }

            // Add bank code if specified
            if (isset($paymentData['bank_code']) && !empty($paymentData['bank_code'])) {
                $inputData['vnp_BankCode'] = $paymentData['bank_code'];
            }

            // Add expire date if specified
            if (isset($paymentData['expire_date']) && !empty($paymentData['expire_date'])) {
                $inputData['vnp_ExpireDate'] = $paymentData['expire_date'];
            }

            // Sort input data
            ksort($inputData);

            // Build query string and hash data
            $query = "";
            $hashdata = "";
            $i = 0;

            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            // Build payment URL
            $vnp_Url = $this->vnp_Url . "?" . $query;
            
            // Add secure hash
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;

            // Log payment creation
            Log::info('VNPay Payment URL created', [
                'txn_ref' => $paymentData['txn_ref'],
                'amount' => $paymentData['amount'],
                'ip_addr' => $vnp_IpAddr,
                'url' => $vnp_Url
            ]);

            return [
                'code' => '00',
                'message' => 'success',
                'data' => [
                    'payment_url' => $vnp_Url,
                    'txn_ref' => $paymentData['txn_ref'],
                    'amount' => $paymentData['amount'],
                    'hash_data' => $hashdata,
                    'secure_hash' => $vnpSecureHash
                ]
            ];

        } catch (\Exception $e) {
            Log::error('VNPay Payment URL creation failed', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);

            return [
                'code' => '99',
                'message' => 'Payment URL creation failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Validate payment data
     */
    private function validatePaymentData(array $paymentData): void
    {
        $required = ['amount', 'order_info', 'txn_ref'];
        
        foreach ($required as $field) {
            if (!isset($paymentData[$field]) || empty($paymentData[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }

        if ($paymentData['amount'] < 1000) {
            throw new \InvalidArgumentException("Amount must be at least 1,000 VND");
        }
    }

    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $ip = request()->ip();
        
        // Handle localhost/empty IP scenarios
        if (empty($ip) || 
            $ip === '127.0.0.1' || 
            $ip === '::1' || 
            $ip === 'localhost' ||
            $ip === '0.0.0.0') {
            
            $realIp = request()->header('X-Forwarded-For') ?: 
                      request()->header('X-Real-IP') ?: 
                      request()->header('CF-Connecting-IP') ?: 
                      '203.205.254.157'; // Fallback to public IP
            
            $ip = $realIp;
        }

        return $ip;
    }

    /**
     * Prepare billing data
     */
    private function prepareBillingData(array $billing): array
    {
        $billingData = [];

        if (isset($billing['mobile'])) {
            $billingData['vnp_Bill_Mobile'] = $billing['mobile'];
        }

        if (isset($billing['email'])) {
            $billingData['vnp_Bill_Email'] = $billing['email'];
        }

        if (isset($billing['fullname'])) {
            $fullName = trim($billing['fullname']);
            if (!empty($fullName)) {
                $name = explode(' ', $fullName);
                $billingData['vnp_Bill_FirstName'] = array_shift($name);
                $billingData['vnp_Bill_LastName'] = !empty($name) ? implode(' ', $name) : '';
            }
        }

        if (isset($billing['address'])) {
            $billingData['vnp_Bill_Address'] = $billing['address'];
        }

        if (isset($billing['city'])) {
            $billingData['vnp_Bill_City'] = $billing['city'];
        }

        if (isset($billing['country'])) {
            $billingData['vnp_Bill_Country'] = $billing['country'];
        }

        if (isset($billing['state'])) {
            $billingData['vnp_Bill_State'] = $billing['state'];
        }

        return $billingData;
    }

    /**
     * Prepare invoice data
     */
    private function prepareInvoiceData(array $invoice): array
    {
        $invoiceData = [];

        if (isset($invoice['phone'])) {
            $invoiceData['vnp_Inv_Phone'] = $invoice['phone'];
        }

        if (isset($invoice['email'])) {
            $invoiceData['vnp_Inv_Email'] = $invoice['email'];
        }

        if (isset($invoice['customer'])) {
            $invoiceData['vnp_Inv_Customer'] = $invoice['customer'];
        }

        if (isset($invoice['address'])) {
            $invoiceData['vnp_Inv_Address'] = $invoice['address'];
        }

        if (isset($invoice['company'])) {
            $invoiceData['vnp_Inv_Company'] = $invoice['company'];
        }

        if (isset($invoice['taxcode'])) {
            $invoiceData['vnp_Inv_Taxcode'] = $invoice['taxcode'];
        }

        if (isset($invoice['type'])) {
            $invoiceData['vnp_Inv_Type'] = $invoice['type'];
        }

        return $invoiceData;
    }

    /**
     * Verify payment response
     */
    public function verifyPaymentResponse(array $responseData): array
    {
        try {
            // Check if response has required fields
            if (!isset($responseData['vnp_SecureHash'])) {
                return ['valid' => false, 'message' => 'Missing secure hash'];
            }

            // Get secure hash from response
            $vnp_SecureHash = $responseData['vnp_SecureHash'];
            unset($responseData['vnp_SecureHash']);

            // Sort response data
            ksort($responseData);

            // Build hash data
            $hashdata = "";
            $i = 0;
            foreach ($responseData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            // Calculate secure hash
            $secureHash = hash_hmac('sha512', $hashdata, $this->vnp_HashSecret);

            // Verify hash
            if ($secureHash === $vnp_SecureHash) {
                return [
                    'valid' => true,
                    'message' => 'Payment response verified successfully',
                    'data' => $responseData
                ];
            } else {
                return [
                    'valid' => false,
                    'message' => 'Invalid secure hash',
                    'data' => $responseData
                ];
            }

        } catch (\Exception $e) {
            Log::error('VNPay Payment verification failed', [
                'error' => $e->getMessage(),
                'response_data' => $responseData
            ]);

            return [
                'valid' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
                'data' => $responseData
            ];
        }
    }
}
