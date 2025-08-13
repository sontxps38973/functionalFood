<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'sku', 'attribute_json',
        'price', 'discount', 'stock_quantity', 'image'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Nếu bạn muốn hiển thị tên biến thể từ JSON
    public function getAttributeNameTextAttribute()
    {
        $attributes = json_decode($this->attribute_json, true);
        return collect($attributes)->map(fn($v, $k) => "$k: $v")->implode(', ');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
