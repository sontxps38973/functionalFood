<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'start_time', 'end_time', 'status', 'banner_image',
        'discount_type', 'discount_value', 'is_featured', 'sort_order'
    ];

    public function eventProducts()
    {
        return $this->hasMany(EventProduct::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
    public function scopeRunning($query)
    {
        return $query->where('status', 'active')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now());
    }
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }
    public function scopeEnded($query)
    {
        return $query->where('end_time', '<', now());
    }
}
