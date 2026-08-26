<?php

namespace App\Models;

use App\RecallTraceItemStatus;
use Database\Factories\RecallTraceItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property RecallTraceItemStatus $status
 */
#[Fillable([
    'recall_case_id',
    'donation_id',
    'blood_unit_id',
    'blood_component_id',
    'hospital_id',
    'hospital_blood_request_id',
    'transfusion_record_id',
    'trace_direction',
    'item_type',
    'item_identifier',
    'current_location',
    'status',
    'notifications',
    'disposition',
    'located_at',
    'notified_at',
    'resolved_at',
    'exception_reason',
])]
class RecallTraceItem extends Model
{
    /** @use HasFactory<RecallTraceItemFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'disposition' => 'array',
            'located_at' => 'immutable_datetime',
            'notifications' => 'array',
            'notified_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'status' => RecallTraceItemStatus::class,
        ];
    }

    /** @return BelongsTo<RecallCase, $this> */
    public function recallCase(): BelongsTo
    {
        return $this->belongsTo(RecallCase::class);
    }
}
