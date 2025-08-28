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
use Illuminate\Support\Facades\Storage;
use App\Support\Currency;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Services\VnpayService;

class OrderController extends Controller
{
 public function applyCoupon(Request $request)
{
    $request->validate([
        'coupon_code' => 'required|string',
        'payment_method' => 'required|string|in:cod,bank_transfer,online_payment',
        'subtotal' => 'required|numeric|min:0',
        'shipping_fee' => 'nullable|numeric|min:0',
        'tax' => 'nullable|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|integer',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.quantity' => 'required|integer|min:1', 
    ]);

    $user = $request->user();
    $subtotal = $request->subtotal;
    $shippingFee = $request->shipping_fee ?? 0;
    $tax = $request->tax ?? 0;
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

    // Nếu coupon là loại tặng riêng (có trong bảng user_coupons), chỉ cho phép user đã được tặng sử dụng
    if (CouponUser::where('coupon_id', $coupon->id)->exists()) {
        $hasCoupon = CouponUser::where('coupon_id', $coupon->id)
            ->where('user_id', $user->id)
            ->exists();
        if (!$hasCoupon) {
            return response()->json(['message' => 'Bạn không được phép sử dụng mã này.'], 422);
        }
    }

    // Kiểm tra điều kiện sử dụng
    if (!$coupon->canBeUsedByUser($user)) {
        if ($coupon->allowed_rank_ids && !in_array($user->customer_rank_id, $coupon->allowed_rank_ids)) {
            return response()->json(['message' => 'Hạng thành viên của bạn không đủ điều kiện.'], 422);
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
    }

    // Kiểm tra thời gian áp dụng
    if ($coupon->time_rules) {
        $rules = is_array($coupon->time_rules) ? $coupon->time_rules : json_decode((string)$coupon->time_rules, true);
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

    // Kiểm tra giá trị đơn hàng
    $orderValue = $subtotal + $shippingFee + $tax;
    if ($coupon->min_order_value && $orderValue < $coupon->min_order_value) {
        return response()->json(['message' => 'Giá trị đơn hàng chưa đủ để áp mã.'], 422);
    }
    if ($coupon->max_order_value && $orderValue > $coupon->max_order_value) {
        return response()->json(['message' => 'Giá trị đơn hàng vượt quá giới hạn áp dụng mã.'], 422);
    }

    // Kiểm tra phương thức thanh toán
    if ($coupon->allowed_payment_methods) {
        $allowedMethods = is_array($coupon->allowed_payment_methods) ? $coupon->allowed_payment_methods : json_decode((string)$coupon->allowed_payment_methods, true);
        if (!in_array($request->payment_method, $allowedMethods)) {
            return response()->json(['message' => 'Phương thức thanh toán không được áp dụng mã này.'], 422);
        }
    }

    // Tính toán discount cho sản phẩm
    $productDiscount = 0;
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

    // Tính discount cho sản phẩm
    if ($coupon->type === 'percent') {
        $productDiscount = $eligibleSubtotal * ($coupon->value / 100);
        if ($coupon->max_discount) {
            $productDiscount = min($productDiscount, $coupon->max_discount);
        }
    } elseif ($coupon->type === 'fixed') {
        $productDiscount = $coupon->value;
    }

    // Tính discount cho vận chuyển
    $shippingDiscount = 0;
    if ($coupon->isShippingCoupon()) {
        $shippingDiscount = $coupon->calculateShippingDiscount($shippingFee);
    }

    // Tổng discount
    $totalDiscount = $productDiscount + $shippingDiscount;
    $finalShippingFee = max(0, $shippingFee - $shippingDiscount);
    $total = max(0, $orderValue - $totalDiscount);

    return response()->json([
        'message' => 'Áp mã thành công.',
        'product_discount' => Currency::toVndInt($productDiscount),
        'shipping_discount' => Currency::toVndInt($shippingDiscount),
        'total_discount' => Currency::toVndInt($totalDiscount),
        'final_shipping_fee' => Currency::toVndInt($finalShippingFee),
        'total' => Currency::toVndInt($total),
        'coupon_id' => $coupon->id,
        'coupon_type' => $coupon->type,
        'coupon_value' => $coupon->value,
        'free_shipping' => $coupon->free_shipping,
        'shipping_discount_amount' => $coupon->shipping_discount,
        'shipping_discount_percent' => $coupon->shipping_discount_percent,
    ]);
}

    /**
     * Place a new order
     */
    public function placeOrder(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'required|string|max:20',
                'address' => 'required|string',
                'payment_method' => 'required|in:cod,bank_transfer,online_payment',
                'subtotal' => 'required|numeric|min:0',
                'shipping_fee' => 'required|numeric|min:0',
                'tax' => 'required|numeric|min:0',
                'discount' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
                'coupon_code' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.product_name' => 'nullable|string|max:255',
            ]);

            $user = $request->user();

            // Check stock availability
            foreach ($request->items as $item) {
                if (isset($item['variant_id'])) {
                    $variant = ProductVariant::find($item['variant_id']);
                    if (!$variant || $variant->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Sản phẩm {$variant->product->name} chỉ còn {$variant->stock_quantity} trong kho."
                        ], 400);
                    }
                } else {
                    $product = Product::find($item['product_id']);
                    if (!$product || $product->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'success' => false,
                            'message' => "Sản phẩm {$product->name} chỉ còn {$product->stock_quantity} trong kho."
                        ], 400);
                    }
                }
            }

            // Generate order number
            $orderNumber = 'ORD' . date('Ymd') . str_pad(Order::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'notes' => $request->notes,
                'subtotal' => $request->subtotal,
                'shipping_fee' => $request->shipping_fee,
                'tax' => $request->tax,
                'discount' => $request->discount,
                'total' => $request->total,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($request->items as $item) {
                // Get product info for static storage
                $product = Product::find($item['product_id']);
                $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;
                
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'] ?? null,
                    'product_name' => $item['product_name'] ?? $product->name,
                    'variant_name' => $variant ? $variant->name : null,
                    'sku' => $variant ? $variant->sku : $product->sku,
                    'product_image' => $product->images->first() ? $product->images->first()->image_path : null,
                    'price' => $item['price'],
                    'discount_price' => 0, // Default discount price
                    'final_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                    'weight' => $product->weight ?? null,
                    'dimensions' => $product->dimensions ?? null,
                    'status' => 'pending'
                ]);

                // Update stock
                if (isset($item['variant_id'])) {
                    $variant = ProductVariant::find($item['variant_id']);
                    $variant->decrement('stock_quantity', $item['quantity']);
                } else {
                    $product = Product::find($item['product_id']);
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            // Apply coupon if provided
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)
                    ->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->first();

                if ($coupon) {
                    // Check if user already used this coupon
                    $usedCoupon = CouponUser::where('user_id', $user->id)
                        ->where('coupon_id', $coupon->id)
                        ->where('used', true)
                        ->first();

                    if (!$usedCoupon) {
                        CouponUser::create([
                            'user_id' => $user->id,
                            'coupon_id' => $coupon->id,
                            'used' => true,
                            'used_at' => now(),
                        ]);
                    }
                }
            }

            // Payment processing removed - VNPay integration disabled

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'status' => $order->status,
                    'payment_method' => $order->payment_method,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Order placement error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi đặt hàng: ' . $e->getMessage()
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
        'orders' => collect($orders->items())->map(function ($order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'subtotal' => Currency::toVndInt($order->subtotal),
                'discount' => Currency::toVndInt($order->discount),
                'total' => Currency::toVndInt($order->total),
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'created_at' => $order->created_at,
            ];
        }),
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
            'subtotal' => Currency::toVndInt($order->subtotal),
            'discount' => Currency::toVndInt($order->discount),
            'total' => Currency::toVndInt($order->total),
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
                    'price' => Currency::toVndInt($item->price),
                    'quantity' => $item->quantity,
                    'total' => Currency::toVndInt($item->total),
                    'product_image' => $item->product_image_url,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => $item->product->image ? asset('storage/' . $item->product->image) : null,
                    ] : null,
                    'variant' => $item->variant ? [
                        'id' => $item->variant->id,
                        'image' => $item->variant->image_url,
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

    // Chỉ cho phép hủy đơn hàng có trạng thái pending, confirmed, processing
    if (!in_array($order->status, ['pending', 'confirmed', 'processing'])) {
        return response()->json(['message' => 'Không thể hủy đơn hàng ở trạng thái này.'], 422);
    }

    DB::beginTransaction();

    try {
        // Cập nhật trạng thái đơn hàng
        $order->update(['status' => 'cancelled']);

        // Hoàn trả tồn kho
        foreach ($order->items as $item) {
            if ($item->product_variant_id) {
                // Hoàn trả tồn kho cho sản phẩm có variant
                $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                if ($variant) {
                    $variant->increment('stock_quantity', $item->quantity);
                }
            } else {
                // Hoàn trả tồn kho cho sản phẩm không có variant
                $product = \App\Models\Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock_quantity', $item->quantity);
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
        'paid_orders' => $user->orders()->where('payment_status', 'paid')->count(),
        'shipped_orders' => $user->orders()->where('status', 'shipped')->count(),
        'delivered_orders' => $user->orders()->where('status', 'delivered')->count(),
        'cancelled_orders' => $user->orders()->where('status', 'cancelled')->count(),
        'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total'),
    ];

    return response()->json(['stats' => $stats]);
}

/**
 * Lấy danh sách đơn hàng cho admin
 */
public function adminGetOrders(Request $request)
{
    $query = Order::with(['user', 'items.product', 'items.variant', 'coupon'])
        ->orderBy('created_at', 'desc');

    // Lọc theo trạng thái nếu có
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    // Lọc theo user nếu có
    if ($request->filled('user_id')) {
        $query->where('user_id', $request->user_id);
    }
    // Lọc theo ngày tạo nếu có
    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }
    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    $orders = $query->paginate($request->input('per_page', 20));

    $ordersData = collect($orders->items())->map(function ($order) {
        return [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'name' => $order->name,
            'phone' => $order->phone,
            'address' => $order->address,
            'email' => $order->email,
            'order_number' => $order->order_number,
            'subtotal' => Currency::toVndInt($order->subtotal),
            'shipping_fee' => Currency::toVndInt($order->shipping_fee),
            'tax' => Currency::toVndInt($order->tax),
            'discount' => Currency::toVndInt($order->discount),
            'total' => Currency::toVndInt($order->total),
            'coupon_id' => $order->coupon_id,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'payment_method' => $order->payment_method,
            'payment_transaction_id' => $order->payment_transaction_id,
            'tracking_number' => $order->tracking_number,
            'shipping_method' => $order->shipping_method,
            'shipped_at' => $order->shipped_at,
            'delivered_at' => $order->delivered_at,
            'notes' => $order->notes,
            'admin_notes' => $order->admin_notes,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
            'user' => $order->user,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'order_id' => $item->order_id,
                    'product_id' => $item->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'product_name' => $item->product_name,
                    'variant_name' => $item->variant_name,
                    'sku' => $item->sku,
                    'product_image' => $item->product_image_url, // trả về full URL
                    'product_image_url' => $item->product_image_url,
                    'price' => Currency::toVndInt($item->price),
                    'discount_price' => Currency::toVndInt($item->discount_price),
                    'final_price' => Currency::toVndInt($item->final_price),
                    'quantity' => $item->quantity,
                    'total' => Currency::toVndInt($item->total),
                    'weight' => $item->weight,
                    'dimensions' => $item->dimensions,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => $item->product->image ? asset('storage/' . $item->product->image) : null,
                        'image_url' => $item->product->image ? Storage::url($item->product->image) : null,
                    ] : null,
                    'variant' => $item->variant ? [
                        'id' => $item->variant->id,
                        'product_id' => $item->variant->product_id,
                        'sku' => $item->variant->sku,
                        'attribute_json' => $item->variant->attribute_json,
                        'price' => Currency::toVndInt($item->variant->price),
                        'discount' => Currency::toVndInt($item->variant->discount),
                        'stock_quantity' => $item->variant->stock_quantity,
                        'image' => $item->variant->image,
                        'image_url' => $item->variant->image_url,
                        'created_at' => $item->variant->created_at,
                        'updated_at' => $item->variant->updated_at,
                    ] : null,
                ];
            }),
            'coupon' => $order->coupon,
        ];
    });

    return response()->json([
        'orders' => $ordersData,
        'pagination' => [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ]
    ]);
}

