<?php

use App\Actions\Laboratory\ApproveLaboratoryTestCatalog;
use App\Actions\Laboratory\ReceiveLaboratorySpecimen;
use App\Actions\Laboratory\RecordLaboratoryQualityControl;
use App\Actions\Laboratory\RecordLaboratoryTestResult;
use App\LaboratoryEquipmentStatus;
use App\LaboratoryInterfaceMode;
use App\LaboratoryQualityControlStatus;
use App\LaboratoryReagentStatus;
use App\LaboratoryReagentValidationState;
use App\LaboratoryTestCategory;
use App\LaboratoryTestInterpretation;
use App\LaboratoryTestOrderStatus;
use App\Models\BloodCenter;
use App\Models\CollectionEpisode;
use App\Models\LaboratoryEquipment;
use App\Models\LaboratoryQualityEvent;
use App\Models\LaboratoryReagentLot;
use App\Models\LaboratorySpecimenReceipt;
use App\Models\LaboratoryTestCatalog;
use App\Models\LaboratoryTestOrder;
use App\Models\OrganizationUnit;
use App\Models\Specimen;
use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use App\SpecimenStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);

    $this->organizationUnit = OrganizationUnit::factory()->create();
    $this->center = BloodCenter::factory()->create(['organization_unit_id' => $this->organizationUnit->id]);
    $this->actor = User::factory()->staff()->create();
    $this->assignment = StaffAssignment::factory()
        ->for($this->actor)
        ->for($this->organizationUnit)
        ->forRole(RoleName::LaboratoryTechnician)
        ->create();

    session(['operations.assignment' => (string) $this->assignment->id]);

    $this->episode = CollectionEpisode::factory()->create(['blood_center_id' => $this->center->id]);
    $this->specimen = Specimen::factory()->create([
        'collection_episode_id' => $this->episode->id,
        'specimen_type' => 'serology',
        'status' => SpecimenStatus::HandedOff,
        'handed_off_by' => $this->actor->id,
        'handed_off_at' => now(),
        'handoff_recipient' => 'Laboratory specimen reception',
    ]);
});

test('approved catalog entries drive specimen reception and automatic release test orders', function () {
    $qualityOwner = User::factory()->staff()->create();
    StaffAssignment::factory()
        ->for($qualityOwner)
        ->for($this->organizationUnit)
        ->forRole(RoleName::LaboratoryApproverQualityOfficer)
        ->create();
    session(['operations.assignment' => (string) StaffAssignment::query()->where('user_id', $qualityOwner->id)->sole()->id]);

    $catalog = app(ApproveLaboratoryTestCatalog::class)->handle(
        actor: $qualityOwner,
        code: 'HIV-AGAB',
        name: 'HIV antigen antibody screening',
        category: LaboratoryTestCategory::TtiScreening,
        specimenType: 'serology',
        method: 'Manual ELISA',
        algorithmVersion: 'construction-v1',
        isRequiredForRelease: true,
        releaseBlockingInterpretations: ['reactive', 'invalid'],
    );

    session(['operations.assignment' => (string) $this->assignment->id]);
    $receipt = app(ReceiveLaboratorySpecimen::class)->handle(
        actor: $this->actor,
        specimen: $this->specimen,
        scannedIdentifier: $this->specimen->specimen_identifier,
        receivingStation: 'Laboratory specimen reception',
    );

    expect($catalog->approved_by)->toBe($qualityOwner->id)
        ->and($receipt->status->value)->toBe('accepted')
        ->and($receipt->orders)->toHaveCount(1)
        ->and($receipt->orders->first()->laboratory_test_catalog_id)->toBe($catalog->id)
        ->and(LaboratoryTestOrder::query()->where('status', LaboratoryTestOrderStatus::Ordered)->count())->toBe(1);
});

