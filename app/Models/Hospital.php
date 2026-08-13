<?php

namespace App\Models;

use App\HospitalStatus;
use Database\Factories\HospitalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int|null $organization_unit_id
 * @property HospitalStatus $status
 */
#[Fillable([
    'organization_unit_id',
    'code',
    'name',
    'status',
    'blood_bank_level',
    'contacts',
    'capabilities',
    'operating_hours',
    'request_routes',
    'integration_identifier',
    'minimum_patient_identity_fields',
    'privacy_policy_version',
    'approved_at',
    'approved_by',
])]
class Hospital extends Model
{
    /** @use HasFactory<HospitalFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'capabilities' => 'array',
            'contacts' => 'array',
            'minimum_patient_identity_fields' => 'array',
            'operating_hours' => 'array',
            'request_routes' => 'array',
            'status' => HospitalStatus::class,
        ];
    }

    /** @return BelongsTo<OrganizationUnit, $this> */
    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    /** @return HasMany<HospitalService, $this> */
    public function services(): HasMany
    {
        return $this->hasMany(HospitalService::class);
    }

    /** @return HasMany<HospitalBloodRequest, $this> */
    public function bloodRequests(): HasMany
    {
        return $this->hasMany(HospitalBloodRequest::class);
    }
}