/**
 * Lấy chi tiết đơn hàng cho admin
 */
public function adminGetOrderDetail($id)
{
    $order = Order::with(['user', 'items.product', 'items.variant', 'coupon'])->find($id);
    if (!$order) {
        return response()->json(['message' => 'Đơn hàng không tồn tại.'], 404);
    }
    return response()->json(['order' => $order]);
}

/**
 * Cập nhật trạng thái đơn hàng cho admin
 */
public function updateOrderStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        'tracking_number' => 'nullable|string',
        'note' => 'nullable|string',
    ]);
    
    $order = Order::find($id);
    if (!$order) {
        return response()->json(['message' => 'Đơn hàng không tồn tại.'], 404);
    }
    
    DB::beginTransaction();
    
    try {
        $oldStatus = $order->status;
        $newStatus = $request->status;
        
        // Cập nhật trạng thái đơn hàng
        $updateData = ['status' => $newStatus];
        
        // Cập nhật tracking number nếu có
        if ($request->tracking_number) {
            $updateData['tracking_number'] = $request->tracking_number;
        }
        
        // Cập nhật admin notes nếu có
        if ($request->note) {
            $updateData['admin_notes'] = $request->note;
        }
        
        // Cập nhật thời gian tương ứng với trạng thái
        switch ($newStatus) {
            case 'shipped':
                $updateData['shipped_at'] = now();
                break;
            case 'delivered':
                $updateData['delivered_at'] = now();
                break;
        }
        
        // Logic tự động thanh toán khi đơn hàng thành công
        if ($newStatus === 'delivered' && $order->payment_status === 'pending') {
            // Tự động thanh toán thành công cho đơn hàng COD
            if ($order->payment_method === 'cod' || $order->payment_method === 'bank_transfer') {
                $updateData['payment_status'] = 'paid';
                $updateData['paid_at'] = now();
                $updateData['payment_transaction_id'] = 'AUTO_' . $order->order_number . '_' . time();
                
                // Cập nhật rank user nếu có
                if ($order->user) {
                    $this->updateUserRank($order->user);
                }
                
                Log::info('Auto payment successful for delivered order', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_method' => $order->payment_method
                ]);
            }
        }
        
        // Logic tự động thanh toán cho các trạng thái khác
        if ($order->canAutoCompletePaymentOnStatusChange($newStatus)) {
            $updateData['payment_status'] = 'paid';
            $updateData['paid_at'] = now();
            $updateData['payment_transaction_id'] = 'AUTO_' . $order->order_number . '_' . time();
            
            // Cập nhật rank user nếu có
            if ($order->user) {
                $this->updateUserRank($order->user);
            }
            
            // Gửi thông báo tự động thanh toán thành công
            $this->sendAutoPaymentNotification($order, $newStatus);
            
            Log::info('Auto payment successful for status change', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'payment_method' => $order->payment_method
            ]);
        }
        
        // Cập nhật đơn hàng
        $order->update($updateData);
        
        // Log thay đổi trạng thái
        Log::info('Order status updated', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'payment_status' => $order->payment_status,
            'admin_id' => $request->user() ? $request->user()->id : null
        ]);
        
        DB::commit();
        
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái thành công.',
            'data' => [
                'order' => $order->fresh(),
                'status_changed' => $oldStatus !== $newStatus,
                'auto_payment' => isset($updateData['payment_status']) && $updateData['payment_status'] === 'paid'
            ]
        ]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Order status update error', [
            'order_id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Thống kê đơn hàng cho admin
 */
