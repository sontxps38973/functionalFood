<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ReviewReport;

class ProductReview extends Model
{
    protected $fillable = [
        'user_id', 'product_id', 'rating', 'comment', 'status', 'flagged', 'admin_note'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class, 'review_id');
    }
}
