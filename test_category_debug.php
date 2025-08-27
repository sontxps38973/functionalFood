<?php

/**
 * Script debug cho vấn đề Category Update
 * Chạy: php test_category_debug.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== DEBUG CATEGORY UPDATE ===\n\n";

try {
    // 1. Kiểm tra kết nối database
    echo "1. Kiểm tra kết nối database...\n";
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connected successfully\n\n";
    
    // 2. Kiểm tra category có tồn tại không
    echo "2. Kiểm tra category ID 1...\n";
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
    
    // 3. Test update category
    echo "3. Test update category...\n";
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
    
    // 4. Test CategoryResource
    echo "4. Test CategoryResource...\n";
    $resource = new \App\Http\Resources\CategoryResource($category);
    $resourceData = $resource->toArray(request());
    
    echo "   Resource data:\n";
    foreach ($resourceData as $key => $value) {
        echo "   {$key}: " . (is_null($value) ? 'null' : $value) . "\n";
    }
    echo "\n";
    
    // 5. Kiểm tra database trực tiếp
    echo "5. Kiểm tra database trực tiếp...\n";
    $dbCategory = DB::table('categories')->where('id', 1)->first();
    
    if ($dbCategory) {
        echo "✅ Database record found:\n";
        echo "   ID: {$dbCategory->id}\n";
        echo "   Name: {$dbCategory->name}\n";
        echo "   Slug: {$dbCategory->slug}\n";
        echo "   Created: {$dbCategory->created_at}\n";
        echo "   Updated: {$dbCategory->updated_at}\n\n";
    } else {
        echo "❌ Database record not found\n\n";
    }
    
    // 6. Restore original name
    echo "6. Restore original name...\n";
    $category->update([
        'name' => $oldName,
        'slug' => \Illuminate\Support\Str::slug($oldName)
    ]);
    echo "✅ Original name restored\n\n";
    
    echo "=== DEBUG COMPLETED ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
