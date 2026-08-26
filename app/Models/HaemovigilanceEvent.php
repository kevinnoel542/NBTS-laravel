<?php

namespace App\Models;

use App\HaemovigilanceEventStatus;
use App\HaemovigilanceEventType;
use App\QualitySeverity;
use Database\Factories\HaemovigilanceEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property HaemovigilanceEventStatus $status
 * @property QualitySeverity $severity
 */
#[Fillable([
    'blood_center_id',
    'hospital_id',
    'donor_id',
    'hospital_blood_request_id',
    'transfusion_record_id',
    'blood_component_id',
    'reported_by',
    'assigned_to',
    'closed_by',
    'event_reference',
    'event_type',
    'severity',
    'status',
    'reaction_type',
    'symptoms',
    'occurred_at',
    'immediate_action',
    'treatment',
    'referral',
    'outcome',
    'equipment_context',
    'investigation_context',
    'classification',
    'imputability',
    'reporting_state',
    'supply_context',
    'notifications',
    'escalated_at',
    'followup_due_at',
    'closed_at',
])]
class HaemovigilanceEvent extends Model
{
    /** @use HasFactory<HaemovigilanceEventFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'closed_at' => 'immutable_datetime',
            'equipment_context' => 'array',
            'escalated_at' => 'immutable_datetime',
            'event_type' => HaemovigilanceEventType::class,
            'followup_due_at' => 'immutable_datetime',
            'investigation_context' => 'array',
            'notifications' => 'array',
            'occurred_at' => 'immutable_datetime',
            'severity' => QualitySeverity::class,
            'status' => HaemovigilanceEventStatus::class,
            'supply_context' => 'array',
            'symptoms' => 'array',
        ];
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }

    /** @return BelongsTo<TransfusionRecord, $this> */
    public function transfusionRecord(): BelongsTo
    {
        return $this->belongsTo(TransfusionRecord::class);
    }
}
