<?php

use App\BloodGroup;
use App\HospitalRequestStatus;
use App\HospitalRequestUrgency;
use App\Models\ComponentProductCatalog;
use App\Models\Hospital;
use App\Models\HospitalService;
use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use App\Services\HospitalRequestService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('hospital request captures minimum patient identity guidance and downtime state', function () {
    $hospital = Hospital::factory()->create();
    $service = HospitalService::factory()->create(['hospital_id' => $hospital->id]);
    $catalog = ComponentProductCatalog::factory()->create(['code' => 'RCC-HSP-REQ']);
    $requester = hospitalRequestActor(RoleName::HospitalClinicianRequester, $hospital);

    expect(fn () => app(HospitalRequestService::class)->submit(
        hospital: $hospital,
        service: $service,
        catalog: $catalog,
        requester: $requester,
        data: hospitalRequestPayload(['hemoglobin_g_dl' => 11.2]),
    ))->toThrow(ValidationException::class);

    $request = app(HospitalRequestService::class)->submit(
        hospital: $hospital,
        service: $service,
        catalog: $catalog,
        requester: $requester,
        data: hospitalRequestPayload([
            'hemoglobin_g_dl' => 11.2,
            'override_reason' => 'Consultant approved transfusion despite guidance threshold.',
        ]),
    );

    $downtime = app(HospitalRequestService::class)->submit(
        hospital: $hospital,
        service: $service,
        catalog: $catalog,
        requester: $requester,
        data: hospitalRequestPayload([
            'patient_reference' => 'DT-PAT-1',
            'source_mode' => 'downtime_paper',
            'urgency' => HospitalRequestUrgency::Urgent->value,
        ]),
    );

    expect($request->status)->toBe(HospitalRequestStatus::Submitted)
        ->and($request->patient_reference_hash)->toBe(hash('sha256', 'PHASE9-PAT-1'))
        ->and($request->guidance_snapshot['override_required'])->toBeTrue()
        ->and($request->override_reason)->toContain('Consultant')
        ->and($downtime->status)->toBe(HospitalRequestStatus::DowntimeCaptured)
        ->and($downtime->source_mode)->toBe('downtime_paper');
});

test('hospital request users are isolated to their assigned hospital', function () {
    $hospital = Hospital::factory()->create();
    $foreignHospital = Hospital::factory()->create();
    $service = HospitalService::factory()->create(['hospital_id' => $hospital->id]);
    $catalog = ComponentProductCatalog::factory()->create(['code' => 'RCC-HSP-ISO']);
    $foreignRequester = hospitalRequestActor(RoleName::HospitalClinicianRequester, $foreignHospital);

    expect(fn () => app(HospitalRequestService::class)->submit(
        hospital: $hospital,
        service: $service,
        catalog: $catalog,
        requester: $foreignRequester,
        data: hospitalRequestPayload(),
    ))->toThrow(ValidationException::class);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function hospitalRequestPayload(array $overrides = []): array
{
    return array_merge([
        'active_bleeding' => false,
        'diagnosis' => 'Severe anaemia',
        'hemoglobin_g_dl' => 6.8,
        'indication' => 'Symptomatic anaemia',
        'observations' => ['pulse' => 98],
        'patient_birth_year' => 1988,
        'patient_gender' => 'female',
        'patient_reference' => 'PHASE9-PAT-1',
        'quantity_requested' => 1,
        'requested_blood_group' => BloodGroup::OPositive->value,
        'required_at' => now()->addHours(6),
        'urgency' => HospitalRequestUrgency::Routine->value,
    ], $overrides);
}

function hospitalRequestActor(RoleName $role, Hospital $hospital): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([$role->value]);
    StaffAssignment::factory()
        ->forRole($role)
        ->create([
            'organization_unit_id' => $hospital->organization_unit_id,
            'user_id' => $user->id,
        ]);

    return $user;
}
