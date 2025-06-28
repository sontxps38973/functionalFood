<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 
        'product_id', 
        'product_variant_id',
        'product_name', 
        'variant_name', 
        'sku',
        'product_image',
        'price',
        'discount_price',
        'final_price',
        'quantity', 
        'total',
        'weight',
        'dimensions',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'total' => 'decimal:2',
        'weight' => 'decimal:3',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    // Methods
    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã gửi',
            'delivered' => 'Đã giao',
            'returned' => 'Đã trả hàng',
            'refunded' => 'Đã hoàn tiền'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function canBeReturned(): bool
    {
        return in_array($this->status, ['delivered']) && 
               $this->order->delivered_at && 
               $this->order->delivered_at->diffInDays(now()) <= 7; // 7 ngày từ khi giao hàng
    }
}