public function adminGetOrderStats(Request $request)
{
    $stats = [
        'total_orders' => Order::count(),
        'pending_orders' => Order::where('status', 'pending')->count(),
        'paid_orders' => Order::where('payment_status', 'paid')->count(),
        'shipped_orders' => Order::where('status', 'shipped')->count(),
        'delivered_orders' => Order::where('status', 'delivered')->count(),
        'cancelled_orders' => Order::where('status', 'cancelled')->count(),
        'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
    ];
    return response()->json(['stats' => $stats]);
}

    /**
     * Gửi thông báo khi tự động thanh toán thành công
     */
    private function sendAutoPaymentNotification(Order $order, string $newStatus): void
    {
        try {
            // Gửi email thông báo cho khách hàng
            if ($order->user && $order->user->email) {
                // TODO: Implement email notification
                Log::info('Auto payment email notification sent', [
                    'order_id' => $order->id,
                    'user_email' => $order->user->email,
                    'status' => $newStatus
                ]);
            }
            
            // Gửi thông báo push hoặc SMS nếu có
            if ($order->user && $order->phone) {
                // TODO: Implement SMS notification
                Log::info('Auto payment SMS notification sent', [
                    'order_id' => $order->id,
                    'user_phone' => $order->phone,
                    'status' => $newStatus
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send auto payment notification', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
