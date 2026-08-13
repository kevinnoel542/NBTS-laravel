<?php

namespace App\Models;

use App\LogisticsMovementStatus;
use Database\Factories\ComponentTransferItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'component_transfer_id',
    'blood_component_id',
    'status',
    'source_confirmed_at',
    'destination_confirmed_at',
    'accepted',
])]
class ComponentTransferItem extends Model
{
    /** @use HasFactory<ComponentTransferItemFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'accepted' => 'boolean',
            'destination_confirmed_at' => 'immutable_datetime',
            'source_confirmed_at' => 'immutable_datetime',
            'status' => LogisticsMovementStatus::class,
        ];
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
