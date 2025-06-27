<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'description',
        'type',
        'value',
        'max_discount',
        'scope',
        'target_ids',
        'min_order_value',
        'usage_limit',
        'used_count',
        'only_once_per_user',
        'first_time_only',
        'allowed_rank_ids',
        'start_at',
        'end_at',
        'time_rules',
        'is_active',
        'allowed_payment_methods'
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
