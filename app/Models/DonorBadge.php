<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorBadge extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'badge_id', 'awarded_at'];

    protected function casts(): array
    {
        return ['awarded_at' => 'datetime'];
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
