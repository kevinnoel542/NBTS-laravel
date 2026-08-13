<?php

namespace App\Models;

use App\BloodGroup;
use App\CompatibilityResult;
use App\CompatibilityTestStatus;
use Database\Factories\CompatibilityTestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $blood_component_id
 * @property CompatibilityResult $compatibility_result
 * @property CompatibilityTestStatus $status
 * @property Carbon|null $valid_until
 * @property BloodComponent $component
 */
#[Fillable([
    'hospital_blood_request_id',
    'patient_specimen_id',
    'blood_component_id',
    'performed_by',
    'reviewed_by',
    'emergency_release_authorization_id',
    'method',
    'instrument_identifier',
    'reagent_lot',
    'control_result',
    'abo_rh_confirmation',
    'antibody_screen_result',
    'compatibility_result',
    'status',
    'performed_at',
    'reviewed_at',
    'valid_until',
    'exception_reason',
    'notes',
])]
class CompatibilityTest extends Model
{
    /** @use HasFactory<CompatibilityTestFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'abo_rh_confirmation' => BloodGroup::class,
            'compatibility_result' => CompatibilityResult::class,
            'performed_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'status' => CompatibilityTestStatus::class,
            'valid_until' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<HospitalBloodRequest, $this> */
    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(HospitalBloodRequest::class, 'hospital_blood_request_id');
    }

    /** @return BelongsTo<PatientSpecimen, $this> */
    public function patientSpecimen(): BelongsTo
    {
        return $this->belongsTo(PatientSpecimen::class);
    }

    /** @return BelongsTo<BloodComponent, $this> */
    public function component(): BelongsTo
    {
        return $this->belongsTo(BloodComponent::class, 'blood_component_id');
    }
}
