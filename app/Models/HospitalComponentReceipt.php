<?php

namespace App\Models;

use App\HospitalReceiptStatus;
use Database\Factories\HospitalComponentReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $hospital_blood_request_id
 * @property int $blood_component_id
 * @property HospitalReceiptStatus $status
 */
#[Fillable([
    'hospital_component_allocation_id',
    'hospital_blood_request_id',
    'blood_component_id',
    'hospital_id',
    'received_by',
    'status',
    'received_at',
    'condition',
    'temperature_evidence',
    'discrepancy_notes',
    'chain_of_custody',
])]
class HospitalComponentReceipt extends Model
{
    /** @use HasFactory<HospitalComponentReceiptFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'chain_of_custody' => 'array',
            'received_at' => 'immutable_datetime',
            'status' => HospitalReceiptStatus::class,
            'temperature_evidence' => 'array',
        ];
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
