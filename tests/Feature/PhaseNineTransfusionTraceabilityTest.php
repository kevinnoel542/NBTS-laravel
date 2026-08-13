<?php

use App\BloodGroup;
use App\CompatibilityResult;
use App\ComponentStatus;
use App\HospitalAllocationStatus;
use App\HospitalReceiptStatus;
use App\HospitalRequestUrgency;
use App\Models\BloodComponent;
use App\Models\ComponentProductCatalog;
use App\Models\Hospital;
use App\Models\HospitalService;
use App\Models\StaffAssignment;
use App\Models\TransfusionRecord;
use App\Models\User;
use App\RoleName;
use App\Services\CompatibilityWorkflowService;
use App\Services\HospitalIssueService;
use App\Services\HospitalRequestService;
use App\TransfusionRecordStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('end to end pilot traces donor component through receipt bedside transfusion and final disposition', function () {
    [$hospital, $service, $catalog] = transfusionHospitalSetup('RCC-TRF-E2E');
    $requester = transfusionHospitalActor(RoleName::HospitalClinicianRequester, $hospital);
    $crossmatch = transfusionHospitalActor(RoleName::CompatibilityCrossmatchOfficer, $hospital);
    $bloodBank = transfusionHospitalActor(RoleName::HospitalBloodBankOfficer, $hospital);
    $nurse = transfusionHospitalActor(RoleName::TransfusionNurseOfficer, $hospital);
    $component = BloodComponent::factory()->create([
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->addDays(8),
        'status' => ComponentStatus::Available,
    ]);
    $request = app(HospitalRequestService::class)->submit($hospital, $service, $catalog, $requester, transfusionRequestPayload());
    $specimen = app(CompatibilityWorkflowService::class)->receiveSpecimen($request, $crossmatch, 'PSP-P9-TRF', 'PHASE9-PAT-1', 'wristband barcode', BloodGroup::OPositive);
    app(CompatibilityWorkflowService::class)->recordCompatibility($request, $specimen, $component, $crossmatch, $crossmatch, CompatibilityResult::Compatible);
    $allocation = app(HospitalIssueService::class)->allocateFefo($request, $bloodBank);
    $issued = app(HospitalIssueService::class)->issue($allocation, $bloodBank, transfusionFinalIssueChecks());
    $receipt = app(HospitalIssueService::class)->recordReceipt(
        allocation: $issued,
        actor: $bloodBank,
        condition: 'intact',
        temperatureEvidence: ['receipt_c' => 4.1, 'logger' => 'DL-P9'],
        chainOfCustody: ['NBTS dispatch', 'hospital blood bank'],
    );

    expect(fn () => app(HospitalIssueService::class)->recordTransfusion($issued, $specimen, $receipt, $nurse, ['right_patient' => true], 280, 'completed_without_reaction'))
        ->toThrow(ValidationException::class);

    $transfusion = app(HospitalIssueService::class)->recordTransfusion(
        allocation: $issued->fresh(),
        specimen: $specimen,
        receipt: $receipt,
        nurse: $nurse,
        bedsideChecks: transfusionBedsideChecks(),
        volumeMl: 280,
        outcome: 'completed_without_reaction',
        observations: ['15_min' => 'stable', 'completion' => 'stable'],
    );

    expect($receipt->status)->toBe(HospitalReceiptStatus::Accepted)
        ->and($issued->fresh()->status)->toBe(HospitalAllocationStatus::Received)
        ->and($transfusion->status)->toBe(TransfusionRecordStatus::Completed)
        ->and($component->fresh()->status)->toBe(ComponentStatus::Transfused)
        ->and($transfusion->component->donation->user_id)->toBe($component->fresh()->donation->user_id)
        ->and($transfusion->bloodRequest->patient_reference_hash)->toBe(hash('sha256', 'PHASE9-PAT-1'));

    expect(fn () => app(HospitalIssueService::class)->recordTransfusion($issued->fresh(), $specimen, $receipt, $nurse, transfusionBedsideChecks(), 280, 'duplicate'))
        ->toThrow(ValidationException::class);
});

test('unreported transfusion outcomes appear in overdue reconciliation queue', function () {
    [$hospital, $service, $catalog] = transfusionHospitalSetup('RCC-TRF-ODQ');
    $requester = transfusionHospitalActor(RoleName::HospitalClinicianRequester, $hospital);
    $crossmatch = transfusionHospitalActor(RoleName::CompatibilityCrossmatchOfficer, $hospital);
    $bloodBank = transfusionHospitalActor(RoleName::HospitalBloodBankOfficer, $hospital);
    $component = BloodComponent::factory()->create([
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->addDays(8),
        'status' => ComponentStatus::Available,
    ]);
    $request = app(HospitalRequestService::class)->submit($hospital, $service, $catalog, $requester, transfusionRequestPayload());
    $specimen = app(CompatibilityWorkflowService::class)->receiveSpecimen($request, $crossmatch, 'PSP-P9-ODQ', 'PHASE9-PAT-1', 'wristband barcode', BloodGroup::OPositive);
    app(CompatibilityWorkflowService::class)->recordCompatibility($request, $specimen, $component, $crossmatch, $crossmatch, CompatibilityResult::Compatible);
    $allocation = app(HospitalIssueService::class)->allocateFefo($request, $bloodBank);
    $issued = app(HospitalIssueService::class)->issue($allocation, $bloodBank, transfusionFinalIssueChecks());
    app(HospitalIssueService::class)->recordReceipt($issued, $bloodBank, 'intact', ['receipt_c' => 4.0], ['NBTS dispatch', 'hospital blood bank']);
    $issued->fresh()->forceFill([
        'issued_at' => now()->subDays(2),
        'status' => HospitalAllocationStatus::Received,
    ])->save();

    $queue = app(HospitalIssueService::class)->overdueOutcomeQueue($hospital);

    expect($queue)->toHaveCount(1)
        ->and($queue->first()->blood_component_id)->toBe($component->id)
        ->and(TransfusionRecord::query()->count())->toBe(0);
});

function transfusionBedsideChecks(): array
{
    return [
        'compatibility_or_emergency' => true,
        'expiry' => true,
        'right_component' => true,
        'right_patient' => true,
        'right_request' => true,
        'right_time' => true,
        'staff' => true,
    ];
}

function transfusionHospitalSetup(string $catalogCode): array
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
function transfusionRequestPayload(array $overrides = []): array
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

function transfusionHospitalActor(RoleName $role, Hospital $hospital): User
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

function transfusionFinalIssueChecks(): array
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
