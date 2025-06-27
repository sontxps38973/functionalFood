<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CouponUser;
use Illuminate\Support\Facades\DB;

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
        'items.*.price' => 'required|numeric|min:0',
        'items.*.quantity' => 'required|integer|min:1',
        'coupon_id' => 'nullable|integer|exists:coupons,id',
        'discount' => 'nullable|numeric|min:0',
        'total' => 'required|numeric|min:0',
    ]);

    $user = $request->user();

    DB::beginTransaction();

    try {
        // ✅ 1. Tạo đơn hàng
        $order = Order::create([
            'user_id' => $user->id,
            'discount' => $request->discount ?? 0,
            'total' => $request->total,
            'status' => 'paid', // hoặc 'pending' tùy hệ thống bạn
        ]);

        // ✅ 2. Thêm các sản phẩm vào bảng order_items
        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $item['variant_id'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
            ]);
        }

        // ✅ 3. Nếu có mã giảm giá → ghi lại và tăng lượt
        if ($request->filled('coupon_id')) {
            $coupon = Coupon::findOrFail($request->coupon_id);

            // Ghi nhận vào bảng coupon_user
            CouponUser::create([
                'coupon_id' => $coupon->id,
                'user_id' => $user->id,
                'usage_count' => 1,
                'used_at' => now(),
                'order_id' => $order->id
            ]);

            // Tăng lượt sử dụng
            $coupon->increment('used_count');
        }

        // ✅ 4. Cập nhật rank người dùng (dựa theo tổng chi tiêu)
        $this->updateUserRank($user);

        DB::commit();

        return response()->json([
            'message' => 'Đặt hàng thành công',
            'order_id' => $order->id
        ], 201);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Lỗi khi đặt hàng',
            'error' => $e->getMessage()
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



}
