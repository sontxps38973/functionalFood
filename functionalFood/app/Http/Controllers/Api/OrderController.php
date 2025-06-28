<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CouponUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
 public function applyCoupon(Request $request)
{
    $request->validate([
        'coupon_code' => 'required|string',
        'payment_method' => 'required|string|in:cod,bank_transfer,online_payment', // Thêm phương thức thanh toán
        'subtotal' => 'required|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|integer',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.quantity' => 'required|integer|min:1', 
    ]);

    $user = $request->user();
    $subtotal = $request->subtotal;
    $items = collect($request->items);

    $coupon = Coupon::where('code', $request->coupon_code)
        ->where('is_active', true)
        ->where(function ($q) {
            $now = now();
            $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
        })
        ->first();

    if (!$coupon) {
        return response()->json(['message' => 'Mã không hợp lệ hoặc đã hết hạn.'], 422);
    }

    if ($coupon->allowed_rank_ids) {
        $allowed = json_decode($coupon->allowed_rank_ids, true);
        if (!in_array($user->customer_rank_id, $allowed)) {
            return response()->json(['message' => 'Hạng thành viên của bạn không đủ điều kiện.'], 422);
        }
    }

    if ($coupon->first_time_only && $user->orders()->exists()) {
        return response()->json(['message' => 'Mã chỉ áp dụng cho đơn hàng đầu tiên.'], 422);
    }

    if ($coupon->only_once_per_user && $coupon->users()->where('user_id', $user->id)->exists()) {
        return response()->json(['message' => 'Bạn đã sử dụng mã này rồi.'], 422);
    }

    if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
        return response()->json(['message' => 'Mã đã hết lượt sử dụng.'], 422);
    }

    if ($coupon->time_rules) {
        $rules = json_decode($coupon->time_rules, true);
        $now = now();

        if (isset($rules['days']) && !in_array($now->dayOfWeek, $rules['days'])) {
            return response()->json(['message' => 'Mã chỉ áp dụng vào một số ngày nhất định.'], 422);
        }

        if (isset($rules['hours'])) {
            $hour = $now->format('H:i');
            $inRange = collect($rules['hours'])->contains(fn($r) =>
                $hour >= $r['from'] && $hour <= $r['to']
            );
            if (!$inRange) {
                return response()->json(['message' => 'Mã chỉ áp dụng trong khung giờ quy định.'], 422);
            }
        }
    }

    if ($coupon->min_order_value && $subtotal < $coupon->min_order_value) {
        return response()->json(['message' => 'Giá trị đơn hàng chưa đủ để áp mã.'], 422);
    }
    // kiểm tra phuơng thức thanh toán
    if ($coupon->allowed_payment_methods) {
        $allowedMethods = json_decode($coupon->allowed_payment_methods, true);
        if (!in_array($request->payment_method, $allowedMethods)) {
            return response()->json(['message' => 'Phương thức thanh toán không được áp dụng mã này.'], 422);
        }
    }

    $eligibleSubtotal = $subtotal;

    if ($coupon->scope === 'product') {
        $productIds = json_decode($coupon->target_ids ?? '[]', true);
        $eligibleItems = $items->whereIn('product_id', $productIds);
        $eligibleSubtotal = $eligibleItems->sum(fn($i) => $i['price'] * $i['quantity']);

        if ($eligibleSubtotal <= 0) {
            return response()->json(['message' => 'Không có sản phẩm phù hợp để áp mã.'], 422);
        }

    } elseif ($coupon->scope === 'category') {
        $categoryIds = json_decode($coupon->target_ids ?? '[]', true);
        $productMap = \App\Models\Product::whereIn('id', $items->pluck('product_id'))
            ->pluck('category_id', 'id')
            ->toArray();

        $eligibleItems = $items->filter(function ($item) use ($productMap, $categoryIds) {
            return isset($productMap[$item['product_id']]) &&
                in_array($productMap[$item['product_id']], $categoryIds);
        });

        $eligibleSubtotal = $eligibleItems->sum(fn($i) => $i['price'] * $i['quantity']);

        if ($eligibleSubtotal <= 0) {
            return response()->json(['message' => 'Không có sản phẩm thuộc danh mục áp dụng mã.'], 422);
        }
    }

    $discount = 0;
    if ($coupon->type === 'percent') {
        $discount = $eligibleSubtotal * ($coupon->value / 100);
        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }
    } elseif ($coupon->type === 'fixed') {
        $discount = $coupon->value;
    }

    $total = max(0, $subtotal - $discount);

    return response()->json([
        'message' => 'Áp mã thành công.',
        'discount' => $discount,
        'total' => $total,
        'coupon_id' => $coupon->id,
        'coupon_type' => $coupon->type,
        'coupon_value' => $coupon->value,
    ]);
}

