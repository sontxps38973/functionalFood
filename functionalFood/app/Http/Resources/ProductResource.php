<?php

// app/Http/Resources/ProductResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
   public function toArray($request)
{
    $variant = null;

    if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
        $variant = $this->variants->first();
    }

    $imageUrl = $variant ? $variant->image_url : ($this->image ? asset('storage/' . $this->image) : null);

    return [
        'id'              => $this->id,
        'category'        => new CategoryResource($this->whenLoaded('category')),
        'name'            => $this->name,
        'slug'            => $this->slug,
        'description'     => $this->description,
        'status'          => $this->status,
        'product_type'    => $this->product_type,

        'price'           => $variant ? $variant->price : $this->price,
        'discount_price'  => $variant ? $variant->discount_price : $this->discount,
        'stock_quantity'  => $variant ? $variant->stock_quantity : $this->stock_quantity,
        'image'           => $imageUrl,

        'images'          => ProductImageResource::collection($this->whenLoaded('images')),
        'variants'        => ProductVariantResource::collection($this->whenLoaded('variants')),
        'reviews'         => ProductReviewResource::collection($this->whenLoaded('reviews')),
        'related_products' => ProductResource::collection($this->whenLoaded('relatedProducts')),

        'average_rating'  => round($this->reviews->avg('rating'), 1),
        'views_count'     => $this->views()->count(),
    ];  
}

}