test('catalog reagent and equipment master data stores approved versions lifecycle and interface state', function () {
    $qualityOwner = User::factory()->staff()->create();
    StaffAssignment::factory()
        ->for($qualityOwner)
        ->for($this->organizationUnit)
        ->forRole(RoleName::LaboratoryApproverQualityOfficer)
        ->create();
    session(['operations.assignment' => (string) StaffAssignment::query()->where('user_id', $qualityOwner->id)->sole()->id]);

    $catalog = app(ApproveLaboratoryTestCatalog::class)->handle(
        actor: $qualityOwner,
        code: 'HBSAG',
        name: 'Hepatitis B surface antigen',
        category: LaboratoryTestCategory::TtiScreening,
        specimenType: 'serology',
        method: 'Analyzer CLIA',
        algorithmVersion: 'tti-v2.4',
        isRequiredForRelease: true,
        releaseBlockingInterpretations: ['reactive', 'invalid', 'discrepant'],
    );
    $validatedReagent = LaboratoryReagentLot::factory()->create([
        'laboratory_test_catalog_id' => $catalog->id,
        'status' => LaboratoryReagentStatus::Usable,
        'validation_state' => LaboratoryReagentValidationState::Validated,
        'expires_on' => today()->addMonth(),
        'validated_at' => now(),
    ]);
    $recalledReagent = LaboratoryReagentLot::factory()->create([
        'laboratory_test_catalog_id' => $catalog->id,
        'status' => LaboratoryReagentStatus::Recalled,
        'validation_state' => LaboratoryReagentValidationState::Validated,
        'expires_on' => today()->addMonth(),
        'recalled_at' => now(),
    ]);
    $activeAnalyzer = LaboratoryEquipment::factory()->create([
        'blood_center_id' => $this->center->id,
        'calibration_due_on' => today(),
        'interface_mode' => LaboratoryInterfaceMode::Analyzer,
        'last_validated_at' => now(),
        'status' => LaboratoryEquipmentStatus::Active,
    ]);
    $downtimeAnalyzer = LaboratoryEquipment::factory()->create([
        'blood_center_id' => $this->center->id,
        'downtime_started_at' => now(),
        'status' => LaboratoryEquipmentStatus::Downtime,
    ]);

    expect($catalog->algorithm_version)->toBe('tti-v2.4')
        ->and($catalog->approved_by)->toBe($qualityOwner->id)
        ->and($catalog->blocksInterpretation('reactive'))->toBeTrue()
        ->and($validatedReagent->permitsTestingUse())->toBeTrue()
        ->and($recalledReagent->permitsTestingUse())->toBeFalse()
        ->and($activeAnalyzer->permitsTestingUse())->toBeTrue()
        ->and($downtimeAnalyzer->permitsTestingUse())->toBeFalse();
});

test('laboratory result recording requires passed QC and marks unsafe interpretations as release blocking', function () {
    $catalog = LaboratoryTestCatalog::factory()->create([
        'specimen_type' => 'serology',
        'release_blocking_interpretations' => ['reactive', 'invalid'],
    ]);
    $receipt = LaboratorySpecimenReceipt::factory()->create(['specimen_id' => $this->specimen->id]);
    $order = LaboratoryTestOrder::factory()->create([
        'laboratory_specimen_receipt_id' => $receipt->id,
        'specimen_id' => $this->specimen->id,
        'laboratory_test_catalog_id' => $catalog->id,
        'ordered_by' => $this->actor->id,
    ]);
    $equipment = LaboratoryEquipment::factory()->create(['blood_center_id' => $this->center->id]);
    $reagent = LaboratoryReagentLot::factory()->create([
        'laboratory_test_catalog_id' => $catalog->id,
        'status' => LaboratoryReagentStatus::Usable,
        'validation_state' => LaboratoryReagentValidationState::Validated,
    ]);

    $failedQc = app(RecordLaboratoryQualityControl::class)->handle(
        actor: $this->actor,
        catalog: $catalog,
        status: LaboratoryQualityControlStatus::Failed,
        expectedResults: ['negative_control' => 'negative'],
        observedResults: ['negative_control' => 'positive'],
        equipment: $equipment,
        reagentLot: $reagent,
        failureReason: 'Negative control was reactive.',
    );

    expect(LaboratoryQualityEvent::query()->count())->toBe(1)
        ->and(fn () => app(RecordLaboratoryTestResult::class)->handle(
            actor: $this->actor,
            order: $order,
            qualityControlRun: $failedQc,
            interpretation: LaboratoryTestInterpretation::NonReactive,
            resultValue: 'non-reactive',
            equipment: $equipment,
            reagentLot: $reagent,
        ))->toThrow(ValidationException::class);

    $passedQc = app(RecordLaboratoryQualityControl::class)->handle(
        actor: $this->actor,
        catalog: $catalog,
        status: LaboratoryQualityControlStatus::Passed,
        expectedResults: ['positive_control' => 'positive', 'negative_control' => 'negative'],
        observedResults: ['positive_control' => 'positive', 'negative_control' => 'negative'],
        equipment: $equipment,
        reagentLot: $reagent,
    );

    $result = app(RecordLaboratoryTestResult::class)->handle(
        actor: $this->actor,
        order: $order,
        qualityControlRun: $passedQc,
        interpretation: LaboratoryTestInterpretation::Reactive,
        resultValue: 'reactive',
        equipment: $equipment,
        reagentLot: $reagent,
    );

    expect($result->is_release_blocking)->toBeTrue()
        ->and($result->order->fresh()->status)->toBe(LaboratoryTestOrderStatus::Resulted);
});

