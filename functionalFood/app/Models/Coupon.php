<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'max_discount',
        'min_order_value', 'usage_limit', 'used_count',
        'only_once_per_user', 'start_at', 'end_at', 'is_active'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'coupon_user')
                    ->withPivot('order_id', 'usage_count', 'used_at')
                    ->withTimestamps();
    }
}
