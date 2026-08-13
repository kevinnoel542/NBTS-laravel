<?php

namespace App\Models;

use App\HospitalAllocationStatus;
use Database\Factories\HospitalComponentAllocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $hospital_blood_request_id
 * @property int $blood_component_id
 * @property HospitalAllocationStatus $status
 * @property Carbon|null $issued_at
 * @property BloodComponent $component
 * @property HospitalBloodRequest $bloodRequest
 */
#[Fillable([
    'hospital_blood_request_id',
    'blood_component_id',
    'compatibility_test_id',
    'emergency_release_authorization_id',
    'allocated_by',
    'issue_checked_by',
    'status',
    'allocated_at',
    'expires_at',
    'issued_at',
    'final_check',
    'issue_reference',
    'notes',
])]
class HospitalComponentAllocation extends Model
{
    /** @use HasFactory<HospitalComponentAllocationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'allocated_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'final_check' => 'array',
            'issued_at' => 'immutable_datetime',
            'status' => HospitalAllocationStatus::class,
        ];
    }

    /** @return BelongsTo<HospitalBloodRequest, $this> */
    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(HospitalBloodRequest::class, 'hospital_blood_request_id');
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }

    /** @return BelongsTo<CompatibilityTest, $this> */
    public function compatibilityTest(): BelongsTo
    {
        return $this->belongsTo(CompatibilityTest::class);
    }
}
