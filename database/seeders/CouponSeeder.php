<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            // 1. Coupon giảm giá sản phẩm - phần trăm
            [
                'code' => 'SALE20',
                'description' => 'Giảm 20% cho tất cả sản phẩm',
                'type' => 'percent',
                'value' => 20,
                'max_discount' => 100000,
                'scope' => 'order',
                'min_order_value' => 500000,
                'usage_limit' => 100,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],

            // 2. Coupon giảm giá sản phẩm - cố định
            [
                'code' => 'SAVE50K',
                'description' => 'Giảm 50,000 VNĐ cho đơn hàng từ 300k',
                'type' => 'fixed',
                'value' => 50000,
                'scope' => 'order',
                'min_order_value' => 300000,
                'usage_limit' => 200,
                'used_count' => 0,
                'only_once_per_user' => true,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],

            // 3. Coupon miễn phí vận chuyển
            [
                'code' => 'FREESHIP',
                'description' => 'Miễn phí vận chuyển cho đơn hàng từ 500k',
                'type' => 'fixed',
                'value' => 0,
                'scope' => 'shipping',
                'free_shipping' => true,
                'min_order_value' => 500000,
                'usage_limit' => 50,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(1),
                'is_active' => true,
            ],

            // 4. Coupon giảm giá vận chuyển cố định
            [
                'code' => 'SHIP30K',
                'description' => 'Giảm 30,000 VNĐ phí vận chuyển',
                'type' => 'fixed',
                'value' => 0,
                'scope' => 'shipping',
                'shipping_discount' => 30000,
                'min_order_value' => 300000,
                'usage_limit' => 150,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],

            // 5. Coupon giảm giá vận chuyển theo %
            [
                'code' => 'SHIP50',
                'description' => 'Giảm 50% phí vận chuyển',
                'type' => 'fixed',
                'value' => 0,
                'scope' => 'shipping',
                'shipping_discount_percent' => 50,
                'min_order_value' => 200000,
                'usage_limit' => 100,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(1),
                'is_active' => true,
            ],

            // 6. Coupon cho đơn hàng đầu tiên
            [
                'code' => 'FIRST10',
                'description' => 'Giảm 10% cho đơn hàng đầu tiên',
                'type' => 'percent',
                'value' => 10,
                'scope' => 'order',
                'min_order_value' => 100000,
                'usage_limit' => 500,
                'used_count' => 0,
                'only_once_per_user' => true,
                'first_time_only' => true,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(6),
                'is_active' => true,
            ],

            // 7. Coupon cho sản phẩm cụ thể
            [
                'code' => 'PRODUCT15',
                'description' => 'Giảm 15% cho sản phẩm thực phẩm chức năng',
                'type' => 'percent',
                'value' => 15,
                'scope' => 'product',
                'target_ids' => [1, 2, 3], // Product IDs
                'min_order_value' => 200000,
                'usage_limit' => 80,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],

            // 8. Coupon cho danh mục
            [
                'code' => 'CATEGORY20',
                'description' => 'Giảm 20% cho danh mục vitamin',
                'type' => 'percent',
                'value' => 20,
                'scope' => 'category',
                'target_ids' => [1, 2], // Category IDs
                'min_order_value' => 300000,
                'usage_limit' => 60,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],

            // 9. Coupon flash sale (có thời gian cụ thể)
            [
                'code' => 'FLASH25',
                'description' => 'Flash sale - Giảm 25% trong 24h',
                'type' => 'percent',
                'value' => 25,
                'max_discount' => 200000,
                'scope' => 'order',
                'min_order_value' => 400000,
                'usage_limit' => 30,
                'used_count' => 0,
                'only_once_per_user' => true,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addDay(),
                'time_rules' => [
                    'days' => [1, 2, 3, 4, 5, 6, 0], // Tất cả các ngày
                    'hours' => [
                        ['from' => '09:00', 'to' => '23:59']
                    ]
                ],
                'is_active' => true,
            ],

            // 10. Coupon cho VIP customers
            [
                'code' => 'VIP30',
                'description' => 'Giảm 30% cho khách hàng VIP',
                'type' => 'percent',
                'value' => 30,
                'max_discount' => 300000,
                'scope' => 'order',
                'min_order_value' => 1000000,
                'usage_limit' => 20,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'allowed_rank_ids' => [2, 3], // VIP, Diamond ranks
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(4),
                'is_active' => true,
            ],

            // 11. Coupon kết hợp (sản phẩm + vận chuyển)
            [
                'code' => 'COMBO100',
                'description' => 'Giảm 100k + miễn phí vận chuyển',
                'type' => 'fixed',
                'value' => 100000,
                'scope' => 'order',
                'free_shipping' => true,
                'min_order_value' => 800000,
                'usage_limit' => 40,
                'used_count' => 0,
                'only_once_per_user' => true,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(2),
                'is_active' => true,
            ],

            // 12. Coupon cho phương thức thanh toán cụ thể
            [
                'code' => 'ONLINE10',
                'description' => 'Giảm 10% khi thanh toán online',
                'type' => 'percent',
                'value' => 10,
                'scope' => 'order',
                'min_order_value' => 200000,
                'usage_limit' => 100,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'allowed_payment_methods' => ['online_payment'],
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],

            // 13. Coupon đã hết hạn (để test)
            [
                'code' => 'EXPIRED',
                'description' => 'Coupon đã hết hạn',
                'type' => 'percent',
                'value' => 10,
                'scope' => 'order',
                'min_order_value' => 100000,
                'usage_limit' => 50,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now()->subMonths(2),
                'end_at' => Carbon::now()->subMonth(),
                'is_active' => true,
            ],

            // 14. Coupon chưa có hiệu lực
            [
                'code' => 'FUTURE',
                'description' => 'Coupon chưa có hiệu lực',
                'type' => 'percent',
                'value' => 15,
                'scope' => 'order',
                'min_order_value' => 150000,
                'usage_limit' => 30,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now()->addMonth(),
                'end_at' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],

            // 15. Coupon bị vô hiệu hóa
            [
                'code' => 'DISABLED',
                'description' => 'Coupon bị vô hiệu hóa',
                'type' => 'percent',
                'value' => 5,
                'scope' => 'order',
                'min_order_value' => 50000,
                'usage_limit' => 100,
                'used_count' => 0,
                'only_once_per_user' => false,
                'first_time_only' => false,
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addMonths(1),
                'is_active' => false,
            ],
        ];

        foreach ($coupons as $couponData) {
            Coupon::create($couponData);
        }

        $this->command->info('Coupon seeder completed successfully!');
    }
}
