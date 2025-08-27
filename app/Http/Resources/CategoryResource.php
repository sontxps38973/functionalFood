<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        // Kiểm tra resource có tồn tại không
        if (!$this->resource) {
            return [
                'id' => null,
                'name' => null,
                'slug' => null,
                'error' => 'Resource is null'
            ];
        }
        
        // Kiểm tra các thuộc tính cần thiết
        if (!$this->id || !$this->name || !$this->slug) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug,
                'error' => 'Missing required fields'
            ];
        }
        
        return [
            'id'        => (int) $this->id,
            'name'      => (string) $this->name,
            'slug'      => (string) $this->slug,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,
        ];
    }
}
