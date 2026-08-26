<?php

namespace App\Models;

use App\QualityAuditStatus;
use Database\Factories\HospitalTransfusionCommitteeReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property QualityAuditStatus $status
 */
#[Fillable([
    'hospital_id',
    'chaired_by',
    'review_reference',
    'meeting_date',
    'status',
    'utilization_metrics',
    'emergency_release_review',
    'reaction_review',
    'wastage_review',
    'education_actions',
    'linked_deviation_ids',
    'closed_at',
])]
class HospitalTransfusionCommitteeReview extends Model
{
    /** @use HasFactory<HospitalTransfusionCommitteeReviewFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'closed_at' => 'immutable_datetime',
            'education_actions' => 'array',
            'emergency_release_review' => 'array',
            'linked_deviation_ids' => 'array',
            'meeting_date' => 'date',
            'reaction_review' => 'array',
            'status' => QualityAuditStatus::class,
            'utilization_metrics' => 'array',
            'wastage_review' => 'array',
        ];
    }
}
