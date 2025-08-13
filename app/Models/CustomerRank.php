<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRank extends Model
{
    protected $fillable = ['name', 'level', 'min_total_spent'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
    public function updateUserRank(User $user)
{
    $totalSpent = $user->orders()
        ->where('status', 'paid')
        ->sum('total');

    $rank = CustomerRank::where('min_total_spent', '<=', $totalSpent)
        ->orderByDesc('min_total_spent')
        ->first();

    if ($rank && $user->customer_rank_id !== $rank->id) {
        $user->update(['customer_rank_id' => $rank->id]);
    }
}
}
