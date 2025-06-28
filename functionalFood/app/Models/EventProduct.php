<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id', 'product_id', 'event_price', 'original_price', 'discount_price',
        'quantity_limit', 'sold_quantity', 'status', 'sort_order'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
