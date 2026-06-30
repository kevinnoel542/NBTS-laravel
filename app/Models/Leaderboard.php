<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'period', 'donation_count', 'rank'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return str($this->period)->replace('_', ' ')->title()->toString();
    }
}
