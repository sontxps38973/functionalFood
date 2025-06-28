<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'banner_image' => $this->banner_image,
            'discount_type' => $this->discount_type,
            'discount_value' => $this->discount_value,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
            'products' => EventProductResource::collection($this->whenLoaded('eventProducts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
