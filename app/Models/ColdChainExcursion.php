<?php

namespace App\Models;

use App\ColdChainExcursionStatus;
use Database\Factories\ColdChainExcursionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cold_chain_device_id',
    'opened_by',
    'closed_by',
    'status',
    'started_at',
    'ended_at',
    'observed_min_c',
    'observed_max_c',
    'affected_component_ids',
    'disposition',
    'capa',
    'opened_at',
    'closed_at',
])]
class ColdChainExcursion extends Model
{
    /** @use HasFactory<ColdChainExcursionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'affected_component_ids' => 'array',
            'closed_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'observed_max_c' => 'decimal:2',
            'observed_min_c' => 'decimal:2',
            'opened_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'status' => ColdChainExcursionStatus::class,
        ];
    }

    /** @return BelongsTo<ColdChainDevice, $this> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(ColdChainDevice::class, 'cold_chain_device_id');
    }
}
