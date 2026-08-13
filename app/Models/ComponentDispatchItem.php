<?php

namespace App\Models;

use App\LogisticsMovementStatus;
use Database\Factories\ComponentDispatchItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'component_dispatch_id',
    'blood_component_id',
    'status',
    'reconciled_disposition',
    'reconciled_at',
])]
class ComponentDispatchItem extends Model
{
    /** @use HasFactory<ComponentDispatchItemFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'reconciled_at' => 'immutable_datetime',
            'status' => LogisticsMovementStatus::class,
        ];
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
