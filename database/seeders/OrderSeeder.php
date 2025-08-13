<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create([
            'name' => 'Trinh son',
            'email' => 'test2@example.com',
            'phone' => '12345678903',
            'password' => 'passs',
        ]);

        $coupon = Coupon::inRandomOrder()->first() ?? Coupon::factory()->create([
            'code' => 'SEEDER10',
            'type' => 'percent',
            'value' => 10,
            'max_discount' => 50000,
            'min_order_value' => 200000,
            'scope' => 'order',
        ]);

        // Lấy variants thay vì products
        $variants = ProductVariant::with('product')->inRandomOrder()->limit(2)->get();

        if ($variants->isEmpty()) {
            $this->command->info('No product variants found. Skipping order seeding.');
            return;
        }

        $subtotal = $variants->sum(fn($variant) => $variant->price * 2);
        $shippingFee = 30000;
        $tax = 15000;
        $discount = min($subtotal * ($coupon->value / 100), $coupon->max_discount);
        $total = $subtotal + $shippingFee + $tax - $discount;

        $order = Order::create([
            'user_id' => $user->id,
            'name' => 'Nguyễn Văn Test',
            'phone' => '0123456789',
            'address' => '123 Đường Test, Quận 1, TP.HCM',
            'email' => 'test@example.com',
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cod',
            'notes' => 'Đơn hàng test từ seeder',
        ]);

        foreach ($variants as $variant) {
            $product = $variant->product;
            $finalPrice = $variant->discount > 0 ? $variant->price - $variant->discount : $variant->price;
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'variant_name' => $variant->attribute_name_text,
                'sku' => $variant->sku,
                'product_image' => $variant->image ?? $product->image,
                'price' => $variant->price,
                'discount_price' => $variant->discount,
                'final_price' => $finalPrice,
                'quantity' => 2,
                'total' => $finalPrice * 2,
                'weight' => $product->weight ?? null,
                'dimensions' => $product->dimensions ?? null,
                'status' => 'pending',
            ]);
        }

        // Ghi nhận sử dụng coupon
        DB::table('coupon_user')->insert([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'usage_count' => 1,
            'used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tăng lượt sử dụng coupon
        $coupon->increment('used_count');

        $this->command->info('Order seeder completed successfully!');
    }
}
