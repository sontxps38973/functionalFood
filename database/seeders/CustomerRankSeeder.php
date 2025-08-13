<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerRank;

class CustomerRankSeeder extends Seeder
{
    public function run(): void
    {
        CustomerRank::insert([
            [
                'name' => 'Đồng',
                'level' => 1,
                'min_total_spent' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bạc',
                'level' => 2,
                'min_total_spent' => 1000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Vàng',
                'level' => 3,
                'min_total_spent' => 5000000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
} 