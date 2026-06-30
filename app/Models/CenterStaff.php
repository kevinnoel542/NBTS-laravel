<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CenterStaff extends Model
{
    use HasFactory;

    protected $table = 'center_staff';

    protected $fillable = [
        'user_id',
        'blood_center_id',
        'position',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function getPositionLabelAttribute(): string
    {
        return str($this->position)->replace('_', ' ')->title()->toString();
    }
}
