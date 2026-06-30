<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorReward extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'reward_id', 'status', 'awarded_at', 'redeemed_at'];

    protected function casts(): array
    {
        return [
            'awarded_at' => 'datetime',
            'redeemed_at' => 'datetime',
        ];
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
