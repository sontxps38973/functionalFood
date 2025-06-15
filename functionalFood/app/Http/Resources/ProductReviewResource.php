<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
