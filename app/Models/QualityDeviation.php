<?php

namespace App\Models;

use App\QualityDeviationStatus;
use App\QualitySeverity;
use Database\Factories\QualityDeviationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property QualityDeviationStatus $status
 * @property QualitySeverity $severity
 */
#[Fillable([
    'blood_center_id',
    'hospital_id',
    'opened_by',
    'owner_id',
    'quality_approved_by',
    'closed_by',
    'deviation_reference',
    'type',
    'severity',
    'status',
    'title',
    'description',
    'affected_records',
    'containment',
    'root_cause',
    'correction',
    'corrective_action',
    'preventive_action',
    'due_at',
    'effectiveness_check',
    'effectiveness_checked_at',
    'closure_evidence',
    'opened_at',
    'closed_at',
])]
class QualityDeviation extends Model
{
    /** @use HasFactory<QualityDeviationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'affected_records' => 'array',
            'closed_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'effectiveness_checked_at' => 'immutable_datetime',
            'opened_at' => 'immutable_datetime',
            'severity' => QualitySeverity::class,
            'status' => QualityDeviationStatus::class,
        ];
    }
}
