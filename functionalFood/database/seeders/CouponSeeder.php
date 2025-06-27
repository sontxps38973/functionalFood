<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        // Mã giảm 10% toàn đơn, cho tất cả, trong 7 ngày
        Coupon::create([
            'code' => 'SALE10',
            'type' => 'percent',
            'value' => 10,
            'scope' => 'order',
            'is_active' => true,
            'start_at' => now(),
            'end_at' => now()->addDays(7),
            'min_order_value' => 100000,
            'max_discount' => 50000,
        ]);

        // Mã giảm 50.000 cho danh mục ID = 2, giới hạn 100 lượt
        Coupon::create([
            'code' => 'FOOD50',
            'type' => 'fixed',
            'value' => 50000,
            'scope' => 'category',
            'target_ids' => json_encode([2]),
            'is_active' => true,
            'usage_limit' => 100,
        ]);

        // Mã cho sản phẩm iPhone (ID = 3), chỉ dùng cho VIP
        Coupon::create([
            'code' => 'VIPIPHONE',
            'type' => 'percent',
            'value' => 15,
            'scope' => 'product',
            'target_ids' => json_encode([3]),
            'allowed_rank_ids' => json_encode([3]), // rank 3 = Vàng
            'only_once_per_user' => true,
            'is_active' => true,
        ]);

        // Mã flash sale 30% mỗi Thứ Sáu từ 18:00 đến 22:00
        Coupon::create([
            'code' => 'FRIDAY30',
            'type' => 'percent',
            'value' => 30,
            'scope' => 'order',
            'is_active' => true,
            'time_rules' => json_encode([
                'days' => [5], // Thứ Sáu
                'hours' => [
                    ['from' => '18:00', 'to' => '22:00'],
                ],
            ]),
        ]);

        // Mã chào mừng người dùng mới
        Coupon::create([
            'code' => 'WELCOME',
            'type' => 'fixed',
            'value' => 70000,
            'scope' => 'order',
            'is_active' => true,
            'first_time_only' => true,
        ]);
    }
}
