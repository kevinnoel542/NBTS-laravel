<?php

namespace App\Models;

use App\ComponentReservationStatus;
use Database\Factories\ComponentReservationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_component_id',
    'requested_by',
    'approved_by',
    'status',
    'reason',
    'exception_reason',
    'reserved_at',
    'reserved_until',
    'released_at',
])]
class ComponentReservation extends Model
{
    /** @use HasFactory<ComponentReservationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'released_at' => 'immutable_datetime',
            'reserved_at' => 'immutable_datetime',
            'reserved_until' => 'immutable_datetime',
            'status' => ComponentReservationStatus::class,
        ];
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
