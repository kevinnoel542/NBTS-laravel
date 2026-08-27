<?php

namespace App\Models;

use Database\Factories\RolloutScaleReadinessReviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rollout_pilot_readiness_review_id',
    'reviewed_by',
    'approved_by',
    'review_reference',
    'scale_level',
    'candidate_sites',
    'readiness_criteria',
    'kpi_comparison',
    'monitoring_plan',
    'support_model',
    'operating_budget',
    'vendor_exit_plan',
    'unresolved_risks',
    'status',
    'reviewed_at',
    'approved_at',
])]
class RolloutScaleReadinessReview extends Model
{
    /** @use HasFactory<RolloutScaleReadinessReviewFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'candidate_sites' => 'array',
            'kpi_comparison' => 'array',
            'monitoring_plan' => 'array',
            'operating_budget' => 'array',
            'readiness_criteria' => 'array',
            'reviewed_at' => 'immutable_datetime',
            'support_model' => 'array',
            'unresolved_risks' => 'array',
            'vendor_exit_plan' => 'array',
        ];
    }

    /** @return BelongsTo<RolloutPilotReadinessReview, $this> */
    public function pilotReadinessReview(): BelongsTo
    {
        return $this->belongsTo(RolloutPilotReadinessReview::class, 'rollout_pilot_readiness_review_id');
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
}
