<?php

namespace App\Models;

use App\EmergencyReleaseStatus;
use Database\Factories\EmergencyReleaseAuthorizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $blood_component_id
 * @property EmergencyReleaseStatus $status
 * @property Carbon $retrospective_completion_due_at
 * @property BloodComponent $component
 */
#[Fillable([
    'hospital_blood_request_id',
    'blood_component_id',
    'authorized_by',
    'acknowledged_by',
    'clinical_authorizer_name',
    'clinical_authorizer_contact',
    'reason',
    'risk_acknowledgement',
    'status',
    'authorized_at',
    'acknowledged_at',
    'retrospective_completion_due_at',
    'retrospective_completed_at',
])]
class EmergencyReleaseAuthorization extends Model
{
    /** @use HasFactory<EmergencyReleaseAuthorizationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'acknowledged_at' => 'immutable_datetime',
            'authorized_at' => 'immutable_datetime',
            'retrospective_completed_at' => 'immutable_datetime',
            'retrospective_completion_due_at' => 'immutable_datetime',
            'status' => EmergencyReleaseStatus::class,
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
}
