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
        $now = now();
        
        $userCoupons = UserCoupon::with(['coupon' => function($query) use ($now) {
                $query->where('is_active', true)
                      ->where(function($q) use ($now) {
                          $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
                      })
                      ->where(function($q) use ($now) {
                          $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
                      });
            }])
            ->where('user_id', $user->id)
            ->where('is_used', false)
            ->get()
            ->filter(function($userCoupon) {
                return $userCoupon->coupon !== null; // Chỉ lấy những coupon còn hợp lệ
            })
            ->map(function($userCoupon) {
                return [
                    'id' => $userCoupon->id,
                    'coupon_id' => $userCoupon->coupon_id,
                    'received_at' => $userCoupon->received_at,
                    'is_used' => $userCoupon->is_used,
                    'used_at' => $userCoupon->used_at,
                    'coupon' => [
                        'id' => $userCoupon->coupon->id,
                        'code' => $userCoupon->coupon->code,
                        'description' => $userCoupon->coupon->description,
                        'type' => $userCoupon->coupon->type,
                        'value' => $userCoupon->coupon->value,
                        'max_discount' => $userCoupon->coupon->max_discount,
                        'scope' => $userCoupon->coupon->scope,
                        'min_order_value' => $userCoupon->coupon->min_order_value,
                        'max_order_value' => $userCoupon->coupon->max_order_value,
                        'free_shipping' => $userCoupon->coupon->free_shipping,
                        'shipping_discount' => $userCoupon->coupon->shipping_discount,
                        'shipping_discount_percent' => $userCoupon->coupon->shipping_discount_percent,
                        'start_at' => $userCoupon->coupon->start_at,
                        'end_at' => $userCoupon->coupon->end_at,
                        'is_active' => $userCoupon->coupon->is_active,
                    ]
                ];
            });
            
        return response()->json(['data' => $userCoupons->values()]);
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