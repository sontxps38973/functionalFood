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
        'free_shipping',
        'shipping_discount',
        'shipping_discount_percent',
        'min_order_value',
        'max_order_value',
        'usage_limit',
        'used_count',
        'only_once_per_user',
        'first_time_only',
        'allowed_rank_ids',
        'start_at',
        'end_at',
        'time_rules',
        'is_active',
        'allowed_payment_methods',
        'allowed_regions'
    ];

    protected $casts = [
        'free_shipping' => 'boolean',
        'shipping_discount' => 'decimal:2',
        'shipping_discount_percent' => 'decimal:2',
        'min_order_value' => 'decimal:2',
        'max_order_value' => 'decimal:2',
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'time_rules' => 'array',
        'target_ids' => 'array',
        'allowed_rank_ids' => 'array',
        'allowed_payment_methods' => 'array',
        'allowed_regions' => 'array',
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', $now);
            });
    }

    // Methods
    public function isShippingCoupon(): bool
    {
        return $this->scope === 'shipping' || $this->free_shipping || 
               $this->shipping_discount > 0 || $this->shipping_discount_percent > 0;
    }

    public function calculateShippingDiscount($shippingFee): float
    {
        if ($this->free_shipping) {
            return $shippingFee;
        }

        if ($this->shipping_discount > 0) {
            return min($this->shipping_discount, $shippingFee);
        }

        if ($this->shipping_discount_percent > 0) {
            return $shippingFee * ($this->shipping_discount_percent / 100);
        }

        return 0;
    }

    public function canBeUsedByUser($user): bool
    {
        // Kiểm tra hạng thành viên
        if ($this->allowed_rank_ids && !in_array($user->customer_rank_id, $this->allowed_rank_ids)) {
            return false;
        }

        // Kiểm tra đơn hàng đầu tiên
        if ($this->first_time_only && $user->orders()->exists()) {
            return false;
        }

        // Kiểm tra sử dụng một lần
        if ($this->only_once_per_user && $this->users()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Kiểm tra giới hạn sử dụng
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }
}
