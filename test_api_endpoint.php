<?php

/**
 * Script test API endpoint cho Category Update
 * Chạy: php test_api_endpoint.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\CategoryController;
use App\Models\Category;
use App\Http\Requests\UpdateCategoryRequest;

echo "=== TEST API ENDPOINT ===\n\n";

try {
    // 1. Tạo mock request
    echo "1. Tạo mock request...\n";
    
    $requestData = [
        'name' => 'Test Category API ' . date('Y-m-d H:i:s')
    ];
    
    $request = Request::create(
        '/api/admin-categories/1',
        'PUT',
        $requestData,
        [],
        [],
        [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CONTENT_TYPE' => 'application/json',
        ]
    );
    
    echo "   Request data: " . json_encode($requestData) . "\n\n";
    
    // 2. Tạo UpdateCategoryRequest
    echo "2. Tạo UpdateCategoryRequest...\n";
    
    // Tạo request với container
    $updateRequest = UpdateCategoryRequest::createFromBase($request);
    
    // Kiểm tra request có được tạo thành công không
    if (!$updateRequest) {
        echo "   ❌ Failed to create UpdateCategoryRequest\n";
        exit(1);
    }
    
    // Validate request
    try {
        $validatedData = $updateRequest->validated();
        echo "   Validated data: " . json_encode($validatedData) . "\n\n";
    } catch (Exception $e) {
        echo "   ❌ Validation error: " . $e->getMessage() . "\n\n";
        exit(1);
    }
    
    // 3. Lấy category
    echo "3. Lấy category...\n";
    $category = Category::find(1);
    
    if (!$category) {
        echo "❌ Category ID 1 không tồn tại\n";
        exit(1);
    }
    
    echo "   Category before update:\n";
    echo "   ID: {$category->id}\n";
    echo "   Name: {$category->name}\n";
    echo "   Slug: {$category->slug}\n\n";
    
    // 4. Test controller method
    echo "4. Test controller method...\n";
    $controller = new CategoryController();
    
    // Gọi method update
    $response = $controller->update($updateRequest, $category);
    
    echo "   Response status: " . $response->getStatusCode() . "\n";
    echo "   Response content: " . $response->getContent() . "\n\n";
    
    // 5. Parse response
    echo "5. Parse response...\n";
    $responseData = json_decode($response->getContent(), true);
    
    if (isset($responseData['data'])) {
        echo "   Response data:\n";
        foreach ($responseData['data'] as $key => $value) {
            echo "   {$key}: " . (is_null($value) ? 'null' : $value) . "\n";
        }
        
        // Kiểm tra null values
        $nullCount = 0;
        foreach ($responseData['data'] as $value) {
            if (is_null($value)) {
                $nullCount++;
            }
        }
        
        if ($nullCount > 0) {
            echo "\n   ⚠️  WARNING: {$nullCount} null values found!\n";
        } else {
            echo "\n   ✅ No null values found\n";
        }
    } else {
        echo "   ❌ No data in response\n";
    }
    
    echo "\n=== TEST COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
