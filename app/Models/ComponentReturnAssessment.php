<?php

namespace App\Models;

use App\ComponentReturnDisposition;
use Database\Factories\ComponentReturnAssessmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_component_id',
    'assessed_by',
    'received_at',
    'temperature_min_c',
    'temperature_max_c',
    'package_condition',
    'chain_of_custody',
    'disposition',
    'accepted_for_restock',
    'evidence_reference',
    'notes',
    'assessed_at',
])]
class ComponentReturnAssessment extends Model
{
    /** @use HasFactory<ComponentReturnAssessmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'accepted_for_restock' => 'boolean',
            'assessed_at' => 'immutable_datetime',
            'chain_of_custody' => 'array',
            'disposition' => ComponentReturnDisposition::class,
            'received_at' => 'immutable_datetime',
            'temperature_max_c' => 'decimal:2',
            'temperature_min_c' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
