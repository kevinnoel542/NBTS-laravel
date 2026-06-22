<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowStockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_center_id',
        'blood_group',
        'available_units',
        'minimum_threshold',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }
}
