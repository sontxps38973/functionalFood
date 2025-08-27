<?php

/**
 * Script test với authentication cho Category Update
 * Chạy: php test_with_auth.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

echo "=== TEST WITH AUTHENTICATION ===\n\n";

try {
    // 1. Kiểm tra admin có tồn tại không
    echo "1. Kiểm tra admin...\n";
    $admin = Admin::first();
    
    if (!$admin) {
        echo "❌ Không có admin nào trong database\n";
        exit(1);
    }
    
    echo "✅ Admin found:\n";
    echo "   ID: {$admin->id}\n";
    echo "   Email: {$admin->email}\n";
    echo "   Name: {$admin->name}\n\n";
    
    // 2. Tạo token cho admin
    echo "2. Tạo token cho admin...\n";
    $token = $admin->createToken('admin-token')->plainTextToken;
    echo "✅ Token created: " . substr($token, 0, 20) . "...\n\n";
    
    // 3. Set token cho request
    echo "3. Set token cho request...\n";
    $request = request();
    $request->headers->set('Authorization', 'Bearer ' . $token);
    echo "✅ Token set for request\n\n";
    
    // 4. Kiểm tra category
    echo "4. Kiểm tra category...\n";
    $category = Category::find(1);
    
    if (!$category) {
        echo "❌ Category ID 1 không tồn tại\n";
        exit(1);
    }
    
    echo "✅ Category found:\n";
    echo "   ID: {$category->id}\n";
    echo "   Name: {$category->name}\n";
    echo "   Slug: {$category->slug}\n\n";
    
    // 5. Test update với authentication
    echo "5. Test update với authentication...\n";
    $oldName = $category->name;
    $newName = "Test Auth Category " . date('Y-m-d H:i:s');
    
    echo "   Old name: {$oldName}\n";
    echo "   New name: {$newName}\n";
    
    // Update category
    $updateResult = $category->update([
        'name' => $newName,
        'slug' => \Illuminate\Support\Str::slug($newName)
    ]);
    
    echo "   Update result: " . ($updateResult ? 'true' : 'false') . "\n";
    
    // Refresh model
    $category->refresh();
    
    echo "   After refresh:\n";
    echo "   ID: {$category->id}\n";
    echo "   Name: {$category->name}\n";
    echo "   Slug: {$category->slug}\n\n";
    
    // 6. Test CategoryResource với authentication
    echo "6. Test CategoryResource với authentication...\n";
    $resource = new \App\Http\Resources\CategoryResource($category);
    $resourceData = $resource->toArray(request());
    
    echo "   Resource data:\n";
    foreach ($resourceData as $key => $value) {
        echo "   {$key}: " . (is_null($value) ? 'null' : $value) . "\n";
    }
    echo "\n";
    
    // 7. Kiểm tra null values
    echo "7. Kiểm tra null values...\n";
    $nullCount = 0;
    $nullFields = [];
    
    foreach ($resourceData as $key => $value) {
        if (is_null($value)) {
            $nullCount++;
            $nullFields[] = $key;
        }
    }
    
    if ($nullCount > 0) {
        echo "   ⚠️  WARNING: {$nullCount} null values found!\n";
        echo "   Null fields: " . implode(', ', $nullFields) . "\n\n";
    } else {
        echo "   ✅ No null values found\n\n";
    }
    
    // 8. Restore original name
    echo "8. Restore original name...\n";
    $category->update([
        'name' => $oldName,
        'slug' => \Illuminate\Support\Str::slug($oldName)
    ]);
    echo "✅ Original name restored\n\n";
    
    // 9. Cleanup
    echo "9. Cleanup...\n";
    $admin->tokens()->delete();
    echo "✅ Tokens deleted\n\n";
    
    echo "=== TEST WITH AUTHENTICATION COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
