<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    //
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'address',
        'is_default',
    ];
}
