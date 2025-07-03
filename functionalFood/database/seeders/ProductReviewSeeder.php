<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductReview;

class ProductReviewSeeder extends Seeder
{
    public function run()
    {
        $users = User::all();
        if ($users->count() < 5) {
            $users = User::factory(10)->create();
        }
        $userIds = $users->pluck('id')->all();

        $products = Product::all();
        foreach ($products as $product) {
            $reviewCount = rand(2, 5);
            for ($i = 0; $i < $reviewCount; $i++) {
                ProductReview::factory()->create([
                    'product_id' => $product->id,
                    'user_id' => $userIds[array_rand($userIds)],
                ]);
            }
        }
    }
} 