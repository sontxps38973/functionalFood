<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductView extends Model
{
    public $timestamps = false;
    protected $fillable = ['product_id', 'user_id', 'ip_address', 'viewed_at'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

