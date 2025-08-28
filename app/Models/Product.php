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

    public function eventProducts()
    {
        return $this->hasMany(EventProduct::class);
    }

    public function activeEventProducts()
    {
        return $this->hasMany(EventProduct::class)
            ->whereHas('event', function($query) {
                $query->where('status', 'active')
                      ->where('start_time', '<=', now())
                      ->where('end_time', '>=', now());
            });
    }

    public function getBestEventPrice()
    {
        $activeEventProduct = $this->activeEventProducts()
            ->join('events', 'event_products.event_id', '=', 'events.id')
            ->select('event_products.*', 'events.name as event_name', 'events.description as event_description', 
                    'events.start_time', 'events.end_time', 'events.discount_type', 'events.discount_value', 'events.banner_image')
            ->orderBy('event_products.discount_price', 'desc') // Ưu tiên discount cao nhất
            ->orderBy('events.end_time', 'asc')  // Nếu discount bằng nhau, ưu tiên sự kiện sắp kết thúc
            ->first();

        if (!$activeEventProduct) {
            return null;
        }

        return [
            'event_price' => $activeEventProduct->event_price,
            'original_price' => $activeEventProduct->original_price,
            'discount_price' => $activeEventProduct->discount_price,
            'event_info' => [
                'id' => $activeEventProduct->event_id,
                'name' => $activeEventProduct->event_name,
                'description' => $activeEventProduct->event_description,
                'start_time' => $activeEventProduct->start_time,
                'end_time' => $activeEventProduct->end_time,
                'discount_type' => $activeEventProduct->discount_type,
                'discount_value' => $activeEventProduct->discount_value,
                'banner_image' => $activeEventProduct->banner_image,
            ]
        ];
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}