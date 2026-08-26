<?php

namespace App\Models;

use App\EqaAssessmentStatus;
use Database\Factories\EqaAssessmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property EqaAssessmentStatus $status
 */
#[Fillable([
    'blood_center_id',
    'laboratory_test_catalog_id',
    'submitted_by',
    'reviewed_by',
    'scheme_code',
    'round_code',
    'status',
    'expected_results',
    'submitted_results',
    'findings',
    'linked_deviation_ids',
    'due_at',
    'submitted_at',
    'reviewed_at',
])]
class EqaAssessment extends Model
{
    /** @use HasFactory<EqaAssessmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'due_at' => 'immutable_datetime',
            'expected_results' => 'array',
            'findings' => 'array',
            'linked_deviation_ids' => 'array',
            'reviewed_at' => 'immutable_datetime',
            'status' => EqaAssessmentStatus::class,
            'submitted_at' => 'immutable_datetime',
            'submitted_results' => 'array',
        ];
    }
}
