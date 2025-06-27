<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    //  Tạo mã giảm giá
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'description' => 'nullable|string',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'scope' => 'required|in:order,product,category',
            'target_ids' => 'nullable|array',
            'target_ids.*' => 'integer',
            'min_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'only_once_per_user' => 'boolean',
            'first_time_only' => 'boolean',
            'allowed_rank_ids' => 'nullable|array',
            'allowed_rank_ids.*' => 'integer',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'time_rules' => 'nullable|array',
            'allowed_payment_methods' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Lưu JSON
        $data['target_ids'] = isset($data['target_ids']) ? json_encode($data['target_ids']) : null;
        $data['allowed_rank_ids'] = isset($data['allowed_rank_ids']) ? json_encode($data['allowed_rank_ids']) : null;
        $data['time_rules'] = isset($data['time_rules']) ? json_encode($data['time_rules']) : null;
        $data['allowed_payment_methods'] = isset($data['allowed_payment_methods']) ? json_encode($data['allowed_payment_methods']) : null;

        $coupon = Coupon::create($data);

        return response()->json(['message' => 'Tạo mã giảm giá thành công.', 'data' => $coupon], 201);
    }

    //  Sửa mã giảm giá
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'description' => 'nullable|string',
            'type' => 'in:percent,fixed',
            'value' => 'numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'scope' => 'in:order,product,category',
            'target_ids' => 'nullable|array',
            'target_ids.*' => 'integer',
            'min_order_value' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'only_once_per_user' => 'boolean',
            'first_time_only' => 'boolean',
            'allowed_rank_ids' => 'nullable|array',
            'allowed_rank_ids.*' => 'integer',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'time_rules' => 'nullable|array',
            'allowed_payment_methods' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Cập nhật JSON
        $data['target_ids'] = isset($data['target_ids']) ? json_encode($data['target_ids']) : $coupon->target_ids;
        $data['allowed_rank_ids'] = isset($data['allowed_rank_ids']) ? json_encode($data['allowed_rank_ids']) : $coupon->allowed_rank_ids;
        $data['time_rules'] = isset($data['time_rules']) ? json_encode($data['time_rules']) : $coupon->time_rules;
        $data['allowed_payment_methods'] = isset($data['allowed_payment_methods']) ? json_encode($data['allowed_payment_methods']) : $coupon->allowed_payment_methods;

        $coupon->update($data);

        return response()->json(['message' => 'Cập nhật mã giảm giá thành công.', 'data' => $coupon]);
    }

    //  Xoá mã giảm giá
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['message' => 'Xoá mã giảm giá thành công.']);
    }

    //  Danh sách
    public function index()
    {
        return Coupon::latest()->paginate(10);
    }
}
