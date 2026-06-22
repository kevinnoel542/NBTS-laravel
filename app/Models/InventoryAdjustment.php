<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_center_id',
        'blood_unit_id',
        'adjusted_by',
        'blood_group',
        'quantity_delta',
        'reason',
        'notes',
    ];

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function adjuster()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
