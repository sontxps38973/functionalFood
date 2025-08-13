<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\Currency;

class ProductVariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'attribute_json' => json_decode($this->attribute_json, true),
            'price' => Currency::toVndInt($this->price),
            'stock_quantity' => $this->stock_quantity,
            'image' => $this->image_url,
        ];
    }
}