public function placeOrder(Request $request)
{
    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.variant_id' => 'required|integer|exists:product_variants,id',
        'items.*.quantity' => 'required|integer|min:1',
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:500',
        'email' => 'required|email|max:255',
        'payment_method' => 'required|string|in:cod,bank_transfer,online_payment',
        'coupon_id' => 'nullable|integer|exists:coupons,id',
        'subtotal' => 'required|numeric|min:0',
        'discount' => 'nullable|numeric|min:0',
        'total' => 'required|numeric|min:0',
        'notes' => 'nullable|string|max:1000',
    ]);

    $user = $request->user();

    // Kiểm tra tồn kho trước khi đặt hàng
    $stockErrors = [];
    $variants = [];
    $calculatedSubtotal = 0;

    foreach ($request->items as $item) {
        $variant = \App\Models\ProductVariant::with('product')->find($item['variant_id']);
        if (!$variant) {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 422);
        }
        if ($variant->stock_quantity < $item['quantity']) {
            $stockErrors[] = "Sản phẩm {$variant->product->name} chỉ còn {$variant->stock_quantity} trong kho.";
        }
        $price = $variant->discount > 0 ? $variant->price - $variant->discount : $variant->price;
        $calculatedSubtotal += $price * $item['quantity'];
        $variants[] = [
            'variant' => $variant,
            'quantity' => $item['quantity'],
            'price' => $price
        ];
    }
    if (!empty($stockErrors)) {
        return response()->json([
            'message' => 'Một số sản phẩm không đủ số lượng trong kho.',
            'errors' => $stockErrors
        ], 422);
    }
    // Kiểm tra tính toán subtotal
    if (abs($calculatedSubtotal - $request->subtotal) > 0.01) {
        return response()->json(['message' => 'Tổng tiền hàng không khớp.'], 422);
    }

    // Kiểm tra coupon nếu có (chỉ kiểm tra trạng thái, không tính lại discount)
    $coupon = null;
    if ($request->filled('coupon_id')) {
        $coupon = Coupon::find($request->coupon_id);
        if (!$coupon || !$coupon->is_active) {
            return response()->json(['message' => 'Mã giảm giá không hợp lệ.'], 422);
        }
        $now = now();
        if (($coupon->start_at && $now < $coupon->start_at) || ($coupon->end_at && $now > $coupon->end_at)) {
            return response()->json(['message' => 'Mã giảm giá đã hết hạn hoặc chưa có hiệu lực.'], 422);
        }
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['message' => 'Mã đã hết lượt sử dụng.'], 422);
        }
    }

    // Kiểm tra discount và total gửi lên (chỉ kiểm tra không âm, không lớn hơn subtotal)
    $finalDiscount = $request->discount ?? 0;
    $finalTotal = $request->total;
    if ($finalDiscount < 0 || $finalDiscount > $calculatedSubtotal) {
        return response()->json(['message' => 'Giá trị giảm giá không hợp lệ.'], 422);
    }
    if ($finalTotal < 0 || abs($finalTotal - ($calculatedSubtotal - $finalDiscount)) > 0.01) {
        return response()->json(['message' => 'Tổng tiền cuối cùng không hợp lệ.'], 422);
    }

    DB::beginTransaction();
    try {
        $order = Order::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'email' => $request->email,
            'subtotal' => $calculatedSubtotal,
            'discount' => $finalDiscount,
            'total' => $finalTotal,
            'coupon_id' => $coupon?->id,
            'status' => $request->payment_method === 'cod' ? 'pending' : 'paid',
            'payment_method' => $request->payment_method,
        ]);
        foreach ($variants as $item) {
            $variant = $item['variant'];
            $product = $variant->product;
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'product_name' => $product->name,
                'variant_name' => $variant->attribute_name_text,
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'total' => $item['price'] * $item['quantity'],
            ]);
            $variant->decrement('stock_quantity', $item['quantity']);
        }
        // Nếu có mã giảm giá → ghi lại và tăng lượt
        if ($coupon) {
            CouponUser::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'usage_count' => 1,
                'used_at' => now(),
                'order_id' => $order->id
            ]);
            $coupon->increment('used_count');
        }
        $this->updateUserRank($user);
        \App\Models\CartItem::where('user_id', $user->id)->delete();
        DB::commit();
        return response()->json([
            'message' => 'Đặt hàng thành công',
            'order_id' => $order->id,
            'order' => [
                'id' => $order->id,
                'total' => $order->total,
                'status' => $order->status,
                'payment_method' => $order->payment_method,
                'created_at' => $order->created_at
            ]
        ], 201);
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Order placement error: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'request' => $request->all(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Lỗi khi đặt hàng. Vui lòng thử lại sau.',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

protected function updateUserRank($user)
{
    $totalSpent = $user->orders()
        ->where('status', 'paid')
        ->sum('total');

    $newRank = \App\Models\CustomerRank::where('min_total_spent', '<=', $totalSpent)
        ->orderByDesc('min_total_spent')
        ->first();

    if ($newRank && $user->customer_rank_id !== $newRank->id) {
        $user->update(['customer_rank_id' => $newRank->id]);
    }
}

/**
 * Lấy danh sách đơn hàng của user
 */
public function getOrders(Request $request)
{
    $user = $request->user();
    
    $orders = $user->orders()
        ->with(['items.product', 'items.variant', 'coupon'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return response()->json([
        'orders' => $orders->items(),
        'pagination' => [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ]
    ]);
}

/**
 * Lấy chi tiết đơn hàng
 */
public function getOrderDetail(Request $request, $orderId)
{
    $user = $request->user();
    
    $order = $user->orders()
        ->with(['items.product', 'items.variant', 'coupon'])
        ->find($orderId);

    if (!$order) {
        return response()->json(['message' => 'Đơn hàng không tồn tại.'], 404);
    }

    return response()->json([
        'order' => [
            'id' => $order->id,
            'name' => $order->name,
            'phone' => $order->phone,
            'address' => $order->address,
            'email' => $order->email,
            'subtotal' => $order->subtotal,
            'discount' => $order->discount,
            'total' => $order->total,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'coupon' => $order->coupon ? [
                'code' => $order->coupon->code,
                'type' => $order->coupon->type,
                'value' => $order->coupon->value,
            ] : null,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'total' => $item->total,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => $item->product->images->first()?->url,
                    ] : null,
                ];
            }),
        ]
    ]);
}

