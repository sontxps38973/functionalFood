<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'sku', 'attribute_json', 'price', 'stock_quantity', 'image'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

