<?php

// app/Http/Resources/ProductResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'category'      => new CategoryResource($this->whenLoaded('category')),
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'status'        => $this->status,
            'product_type'  => $this->product_type,
            'price'         => $this->price,
            'stock_quantity'=> $this->stock_quantity,
            'image'         => $this->image,
            'images'        => ProductImageResource::collection($this->whenLoaded('images')),
            'variants'      => ProductVariantResource::collection($this->whenLoaded('variants')),
            'reviews'       => ProductReviewResource::collection($this->whenLoaded('reviews')),
            'average_rating'=> $this->reviews()->avg('rating'),
            'views_count'   => $this->views()->count(),
        ];
    }
}

