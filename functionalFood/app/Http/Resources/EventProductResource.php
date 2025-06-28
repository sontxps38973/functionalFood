<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'product_id' => $this->product_id,
            'event_price' => $this->event_price,
            'original_price' => $this->original_price,
            'discount_price' => $this->discount_price,
            'quantity_limit' => $this->quantity_limit,
            'sold_quantity' => $this->sold_quantity,
            'status' => $this->status,
            'sort_order' => $this->sort_order,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
