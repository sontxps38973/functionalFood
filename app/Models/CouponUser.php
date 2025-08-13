<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUser extends Model
{
    protected $table = 'coupon_user';

    protected $fillable = [
        'coupon_id',
        'user_id',
        'order_id',
        'usage_count',
        'used_at',
    ];

    protected $dates = ['used_at'];

    // ✅ Quan hệ với coupon
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    // ✅ Quan hệ với user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ✅ Quan hệ với đơn hàng (có thể null)
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
