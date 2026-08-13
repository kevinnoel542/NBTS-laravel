<?php

namespace App\Models;

use Database\Factories\ComponentDisposalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_component_id',
    'disposed_by',
    'witnessed_by',
    'approved_by',
    'method',
    'reason',
    'quantity',
    'location',
    'evidence_reference',
    'disposed_at',
])]
class ComponentDisposal extends Model
{
    /** @use HasFactory<ComponentDisposalFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'disposed_at' => 'immutable_datetime',
            'quantity' => 'integer',
        ];
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
