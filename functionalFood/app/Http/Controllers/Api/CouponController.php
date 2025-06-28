<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * Lấy danh sách mã giảm giá
     */
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Filter theo trạng thái
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Filter theo scope
        if ($request->has('scope')) {
            $query->where('scope', $request->scope);
        }

        // Filter theo type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Search theo code hoặc description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter theo thời gian hiệu lực
        if ($request->has('valid_only')) {
            $now = now();
            $query->where('is_active', true)
                  ->where(function ($q) use ($now) {
                      $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
                  })
                  ->where(function ($q) use ($now) {
                      $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
                  });
        }

        $coupons = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'coupons' => $coupons->items(),
            'pagination' => [
                'current_page' => $coupons->currentPage(),
                'last_page' => $coupons->lastPage(),
                'per_page' => $coupons->perPage(),
                'total' => $coupons->total(),
            ]
        ]);
    }

    /**
     * Lấy chi tiết mã giảm giá
     */
    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);

        return response()->json([
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'max_discount' => $coupon->max_discount,
                'scope' => $coupon->scope,
                'target_ids' => $coupon->target_ids,
                'free_shipping' => $coupon->free_shipping,
                'shipping_discount' => $coupon->shipping_discount,
                'shipping_discount_percent' => $coupon->shipping_discount_percent,
                'min_order_value' => $coupon->min_order_value,
                'max_order_value' => $coupon->max_order_value,
                'usage_limit' => $coupon->usage_limit,
                'used_count' => $coupon->used_count,
                'only_once_per_user' => $coupon->only_once_per_user,
                'first_time_only' => $coupon->first_time_only,
                'allowed_rank_ids' => $coupon->allowed_rank_ids,
                'start_at' => $coupon->start_at,
                'end_at' => $coupon->end_at,
                'time_rules' => $coupon->time_rules,
                'is_active' => $coupon->is_active,
                'allowed_payment_methods' => $coupon->allowed_payment_methods,
                'allowed_regions' => $coupon->allowed_regions,
                'created_at' => $coupon->created_at,
                'updated_at' => $coupon->updated_at,
                'usage_rate' => $coupon->usage_limit ? round(($coupon->used_count / $coupon->usage_limit) * 100, 2) : null,
            ]
        ]);
    }

    /**
     * Tạo mã giảm giá mới
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:coupons,code|max:50',
            'description' => 'nullable|string|max:500',
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'scope' => ['required', Rule::in(['order', 'product', 'category', 'shipping'])],
            'target_ids' => 'nullable|array',
            'target_ids.*' => 'integer',
            
            // Giảm giá vận chuyển
            'free_shipping' => 'boolean',
            'shipping_discount' => 'nullable|numeric|min:0',
            'shipping_discount_percent' => 'nullable|numeric|min:0|max:100',
            
            // Điều kiện sử dụng
            'min_order_value' => 'nullable|numeric|min:0',
            'max_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'only_once_per_user' => 'boolean',
            'first_time_only' => 'boolean',
            'allowed_rank_ids' => 'nullable|array',
            'allowed_rank_ids.*' => 'integer',
            
            // Thời gian
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'time_rules' => 'nullable|array',
            
            // Cấu hình khác
            'allowed_payment_methods' => 'nullable|array',
            'allowed_payment_methods.*' => Rule::in(['cod', 'bank_transfer', 'online_payment']),
            'allowed_regions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Validation logic
        if ($data['scope'] === 'shipping' && !$data['free_shipping'] && !$data['shipping_discount'] && !$data['shipping_discount_percent']) {
            return response()->json(['message' => 'Coupon vận chuyển phải có ít nhất một loại giảm giá vận chuyển.'], 422);
        }

        if ($data['type'] === 'percent' && $data['value'] > 100) {
            return response()->json(['message' => 'Giá trị phần trăm không được vượt quá 100%.'], 422);
        }

        if (isset($data['max_order_value']) && isset($data['min_order_value']) && $data['max_order_value'] <= $data['min_order_value']) {
            return response()->json(['message' => 'Giá trị đơn hàng tối đa phải lớn hơn giá trị tối thiểu.'], 422);
        }

        try {
            $coupon = Coupon::create($data);

            Log::info('Coupon created', [
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'created_by' => $request->user()?->id
            ]);

            return response()->json([
                'message' => 'Tạo mã giảm giá thành công.',
                'coupon' => $coupon
            ], 201);

        } catch (\Exception $e) {
            Log::error('Coupon creation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return response()->json([
                'message' => 'Lỗi khi tạo mã giảm giá.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cập nhật mã giảm giá
     */
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'code' => ['sometimes', 'string', Rule::unique('coupons')->ignore($id), 'max:50'],
            'description' => 'nullable|string|max:500',
            'type' => ['sometimes', Rule::in(['percent', 'fixed'])],
            'value' => 'sometimes|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'scope' => ['sometimes', Rule::in(['order', 'product', 'category', 'shipping'])],
            'target_ids' => 'nullable|array',
            'target_ids.*' => 'integer',
            
            // Giảm giá vận chuyển
            'free_shipping' => 'boolean',
            'shipping_discount' => 'nullable|numeric|min:0',
            'shipping_discount_percent' => 'nullable|numeric|min:0|max:100',
            
            // Điều kiện sử dụng
            'min_order_value' => 'nullable|numeric|min:0',
            'max_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'only_once_per_user' => 'boolean',
            'first_time_only' => 'boolean',
            'allowed_rank_ids' => 'nullable|array',
            'allowed_rank_ids.*' => 'integer',
            
            // Thời gian
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'time_rules' => 'nullable|array',
            
            // Cấu hình khác
            'allowed_payment_methods' => 'nullable|array',
            'allowed_payment_methods.*' => Rule::in(['cod', 'bank_transfer', 'online_payment']),
            'allowed_regions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Validation logic
        if (isset($data['scope']) && $data['scope'] === 'shipping' && 
            !($data['free_shipping'] ?? $coupon->free_shipping) && 
            !($data['shipping_discount'] ?? $coupon->shipping_discount) && 
            !($data['shipping_discount_percent'] ?? $coupon->shipping_discount_percent)) {
            return response()->json(['message' => 'Coupon vận chuyển phải có ít nhất một loại giảm giá vận chuyển.'], 422);
        }

        if (isset($data['type']) && $data['type'] === 'percent' && isset($data['value']) && $data['value'] > 100) {
            return response()->json(['message' => 'Giá trị phần trăm không được vượt quá 100%.'], 422);
        }

        if (isset($data['max_order_value']) && isset($data['min_order_value']) && $data['max_order_value'] <= $data['min_order_value']) {
            return response()->json(['message' => 'Giá trị đơn hàng tối đa phải lớn hơn giá trị tối thiểu.'], 422);
        }

        try {
            $coupon->update($data);

            Log::info('Coupon updated', [
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'updated_by' => $request->user()?->id
            ]);

            return response()->json([
                'message' => 'Cập nhật mã giảm giá thành công.',
                'coupon' => $coupon
            ]);

        } catch (\Exception $e) {
            Log::error('Coupon update failed', [
                'coupon_id' => $id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return response()->json([
                'message' => 'Lỗi khi cập nhật mã giảm giá.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Xóa mã giảm giá
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);

        // Kiểm tra xem coupon có đang được sử dụng không
        if ($coupon->used_count > 0) {
            return response()->json([
                'message' => 'Không thể xóa mã giảm giá đã được sử dụng.',
                'used_count' => $coupon->used_count
            ], 422);
        }

        try {
            $coupon->delete();

            Log::info('Coupon deleted', [
                'coupon_id' => $id,
                'code' => $coupon->code
            ]);

            return response()->json(['message' => 'Xóa mã giảm giá thành công.']);

        } catch (\Exception $e) {
            Log::error('Coupon deletion failed', [
                'coupon_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Lỗi khi xóa mã giảm giá.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Kích hoạt/vô hiệu hóa mã giảm giá
     */
    public function toggleStatus($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'message' => $coupon->is_active ? 'Kích hoạt mã giảm giá thành công.' : 'Vô hiệu hóa mã giảm giá thành công.',
            'is_active' => $coupon->is_active
        ]);
    }

    /**
     * Lấy thống kê sử dụng mã giảm giá
     */
    public function getStats($id)
    {
        $coupon = Coupon::findOrFail($id);

        $stats = [
            'total_usage' => $coupon->used_count,
            'usage_limit' => $coupon->usage_limit,
            'usage_rate' => $coupon->usage_limit ? round(($coupon->used_count / $coupon->usage_limit) * 100, 2) : null,
            'remaining_usage' => $coupon->usage_limit ? max(0, $coupon->usage_limit - $coupon->used_count) : null,
            'is_active' => $coupon->is_active,
            'is_expired' => $coupon->end_at && $coupon->end_at < now(),
            'is_not_started' => $coupon->start_at && $coupon->start_at > now(),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Lấy danh sách mã giảm giá có hiệu lực cho user
     */
    public function getValidCoupons(Request $request)
    {
        $user = $request->user();
        $subtotal = $request->get('subtotal', 0);
        $shippingFee = $request->get('shipping_fee', 0);
        $paymentMethod = $request->get('payment_method');

        $query = Coupon::valid();

        // Filter theo điều kiện user
        $query->where(function ($q) use ($user) {
            $q->whereNull('allowed_rank_ids')
              ->orWhereJsonContains('allowed_rank_ids', $user->customer_rank_id);
        });

        // Filter theo giá trị đơn hàng
        $orderValue = $subtotal + $shippingFee;
        $query->where(function ($q) use ($orderValue) {
            $q->whereNull('min_order_value')
              ->orWhere('min_order_value', '<=', $orderValue);
        });

        $query->where(function ($q) use ($orderValue) {
            $q->whereNull('max_order_value')
              ->orWhere('max_order_value', '>=', $orderValue);
        });

        // Filter theo phương thức thanh toán
        if ($paymentMethod) {
            $query->where(function ($q) use ($paymentMethod) {
                $q->whereNull('allowed_payment_methods')
                  ->orWhereJsonContains('allowed_payment_methods', $paymentMethod);
            });
        }

        // Loại bỏ coupon đã sử dụng (nếu only_once_per_user)
        $usedCouponIds = $user->coupons()->where('only_once_per_user', true)->pluck('coupons.id');
        if ($usedCouponIds->isNotEmpty()) {
            $query->whereNotIn('id', $usedCouponIds);
        }

        // Loại bỏ coupon cho đơn hàng đầu tiên (nếu user đã có đơn hàng)
        if ($user->orders()->exists()) {
            $query->where('first_time_only', false);
        }

        $coupons = $query->get();

        return response()->json([
            'coupons' => $coupons->map(function ($coupon) use ($subtotal, $shippingFee) {
                $productDiscount = 0;
                $shippingDiscount = 0;

                // Tính discount sản phẩm
                if ($coupon->type === 'percent') {
                    $productDiscount = $subtotal * ($coupon->value / 100);
                    if ($coupon->max_discount) {
                        $productDiscount = min($productDiscount, $coupon->max_discount);
                    }
                } elseif ($coupon->type === 'fixed') {
                    $productDiscount = $coupon->value;
                }

                // Tính discount vận chuyển
                if ($coupon->isShippingCoupon()) {
                    $shippingDiscount = $coupon->calculateShippingDiscount($shippingFee);
                }

                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'description' => $coupon->description,
                    'type' => $coupon->type,
                    'scope' => $coupon->scope,
                    'product_discount' => $productDiscount,
                    'shipping_discount' => $shippingDiscount,
                    'total_discount' => $productDiscount + $shippingDiscount,
                    'free_shipping' => $coupon->free_shipping,
                ];
            })
        ]);
    }
}
