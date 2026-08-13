<?php

namespace App\Models;

use App\ComponentStatus;
use Database\Factories\ComponentInventoryAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_component_id',
    'blood_center_id',
    'adjusted_by',
    'independent_approved_by',
    'previous_status',
    'new_status',
    'reason',
    'evidence_reference',
    'notes',
    'adjusted_at',
])]
class ComponentInventoryAdjustment extends Model
{
    /** @use HasFactory<ComponentInventoryAdjustmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'adjusted_at' => 'immutable_datetime',
            'new_status' => ComponentStatus::class,
            'previous_status' => ComponentStatus::class,
        ];
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
