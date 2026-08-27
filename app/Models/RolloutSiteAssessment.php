<?php

namespace App\Models;

use Database\Factories\RolloutSiteAssessmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'blood_center_id',
    'assessed_by',
    'approved_by',
    'assessment_reference',
    'site_name',
    'site_type',
    'workflow_map',
    'inventory_snapshot',
    'baseline_kpis',
    'risks',
    'data_dictionary_scope',
    'master_data_owners',
    'safety_case_reference',
    'target_process_reference',
    'pilot_scope',
    'prioritized_backlog',
    'legal_and_policy_inputs',
    'operational_readiness',
    'status',
    'assessed_at',
    'approved_at',
])]
class RolloutSiteAssessment extends Model
{
    /** @use HasFactory<RolloutSiteAssessmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'assessed_at' => 'immutable_datetime',
            'baseline_kpis' => 'array',
            'data_dictionary_scope' => 'array',
            'inventory_snapshot' => 'array',
            'legal_and_policy_inputs' => 'array',
            'master_data_owners' => 'array',
            'operational_readiness' => 'array',
            'pilot_scope' => 'array',
            'prioritized_backlog' => 'array',
            'risks' => 'array',
            'workflow_map' => 'array',
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<User, $this> */
    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<RolloutPolicyDecision, $this> */
    public function policyDecisions(): HasMany
    {
        return $this->hasMany(RolloutPolicyDecision::class);
    }

    /** @return HasMany<RolloutPilotReadinessReview, $this> */
    public function pilotReadinessReviews(): HasMany
    {
        return $this->hasMany(RolloutPilotReadinessReview::class);
    }
}
