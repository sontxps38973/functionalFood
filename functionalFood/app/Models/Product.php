<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'status',
        'product_type', 'price','discount', 'stock_quantity', 'image'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
        public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }
    public function relatedProducts()
{
    return $this->hasMany(Product::class, 'category_id', 'category_id')
                ->where('id', '!=', $this->id)
                ->limit(8);
}
}