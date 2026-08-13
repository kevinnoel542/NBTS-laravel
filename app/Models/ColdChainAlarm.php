<?php

namespace App\Models;

use App\ColdChainAlarmStatus;
use Database\Factories\ColdChainAlarmFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cold_chain_device_id',
    'cold_chain_excursion_id',
    'acknowledged_by',
    'status',
    'triggered_at',
    'acknowledged_at',
    'escalated_at',
    'response_target_at',
    'summary',
    'threshold_min_c',
    'threshold_max_c',
    'observed_min_c',
    'observed_max_c',
])]
class ColdChainAlarm extends Model
{
    /** @use HasFactory<ColdChainAlarmFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'immutable_datetime',
            'escalated_at' => 'immutable_datetime',
            'observed_max_c' => 'decimal:2',
            'observed_min_c' => 'decimal:2',
            'response_target_at' => 'immutable_datetime',
            'status' => ColdChainAlarmStatus::class,
            'threshold_max_c' => 'decimal:2',
            'threshold_min_c' => 'decimal:2',
            'triggered_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<ColdChainDevice, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(ColdChainDevice::class, 'cold_chain_device_id');
    }
}
