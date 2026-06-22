<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodInventory extends Model
{
    use HasFactory;

    protected $table = 'blood_inventory';

    protected $fillable = [
        'blood_center_id',
        'blood_group',
        'available_units',
        'reserved_units',
        'minimum_threshold',
    ];

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }
}
