<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'email',
        'order_number',
        'subtotal',
        'shipping_fee',
        'tax',
        'discount',
        'total',
        'coupon_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_transaction_id',
        'payment_reference',
        'paid_at',
        'payment_error',
        'tracking_number',
        'shipping_method',
        'shipped_at',
        'delivered_at',
        'notes',
        'admin_notes'
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $lastOrder = self::whereDate('created_at', today())->latest()->first();
        
        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    public function canBeShipped(): bool
    {
        return in_array($this->status, ['confirmed', 'processing']) && $this->payment_status === 'paid';
    }
    
    /**
     * Kiểm tra xem đơn hàng có thể tự động thanh toán không
     */
    public function canAutoCompletePayment(): bool
    {
        return $this->payment_status === 'pending' && 
               in_array($this->payment_method, ['cod', 'bank_transfer']);
    }
    
    /**
     * Kiểm tra xem đơn hàng có phải là COD không
     */
    public function isCodOrder(): bool
    {
        return $this->payment_method === 'cod';
    }
    
    /**
     * Kiểm tra xem đơn hàng có phải là chuyển khoản không
     */
    public function isBankTransferOrder(): bool
    {
        return $this->payment_method === 'bank_transfer';
    }
    
    /**
     * Kiểm tra xem đơn hàng có thể tự động thanh toán khi thay đổi trạng thái không
     */
    public function canAutoCompletePaymentOnStatusChange(string $newStatus): bool
    {
        if (!$this->canAutoCompletePayment()) {
            return false;
        }
        
        // Tự động thanh toán khi giao hàng thành công
        if ($newStatus === 'delivered') {
            return true;
        }
        
        // Tự động thanh toán khi xác nhận đơn hàng
        if ($newStatus === 'confirmed' && $this->status === 'pending') {
            return true;
        }
        
        // Tự động thanh toán khi bắt đầu xử lý
        if ($newStatus === 'processing' && in_array($this->status, ['pending', 'confirmed'])) {
            return true;
        }
        
        // Tự động thanh toán khi gửi hàng
        if ($newStatus === 'shipped' && in_array($this->status, ['confirmed', 'processing'])) {
            return true;
        }
        
        return false;
    }

    public function getStatusTextAttribute(): string
    {
        $statuses = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã gửi hàng',
            'delivered' => 'Đã giao hàng',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền'
        ];
        
        return $statuses[$this->status] ?? $this->status;
    }

    public function getPaymentStatusTextAttribute(): string
    {
        $statuses = [
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            'refunded' => 'Đã hoàn tiền'
        ];
        
        return $statuses[$this->payment_status] ?? $this->payment_status;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
        });
    }
}