test('laboratory result recording rejects invalid equipment and reagent context', function () {
    $catalog = LaboratoryTestCatalog::factory()->create(['specimen_type' => 'serology']);
    $receipt = LaboratorySpecimenReceipt::factory()->create(['specimen_id' => $this->specimen->id]);
    $order = LaboratoryTestOrder::factory()->create([
        'laboratory_specimen_receipt_id' => $receipt->id,
        'specimen_id' => $this->specimen->id,
        'laboratory_test_catalog_id' => $catalog->id,
        'ordered_by' => $this->actor->id,
    ]);
    $expiredEquipment = LaboratoryEquipment::factory()->create([
        'blood_center_id' => $this->center->id,
        'calibration_due_on' => today()->subDay(),
        'status' => LaboratoryEquipmentStatus::Active,
    ]);
    $unvalidatedReagent = LaboratoryReagentLot::factory()->create([
        'laboratory_test_catalog_id' => $catalog->id,
        'status' => LaboratoryReagentStatus::Usable,
        'validation_state' => LaboratoryReagentValidationState::Pending,
    ]);
    $passedQc = app(RecordLaboratoryQualityControl::class)->handle(
        actor: $this->actor,
        catalog: $catalog,
        status: LaboratoryQualityControlStatus::Passed,
        expectedResults: ['negative_control' => 'negative'],
        observedResults: ['negative_control' => 'negative'],
    );

    expect(fn () => app(RecordLaboratoryTestResult::class)->handle(
        actor: $this->actor,
        order: $order,
        qualityControlRun: $passedQc,
        interpretation: LaboratoryTestInterpretation::NonReactive,
        resultValue: 'non-reactive',
        equipment: $expiredEquipment,
    ))->toThrow(ValidationException::class)
        ->and(fn () => app(RecordLaboratoryTestResult::class)->handle(
            actor: $this->actor,
            order: $order,
            qualityControlRun: $passedQc,
            interpretation: LaboratoryTestInterpretation::NonReactive,
            resultValue: 'non-reactive',
            reagentLot: $unvalidatedReagent,
        ))->toThrow(ValidationException::class);
});

test('laboratory receipt rejects wrong barcode and duplicate receipt attempts', function () {
    expect(fn () => app(ReceiveLaboratorySpecimen::class)->handle(
        actor: $this->actor,
        specimen: $this->specimen,
        scannedIdentifier: 'WRONG-BARCODE',
        receivingStation: 'Laboratory specimen reception',
    ))->toThrow(ValidationException::class);

    app(ReceiveLaboratorySpecimen::class)->handle(
        actor: $this->actor,
        specimen: $this->specimen,
        scannedIdentifier: $this->specimen->specimen_identifier,
        receivingStation: 'Laboratory specimen reception',
    );

    expect(fn () => app(ReceiveLaboratorySpecimen::class)->handle(
        actor: $this->actor,
        specimen: $this->specimen,
        scannedIdentifier: $this->specimen->specimen_identifier,
        receivingStation: 'Laboratory specimen reception',
    ))->toThrow(ValidationException::class);
});
