<?php

/**
 * Script test đơn giản cho Category Update
 * Chạy: php test_simple_debug.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Log;

echo "=== SIMPLE DEBUG CATEGORY UPDATE ===\n\n";

try {
    // 1. Kiểm tra category có tồn tại không
    echo "1. Kiểm tra category ID 1...\n";
    $category = Category::find(1);
    
    if (!$category) {
        echo "❌ Category ID 1 không tồn tại\n";
        exit(1);
    }
    
    echo "✅ Category found:\n";
    echo "   ID: {$category->id}\n";
    echo "   Name: {$category->name}\n";
    echo "   Slug: {$category->slug}\n";
    echo "   Created: {$category->created_at}\n";
    echo "   Updated: {$category->updated_at}\n\n";
    
    // 2. Test update trực tiếp
    echo "2. Test update category trực tiếp...\n";
    $oldName = $category->name;
    $newName = "Test Category " . date('Y-m-d H:i:s');
    
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
    echo "   Slug: {$category->slug}\n";
    echo "   Updated: {$category->updated_at}\n\n";
    
    // 3. Test CategoryResource
    echo "3. Test CategoryResource...\n";
    $resource = new CategoryResource($category);
    $resourceData = $resource->toArray(request());
    
    echo "   Resource data:\n";
    foreach ($resourceData as $key => $value) {
        echo "   {$key}: " . (is_null($value) ? 'null' : $value) . "\n";
    }
    echo "\n";
    
    // 4. Kiểm tra null values
    echo "4. Kiểm tra null values...\n";
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
    
    // 5. Test response JSON
    echo "5. Test response JSON...\n";
    $responseData = [
        'message' => 'Cập nhật danh mục thành công',
        'data' => $resourceData
    ];
    
    $jsonResponse = json_encode($responseData, JSON_PRETTY_PRINT);
    echo "   JSON response:\n";
    echo $jsonResponse . "\n\n";
    
    // 6. Restore original name
    echo "6. Restore original name...\n";
    $category->update([
        'name' => $oldName,
        'slug' => \Illuminate\Support\Str::slug($oldName)
    ]);
    echo "✅ Original name restored\n\n";
    
    echo "=== SIMPLE DEBUG COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
