<?php

namespace App\Models;

use App\BloodGroup;
use App\PatientSpecimenStatus;
use Database\Factories\PatientSpecimenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $hospital_blood_request_id
 * @property int $hospital_id
 * @property string $patient_reference_hash
 * @property string|null $antibody_screen_result
 * @property BloodGroup|null $blood_group
 * @property PatientSpecimenStatus $status
 * @property Carbon $expires_at
 */
#[Fillable([
    'hospital_blood_request_id',
    'hospital_id',
    'collected_by',
    'received_by',
    'specimen_identifier',
    'patient_reference',
    'patient_reference_hash',
    'positive_identification_method',
    'blood_group',
    'antibody_screen_result',
    'status',
    'collected_at',
    'received_at',
    'expires_at',
    'rejection_reason',
])]
class PatientSpecimen extends Model
{
    /** @use HasFactory<PatientSpecimenFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'collected_at' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
            'received_at' => 'immutable_datetime',
            'status' => PatientSpecimenStatus::class,
        ];
    }

    /** @return BelongsTo<HospitalBloodRequest, $this> */
    public function bloodRequest(): BelongsTo
    {
        return $this->belongsTo(HospitalBloodRequest::class, 'hospital_blood_request_id');
    }

    /** @return BelongsTo<Hospital, $this> */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /** @return HasMany<CompatibilityTest, $this> */
    public function compatibilityTests(): HasMany
    {
        return $this->hasMany(CompatibilityTest::class);
    }
}
