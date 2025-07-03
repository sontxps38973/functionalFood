<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;



class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Add other seeders here
            // Example: CategorySeeder::class,
            ProductSeeder::class,
            CategorySeeder::class,
           OrderSeeder::class,
            CouponSeeder::class,
            AdminSeeder::class,
            ProductReviewSeeder::class,
            // ProductImageSeeder::class,
            // ProductViewSeeder::class,
            // WishlistSeeder::class,
        ]);
        // User::factory(10)->create();


        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'password' => 'pass',
        ]);
    }
}
