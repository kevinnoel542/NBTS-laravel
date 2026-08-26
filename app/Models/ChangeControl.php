<?php

namespace App\Models;

use Database\Factories\ChangeControlFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'requested_by',
    'approved_by',
    'change_reference',
    'classification',
    'title',
    'scope',
    'risk_level',
    'approvals',
    'regression_evidence',
    'migration_plan',
    'rollback_plan',
    'release_notes',
    'training_impact',
    'emergency_change',
    'status',
    'effective_at',
    'retrospective_review_due_at',
    'approved_at',
])]
class ChangeControl extends Model
{
    /** @use HasFactory<ChangeControlFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'approvals' => 'array',
            'effective_at' => 'immutable_datetime',
            'emergency_change' => 'boolean',
            'regression_evidence' => 'array',
            'retrospective_review_due_at' => 'immutable_datetime',
            'scope' => 'array',
        ];
    }
}
