<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserCoupon;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class UserCouponController extends Controller
{
    // Lưu/tặng coupon cho user
    public function saveCoupon(Request $request, $coupon_id)
    {
        $user = Auth::user();
        $exists = UserCoupon::where('user_id', $user->id)->where('coupon_id', $coupon_id)->first();
        if ($exists) {
            return response()->json(['message' => 'Coupon already saved'], 409);
        }
        $userCoupon = UserCoupon::create([
            'user_id' => $user->id,
            'coupon_id' => $coupon_id,
            'received_at' => now(),
            'is_used' => false,
        ]);
        return response()->json(['message' => 'Coupon saved', 'data' => $userCoupon], 201);
    }

    // Lấy danh sách coupon user đang có (chưa dùng)
    public function listCoupons(Request $request)
    {
        $user = Auth::user();
        $coupons = UserCoupon::with('coupon')
            ->where('user_id', $user->id)
            ->where('is_used', false)
            ->get();
        return response()->json(['data' => $coupons]);
    }

    // Đánh dấu đã dùng coupon
    public function useCoupon(Request $request, $user_coupon_id)
    {
        $user = Auth::user();
        $userCoupon = UserCoupon::where('id', $user_coupon_id)
            ->where('user_id', $user->id)
            ->where('is_used', false)
            ->firstOrFail();
        $userCoupon->is_used = true;
        $userCoupon->used_at = now();
        $userCoupon->save();
        // Có thể thêm logic tạo record ở coupon_user nếu cần thống kê
        return response()->json(['message' => 'Coupon used', 'data' => $userCoupon]);
    }
} 