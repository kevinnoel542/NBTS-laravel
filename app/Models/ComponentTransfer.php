<?php

namespace App\Models;

use App\LogisticsMovementStatus;
use Database\Factories\ComponentTransferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'source_center_id',
    'destination_center_id',
    'requested_by',
    'approved_by',
    'status',
    'urgency',
    'reason',
    'package_seal',
    'courier_name',
    'vehicle_identifier',
    'departed_at',
    'received_at',
    'temperature_evidence',
    'discrepancy_notes',
    'acceptance_decision',
])]
class ComponentTransfer extends Model
{
    /** @use HasFactory<ComponentTransferFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'departed_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
            'status' => LogisticsMovementStatus::class,
            'temperature_evidence' => 'array',
        ];
    }

    /** @return HasMany<ComponentTransferItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(ComponentTransferItem::class);
    }
}
