<?php

namespace App\Models;

use Database\Factories\ColdChainTemperatureReadingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cold_chain_device_id',
    'recorded_by',
    'temperature_c',
    'recorded_at',
    'sync_state',
    'payload',
])]
class ColdChainTemperatureReading extends Model
{
    /** @use HasFactory<ColdChainTemperatureReadingFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'recorded_at' => 'immutable_datetime',
            'temperature_c' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<ColdChainDevice, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(ColdChainDevice::class, 'cold_chain_device_id');
    }
}
