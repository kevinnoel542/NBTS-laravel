<?php

namespace App\Models;

use Database\Factories\RolloutPilotReadinessReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'rollout_site_assessment_id',
    'reviewed_by',
    'approved_by',
    'review_reference',
    'pilot_name',
    'pilot_sites',
    'chain_coverage',
    'prerequisites',
    'validation_evidence',
    'data_migration_evidence',
    'training_evidence',
    'downtime_restore_evidence',
    'traceability_recall_evidence',
    'open_defects',
    'signoffs',
    'exit_criteria',
    'status',
    'reviewed_at',
    'approved_at',
])]
class RolloutPilotReadinessReview extends Model
{
    /** @use HasFactory<RolloutPilotReadinessReviewFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'chain_coverage' => 'array',
            'data_migration_evidence' => 'array',
            'downtime_restore_evidence' => 'array',
            'exit_criteria' => 'array',
            'open_defects' => 'array',
            'pilot_sites' => 'array',
            'prerequisites' => 'array',
            'reviewed_at' => 'immutable_datetime',
            'signoffs' => 'array',
            'traceability_recall_evidence' => 'array',
            'training_evidence' => 'array',
            'validation_evidence' => 'array',
        ];
    }

    /** @return BelongsTo<RolloutSiteAssessment, $this> */
    public function siteAssessment(): BelongsTo
    {
        return $this->belongsTo(RolloutSiteAssessment::class, 'rollout_site_assessment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<RolloutScaleReadinessReview, $this> */
    public function scaleReadinessReviews(): HasMany
    {
        return $this->hasMany(RolloutScaleReadinessReview::class);
    }
}
