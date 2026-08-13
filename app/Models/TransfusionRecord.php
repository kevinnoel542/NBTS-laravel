<?php

namespace App\Models;

use App\TransfusionRecordStatus;
use Database\Factories\TransfusionRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property BloodComponent $component
 * @property HospitalBloodRequest $bloodRequest
 */
#[Fillable([
    'hospital_blood_request_id',
    'hospital_component_allocation_id',
    'hospital_component_receipt_id',
    'patient_specimen_id',
    'blood_component_id',
    'verified_by',
    'recorded_by',
    'status',
    'bedside_checks',
    'verified_at',
    'started_at',
    'completed_at',
    'observations',
    'volume_ml',
    'outcome',
    'unused_component_disposition',
    'overdue_at',
    'final_disposition_at',
    'notes',
])]
class TransfusionRecord extends Model
{
    /** @use HasFactory<TransfusionRecordFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'bedside_checks' => 'array',
            'completed_at' => 'immutable_datetime',
            'final_disposition_at' => 'immutable_datetime',
            'observations' => 'array',
            'overdue_at' => 'immutable_datetime',
            'started_at' => 'immutable_datetime',
            'status' => TransfusionRecordStatus::class,
            'verified_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<HospitalBloodRequest, $this> */
    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(HospitalBloodRequest::class, 'hospital_blood_request_id');
    }

    /** @return BelongsTo<HospitalComponentAllocation, $this> */
    public function allocation(): BelongsTo
    {
        return $this->belongsTo(HospitalComponentAllocation::class, 'hospital_component_allocation_id');
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
