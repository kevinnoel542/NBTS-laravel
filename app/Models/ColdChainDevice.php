<?php

namespace App\Models;

use App\ColdChainDeviceStatus;
use App\ColdChainDeviceType;
use Database\Factories\ColdChainDeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'blood_center_id',
    'device_code',
    'name',
    'device_type',
    'status',
    'location',
    'capacity_units',
    'responsible_staff_id',
    'temperature_min_c',
    'temperature_max_c',
    'calibration_due_on',
    'maintenance_due_on',
    'alarm_config',
])]
class ColdChainDevice extends Model
{
    /** @use HasFactory<ColdChainDeviceFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'alarm_config' => 'array',
            'calibration_due_on' => 'date',
            'capacity_units' => 'integer',
            'device_type' => ColdChainDeviceType::class,
            'maintenance_due_on' => 'date',
            'status' => ColdChainDeviceStatus::class,
            'temperature_max_c' => 'decimal:2',
            'temperature_min_c' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return HasMany<ColdChainTemperatureReading, $this> */
    public function readings(): HasMany
    {
        return $this->hasMany(ColdChainTemperatureReading::class);
    }

    /** @return HasMany<ColdChainExcursion, $this> */
    public function excursions(): HasMany
    {
        return $this->hasMany(ColdChainExcursion::class);
    }
}
