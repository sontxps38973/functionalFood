<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerRank;

class CustomerRankSeeder extends Seeder
{
    public function run(): void
    {
        // Kiểm tra và tạo customer ranks nếu chưa có
        $ranks = [
            [
                'name' => 'Đồng',
                'level' => 1,
                'min_total_spent' => 0,
            ],
            [
                'name' => 'Bạc',
                'level' => 2,
                'min_total_spent' => 1000000,
            ],
            [
                'name' => 'Vàng',
                'level' => 3,
                'min_total_spent' => 5000000,
            ],
        ];

        foreach ($ranks as $rank) {
            CustomerRank::firstOrCreate(
                ['level' => $rank['level']], // Tìm theo level
                [
                    'name' => $rank['name'],
                    'min_total_spent' => $rank['min_total_spent'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
} 