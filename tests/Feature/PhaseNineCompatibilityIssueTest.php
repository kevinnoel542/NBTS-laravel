<?php

use App\BloodGroup;
use App\CompatibilityResult;
use App\ComponentStatus;
use App\HospitalAllocationStatus;
use App\HospitalRequestUrgency;
use App\Models\BloodComponent;
use App\Models\ComponentProductCatalog;
use App\Models\Hospital;
use App\Models\HospitalService;
use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use App\Services\CompatibilityWorkflowService;
use App\Services\HospitalIssueService;
use App\Services\HospitalRequestService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('compatibility workflow allocates FEFO and enforces final issue checks', function () {
    [$hospital, $service, $catalog] = compatHospitalSetup('RCC-XMT-FEFO');
    $requester = compatHospitalActor(RoleName::HospitalClinicianRequester, $hospital);
    $crossmatch = compatHospitalActor(RoleName::CompatibilityCrossmatchOfficer, $hospital);
    $bloodBank = compatHospitalActor(RoleName::HospitalBloodBankOfficer, $hospital);
    $request = app(HospitalRequestService::class)->submit($hospital, $service, $catalog, $requester, compatRequestPayload());
    $specimen = app(CompatibilityWorkflowService::class)->receiveSpecimen(
        request: $request,
        actor: $crossmatch,
        specimenIdentifier: 'PSP-P9-1',
        patientReference: 'PHASE9-PAT-1',
        positiveIdentificationMethod: 'wristband barcode and request form',
        bloodGroup: BloodGroup::OPositive,
    );
    $later = BloodComponent::factory()->create([
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->addDays(20),
        'status' => ComponentStatus::Available,
    ]);
    $earlier = BloodComponent::factory()->create([
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->addDays(4),
        'status' => ComponentStatus::Available,
    ]);

    app(CompatibilityWorkflowService::class)->recordCompatibility($request, $specimen, $later, $crossmatch, $crossmatch, CompatibilityResult::Compatible);
    app(CompatibilityWorkflowService::class)->recordCompatibility($request, $specimen, $earlier, $crossmatch, $crossmatch, CompatibilityResult::Compatible);

    $allocation = app(HospitalIssueService::class)->allocateFefo($request, $bloodBank);

    expect($allocation->blood_component_id)->toBe($earlier->id)
        ->and($earlier->fresh()->status)->toBe(ComponentStatus::Allocated)
        ->and($later->fresh()->status)->toBe(ComponentStatus::Available);

    expect(fn () => app(HospitalIssueService::class)->issue($allocation, $bloodBank, ['request' => true]))
        ->toThrow(ValidationException::class);

    $issued = app(HospitalIssueService::class)->issue($allocation, $bloodBank, compatFinalIssueChecks());

    expect($issued->status)->toBe(HospitalAllocationStatus::Issued)
        ->and($earlier->fresh()->status)->toBe(ComponentStatus::Issued)
        ->and($issued->final_check['compatibility_or_emergency'])->toBeTrue();
});

test('compatibility blocks wrong patient incompatible and unsafe components while emergency release is explicit', function () {
    [$hospital, $service, $catalog] = compatHospitalSetup('RCC-XMT-BLOCK');
    $requester = compatHospitalActor(RoleName::HospitalClinicianRequester, $hospital);
    $crossmatch = compatHospitalActor(RoleName::CompatibilityCrossmatchOfficer, $hospital);
    $bloodBank = compatHospitalActor(RoleName::HospitalBloodBankOfficer, $hospital);
    $request = app(HospitalRequestService::class)->submit($hospital, $service, $catalog, $requester, compatRequestPayload([
        'urgency' => HospitalRequestUrgency::Emergency->value,
    ]));

    expect(fn () => app(CompatibilityWorkflowService::class)->receiveSpecimen(
        request: $request,
        actor: $crossmatch,
        specimenIdentifier: 'PSP-WRONG',
        patientReference: 'WRONG-PATIENT',
        positiveIdentificationMethod: 'wristband',
        bloodGroup: BloodGroup::OPositive,
    ))->toThrow(ValidationException::class);

    $specimen = app(CompatibilityWorkflowService::class)->receiveSpecimen($request, $crossmatch, 'PSP-P9-2', 'PHASE9-PAT-1', 'wristband', BloodGroup::OPositive);
    $wrongGroup = BloodComponent::factory()->create([
        'blood_group' => BloodGroup::APositive,
        'component_product_catalog_id' => $catalog->id,
        'status' => ComponentStatus::Available,
    ]);
    $expired = BloodComponent::factory()->create([
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->subDay(),
        'status' => ComponentStatus::Available,
    ]);
    $emergencyComponent = BloodComponent::factory()->create([
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->addDays(10),
        'status' => ComponentStatus::Available,
    ]);

    expect(fn () => app(CompatibilityWorkflowService::class)->recordCompatibility($request, $specimen, $wrongGroup, $crossmatch, $crossmatch, CompatibilityResult::Compatible))
        ->toThrow(ValidationException::class);
    expect(fn () => app(CompatibilityWorkflowService::class)->recordCompatibility($request, $specimen, $expired, $crossmatch, $crossmatch, CompatibilityResult::Compatible))
        ->toThrow(ValidationException::class);

    $authorization = app(CompatibilityWorkflowService::class)->authorizeEmergencyRelease(
        request: $request,
        component: $emergencyComponent,
        actor: $bloodBank,
        clinicalAuthorizerName: 'Dr Phase Nine',
        reason: 'Life-threatening haemorrhage requires immediate release.',
        riskAcknowledgement: 'The clinical team accepts emergency release risk and retrospective completion.',
    );
    $allocation = app(HospitalIssueService::class)->allocateFefo($request, $bloodBank);

    expect($authorization->blood_component_id)->toBe($emergencyComponent->id)
        ->and($allocation->emergency_release_authorization_id)->toBe($authorization->id)
        ->and($emergencyComponent->fresh()->status)->toBe(ComponentStatus::Allocated);
});

function compatHospitalSetup(string $catalogCode): array
{
    $hospital = Hospital::factory()->create();
    $service = HospitalService::factory()->create(['hospital_id' => $hospital->id]);
    $catalog = ComponentProductCatalog::factory()->create(['code' => $catalogCode]);

    return [$hospital, $service, $catalog];
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function compatRequestPayload(array $overrides = []): array
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

function compatHospitalActor(RoleName $role, Hospital $hospital): User
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

function compatFinalIssueChecks(): array
{
    return [
        'compatibility_or_emergency' => true,
        'component' => true,
        'expiry' => true,
        'label' => true,
        'package' => true,
        'patient' => true,
        'release' => true,
        'request' => true,
        'staff' => true,
    ];
}
