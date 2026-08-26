<?php

namespace App\Models;

use Database\Factories\RecoveryExerciseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'operator_id',
    'approver_id',
    'exercise_reference',
    'scenario',
    'rto_minutes',
    'rpo_minutes',
    'recovery_point_at',
    'recovered_at',
    'validation_checks',
    'exceptions',
    'reopening_approved_at',
    'capa_reference',
    'status',
])]
class RecoveryExercise extends Model
{
    /** @use HasFactory<RecoveryExerciseFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'exceptions' => 'array',
            'recovered_at' => 'immutable_datetime',
            'recovery_point_at' => 'immutable_datetime',
            'reopening_approved_at' => 'immutable_datetime',
            'validation_checks' => 'array',
        ];
    }
}
