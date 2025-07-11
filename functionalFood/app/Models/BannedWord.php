<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BannedWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'word', 'description', 'is_active'
    ];
} 