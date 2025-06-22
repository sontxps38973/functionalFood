<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $coupon = Coupon::factory()->create([
            'type' => 'percent',
            'value' => 10,
            'max_discount' => 50000,
            'min_order_value' => 200000,
        ]);

        $products = Product::inRandomOrder()->limit(2)->get();

        $subtotal = $products->sum(fn($product) => $product->price * 2);
        $discount = min($subtotal * ($coupon->value / 100), $coupon->max_discount);
        $total = $subtotal - $discount;

        $order = Order::create([
            'user_id' => $user->id,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon_id' => $coupon->id,
            'status' => 'pending',
            'payment_method' => 'cod',
        ]);

        foreach ($products as $product) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => 2,
                'total' => $product->price * 2,
            ]);
        }

        DB::table('coupon_user')->insert([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'usage_count' => 1,
            'used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
