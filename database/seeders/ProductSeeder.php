<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $category = Category::create([
            'name' => 'Thực phẩm chức năng',
            'slug' => 'thuc-pham-chuc-nang',
        ]);

        for ($i = 1; $i <= 25; $i++) {
            $isVariable = $i % 2 === 0; // sản phẩm chẵn có biến thể

            $product = Product::create([
                'category_id' => $category->id,
                'name' => 'Sản phẩm số ' . $i,
                'slug' => 'san-pham-so-' . $i,
                'description' => 'Mô tả cho sản phẩm số ' . $i,
                'product_type' => $isVariable ? 'variable' : 'simple',
                'price' =>  rand(100000, 300000),
                'stock_quantity' =>  rand(10, 100),
                'image' =>  'products/product' . $i . '.jpg',
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'products/gallery' . $i . '.jpg',
                'alt_text' => 'Hình sản phẩm số ' . $i,
                'is_main' => true,
            ]);

            if ($isVariable) {
                foreach (["30 viên", "60 viên"] as $count) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => 'SKU-' . $i . '-' . Str::slug($count),
                        'attribute_json' => json_encode(['số_lượng' => $count]),
                        'price' => rand(100000, 300000),
                        'stock_quantity' => rand(5, 50),
                        'image' => 'variants/product' . $i . '-' . Str::slug($count) . '.jpg',
                    ]);
                }
            }
        }
    }
}

