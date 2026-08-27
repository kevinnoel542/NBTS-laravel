<?php

namespace App\Models;

use Database\Factories\RolloutPolicyDecisionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'rollout_site_assessment_id',
    'owner_id',
    'approved_by',
    'decision_code',
    'category',
    'title',
    'decision_summary',
    'options_considered',
    'required_approvals',
    'approval_evidence',
    'risk_acceptance',
    'implementation_controls',
    'review_schedule',
    'status',
    'due_at',
    'approved_at',
])]
class RolloutPolicyDecision extends Model
{
    /** @use HasFactory<RolloutPolicyDecisionFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approval_evidence' => 'array',
            'approved_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'implementation_controls' => 'array',
            'options_considered' => 'array',
            'required_approvals' => 'array',
            'review_schedule' => 'array',
            'risk_acceptance' => 'array',
        ];
    }

    /** @return BelongsTo<RolloutSiteAssessment, $this> */
    public function siteAssessment(): BelongsTo
    {
        return $this->belongsTo(RolloutSiteAssessment::class, 'rollout_site_assessment_id');
    }

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
