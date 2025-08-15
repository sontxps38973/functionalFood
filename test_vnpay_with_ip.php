<?php
/**
 * Test VNPay Payment with Enhanced IP Address Handling
 */

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\Api\PaymentController;

class VNPayPaymentTest
{
    public function testPaymentCreation()
    {
        echo "🧪 Testing VNPay Payment Creation with Enhanced IP Handling\n";
        echo "===========================================================\n\n";

        try {
            // Create a test request
            $request = $this->createTestRequest();
            
            // Create payment controller
            $controller = new \App\Http\Controllers\Api\PaymentController();
            
            // Test payment creation
            echo "🔍 Testing payment creation...\n";
            echo "Request IP: " . $request->ip() . "\n";
            echo "Request Headers: " . json_encode([
                'X-Forwarded-For' => $request->header('X-Forwarded-For'),
                'X-Real-IP' => $request->header('X-Real-IP'),
                'CF-Connecting-IP' => $request->header('CF-Connecting-IP')
            ]) . "\n\n";
            
            // Call the createPayment method
            $response = $controller->createPayment($request);
            
            echo "✅ Payment creation successful!\n";
            echo "Response: " . $response->getContent() . "\n";
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
        }
    }

    private function createTestRequest()
    {
                    // Create a mock request with test data
            $request = new class {
                public function validate($rules)
                {
                    return true;
                }
                
                public function ip()
                {
                    // Simulate localhost IP
                    return '127.0.0.1';
                }
                
                public function header($name)
                {
                    // Simulate proxy headers
                    $headers = [
                        'X-Forwarded-For' => '203.205.254.157',
                        'X-Real-IP' => '203.205.254.158',
                        'CF-Connecting-IP' => '203.205.254.159'
                    ];
                    
                    return $headers[$name] ?? null;
                }
                
                public function input($key, $default = null)
                {
                    $data = [
                        'order_id' => 1,
                        'amount' => 100000,
                        'return_url' => 'http://localhost:8000/payment/return',
                        'ipn_url' => 'http://localhost:8000/api/v1/vnpay-ipn'
                    ];
                    
                    return $data[$key] ?? $default;
                }
                
                public function all()
                {
                    return [
                        'order_id' => 1,
                        'amount' => 100000,
                        'return_url' => 'http://localhost:8000/payment/return',
                        'ipn_url' => 'http://localhost:8000/api/v1/vnpay-ipn'
                    ];
                }
            };

        return $request;
    }

    public function testIPDetection()
    {
        echo "\n🔍 Testing IP Address Detection Logic\n";
        echo "====================================\n\n";

        $request = $this->createTestRequest();
        
        echo "Original IP: " . $request->ip() . "\n";
        echo "X-Forwarded-For: " . $request->header('X-Forwarded-For') . "\n";
        echo "X-Real-IP: " . $request->header('X-Real-IP') . "\n";
        echo "CF-Connecting-IP: " . $request->header('CF-Connecting-IP') . "\n\n";
        
        // Test the IP detection logic
        $vnp_IpAddr = $request->ip();
        
        if (empty($vnp_IpAddr) || 
            $vnp_IpAddr === '127.0.0.1' || 
            $vnp_IpAddr === '::1' || 
            $vnp_IpAddr === 'localhost' ||
            $vnp_IpAddr === '0.0.0.0') {
            
            $realIp = $request->header('X-Forwarded-For') ?: 
                      $request->header('X-Real-IP') ?: 
                      $request->header('CF-Connecting-IP') ?: 
                      '203.205.254.157';
            
            $vnp_IpAddr = $realIp;
            echo "IP detected as localhost/empty, using fallback: {$vnp_IpAddr}\n";
        }
        
        if (empty($vnp_IpAddr)) {
            $vnp_IpAddr = '203.205.254.157';
            echo "IP still empty, using final fallback: {$vnp_IpAddr}\n";
        }
        
        echo "Final IP for VNPay: {$vnp_IpAddr}\n";
        echo "IP Status: " . ($this->isValidIP($vnp_IpAddr) ? "✅ Valid" : "❌ Invalid") . "\n";
    }

    private function isValidIP($ip)
    {
        return !empty($ip) && 
               $ip !== '127.0.0.1' && 
               $ip !== '::1' && 
               $ip !== 'localhost' && 
               $ip !== '0.0.0.0' &&
               filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}

// Run tests
if (php_sapi_name() === 'cli') {
    $tester = new VNPayPaymentTest();
    $tester->testIPDetection();
    echo "\n";
    $tester->testPaymentCreation();
} else {
    echo "This script should be run from command line\n";
    echo "Usage: php test_vnpay_with_ip.php\n";
}
