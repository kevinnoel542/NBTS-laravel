<?php

namespace App\Models;

use App\QualitySeverity;
use App\RecallCaseStatus;
use Database\Factories\RecallCaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property RecallCaseStatus $status
 * @property QualitySeverity $severity
 */
#[Fillable([
    'blood_center_id',
    'opened_by',
    'decision_authority_id',
    'closed_by',
    'case_reference',
    'trigger_type',
    'severity',
    'status',
    'description',
    'trigger_evidence',
    'containment_actions',
    'notification_plan',
    'regulator_communication',
    'opened_at',
    'trace_started_at',
    'deadline_at',
    'closed_at',
    'closure_summary',
    'unresolved_exception_reason',
    'approved_for_closure_at',
])]
class RecallCase extends Model
{
    /** @use HasFactory<RecallCaseFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_for_closure_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'containment_actions' => 'array',
            'deadline_at' => 'immutable_datetime',
            'notification_plan' => 'array',
            'opened_at' => 'immutable_datetime',
            'regulator_communication' => 'array',
            'severity' => QualitySeverity::class,
            'status' => RecallCaseStatus::class,
            'trace_started_at' => 'immutable_datetime',
            'trigger_evidence' => 'array',
        ];
    }

    /** @return HasMany<RecallTraceItem, $this> */
    public function traceItems(): HasMany
    {
        return $this->hasMany(RecallTraceItem::class);
    }
}
