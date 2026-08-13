<?php

namespace App\Models;

use App\BloodGroup;
use App\HospitalRequestStatus;
use App\HospitalRequestUrgency;
use Database\Factories\HospitalBloodRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $hospital_id
 * @property int $component_product_catalog_id
 * @property int $quantity_allocated
 * @property int $quantity_requested
 * @property string $patient_reference
 * @property string $patient_reference_hash
 * @property BloodGroup|null $requested_blood_group
 * @property HospitalRequestStatus $status
 * @property HospitalRequestUrgency $urgency
 */
#[Fillable([
    'hospital_id',
    'hospital_service_id',
    'requested_by',
    'reviewed_by',
    'component_product_catalog_id',
    'request_reference',
    'patient_reference',
    'patient_reference_hash',
    'patient_birth_year',
    'patient_gender',
    'diagnosis',
    'indication',
    'hemoglobin_g_dl',
    'observations',
    'active_bleeding',
    'urgency',
    'requested_blood_group',
    'quantity_requested',
    'quantity_allocated',
    'required_at',
    'attachments',
    'notes',
    'guidance_snapshot',
    'override_reason',
    'source_mode',
    'status',
    'submitted_at',
    'reviewed_at',
    'partially_filled_at',
    'fulfilled_at',
    'cancelled_at',
])]
class HospitalBloodRequest extends Model
{
    /** @use HasFactory<HospitalBloodRequestFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'active_bleeding' => 'boolean',
            'attachments' => 'array',
            'cancelled_at' => 'immutable_datetime',
            'fulfilled_at' => 'immutable_datetime',
            'guidance_snapshot' => 'array',
            'hemoglobin_g_dl' => 'decimal:2',
            'observations' => 'array',
            'partially_filled_at' => 'immutable_datetime',
            'requested_blood_group' => BloodGroup::class,
            'required_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'status' => HospitalRequestStatus::class,
            'submitted_at' => 'immutable_datetime',
            'urgency' => HospitalRequestUrgency::class,
        ];
    }

    /** @return BelongsTo<Hospital, $this> */
    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    /** @return BelongsTo<HospitalService, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(HospitalService::class, 'hospital_service_id');
    }

    /** @return BelongsTo<ComponentProductCatalog, $this> */
    public function productCatalog(): BelongsTo
    {
        return $this->belongsTo(ComponentProductCatalog::class, 'component_product_catalog_id');
    }

    /** @return HasMany<PatientSpecimen, $this> */
    public function specimens(): HasMany
    {
        return $this->hasMany(PatientSpecimen::class);
    }

    /** @return HasMany<CompatibilityTest, $this> */
    public function compatibilityTests(): HasMany
    {
        return $this->hasMany(CompatibilityTest::class);
    }

    /** @return HasMany<HospitalComponentAllocation, $this> */
    public function allocations(): HasMany
    {
        return $this->hasMany(HospitalComponentAllocation::class);
    }
}
