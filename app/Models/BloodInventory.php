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

    public function getTotalUnitsAttribute(): int
    {
        return (int) $this->available_units + (int) $this->reserved_units;
    }

    public function getStockGapAttribute(): int
    {
        return max(0, (int) $this->minimum_threshold - (int) $this->available_units);
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->available_units <= 0) {
            return 'critical';
        }

        if ($this->available_units < $this->minimum_threshold) {
            return 'low';
        }

        return 'healthy';
    }
}