/**
 * Hủy đơn hàng
 */
public function cancelOrder(Request $request, $orderId)
{
    $user = $request->user();
    
    $order = $user->orders()->find($orderId);

    if (!$order) {
        return response()->json(['message' => 'Đơn hàng không tồn tại.'], 404);
    }

    // Chỉ cho phép hủy đơn hàng có trạng thái pending hoặc paid
    if (!in_array($order->status, ['pending', 'paid'])) {
        return response()->json(['message' => 'Không thể hủy đơn hàng ở trạng thái này.'], 422);
    }

    DB::beginTransaction();

    try {
        // Cập nhật trạng thái đơn hàng
        $order->update(['status' => 'cancelled']);

        // Hoàn trả tồn kho
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                if ($variant) {
                    $variant->increment('stock_quantity', $item->quantity);
                }
            }
        }

        // Hoàn trả coupon nếu có
        if ($order->coupon_id) {
            $coupon = Coupon::find($order->coupon_id);
            if ($coupon) {
                $coupon->decrement('used_count');
                
                // Xóa record trong coupon_user
                CouponUser::where('coupon_id', $order->coupon_id)
                    ->where('user_id', $user->id)
                    ->where('order_id', $order->id)
                    ->delete();
            }
        }

        // Cập nhật lại rank user
        $this->updateUserRank($user);

        DB::commit();

        return response()->json(['message' => 'Hủy đơn hàng thành công.']);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Cancel order error: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'order_id' => $orderId,
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'message' => 'Lỗi khi hủy đơn hàng. Vui lòng thử lại sau.',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
}

/**
 * Lấy thống kê đơn hàng của user
 */
public function getOrderStats(Request $request)
{
    $user = $request->user();
    
    $stats = [
        'total_orders' => $user->orders()->count(),
        'pending_orders' => $user->orders()->where('status', 'pending')->count(),
        'paid_orders' => $user->orders()->where('status', 'paid')->count(),
        'shipped_orders' => $user->orders()->where('status', 'shipped')->count(),
        'delivered_orders' => $user->orders()->where('status', 'delivered')->count(),
        'cancelled_orders' => $user->orders()->where('status', 'cancelled')->count(),
        'total_spent' => $user->orders()->where('status', 'paid')->sum('total'),
    ];

    return response()->json(['stats' => $stats]);
}

}
