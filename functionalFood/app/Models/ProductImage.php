<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = ['product_id', 'image_path', 'alt_text', 'is_main'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}