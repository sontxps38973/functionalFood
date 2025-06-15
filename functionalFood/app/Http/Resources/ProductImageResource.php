<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'image_path' => $this->image_path,
            'alt_text' => $this->alt_text,
            'is_main' => $this->is_main,
        ];
    }
}
