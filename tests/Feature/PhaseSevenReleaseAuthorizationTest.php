<?php

use App\Actions\Laboratory\AuthorizeBloodUnitRelease;
use App\BloodGroup;
use App\BloodUnitQuarantineReason;
use App\BloodUnitStatus;
use App\LaboratoryQualityControlStatus;
use App\LaboratoryTestCategory;
use App\LaboratoryTestInterpretation;
use App\LaboratoryTestResultStatus;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\CenterStaff;
use App\Models\CollectionEpisode;
use App\Models\LaboratoryEquipment;
use App\Models\LaboratoryQualityControlRun;
use App\Models\LaboratoryReagentLot;
use App\Models\LaboratorySpecimenReceipt;
use App\Models\LaboratoryTestCatalog;
use App\Models\LaboratoryTestOrder;
use App\Models\LaboratoryTestResult;
use App\Models\LaboratoryTestRun;
use App\Models\ReleaseAuthorization;
use App\Models\Specimen;
use App\Models\User;
use App\ReleaseAuthorizationDecision;
use App\RoleName;
use App\Services\BloodUnitQuarantineService;
use App\SpecimenStatus;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    app(RolePermissionSeeder::class)->run();
});

test('routine release requires all required verified lab results', function () {
    $center = BloodCenter::factory()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'status' => BloodUnitStatus::Testing,
    ]);
    $approver = laboratoryReleaseApprover($center);

    createRequiredReleaseResults($unit, missing: ['HCV']);

    $authorization = app(AuthorizeBloodUnitRelease::class)->execute(
        bloodUnit: $unit,
        approver: $approver,
        reason: 'Routine release review.',
        electronicSignature: true,
    );

    expect($authorization->decision)->toBe(ReleaseAuthorizationDecision::Rejected)
        ->and($authorization->criteria_version)->toBe('NBTS-P7-REL-AUTH-v1')
        ->and($authorization->exceptions)->toContain('missing_required_test:HCV')
        ->and($authorization->evaluated_tests)->toHaveCount(5)
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Testing)
        ->and(BloodInventory::query()->count())->toBe(0);
});

test('routine release blocks same person tester verifier or releaser', function () {
    $center = BloodCenter::factory()->create();
    $actor = laboratoryReleaseApprover($center);
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'status' => BloodUnitStatus::Testing,
    ]);

    createRequiredReleaseResults($unit, recorder: $actor, verifier: $actor);

    $authorization = app(AuthorizeBloodUnitRelease::class)->execute(
        bloodUnit: $unit,
        approver: $actor,
        reason: 'Release should be rejected by separation controls.',
        electronicSignature: true,
    );

    expect($authorization->decision)->toBe(ReleaseAuthorizationDecision::Rejected)
        ->and($authorization->released_by)->toBeNull()
        ->and($authorization->exceptions)->toContain('tester_verifier_not_separated:ABO-RH', 'releaser_participated_in_testing')
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Testing);
});

test('emergency override records an auditable exception without routine stock release', function () {
    $center = BloodCenter::factory()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::BPositive,
        'status' => BloodUnitStatus::Testing,
    ]);
    $approver = laboratoryReleaseApprover($center);
    $independentApprover = laboratoryReleaseApprover($center);

    createRequiredReleaseResults($unit, overrides: [
        'HIV-1-2' => [
            'interpretation' => LaboratoryTestInterpretation::Reactive,
            'is_release_blocking' => true,
            'result_value' => 'reactive',
        ],
    ]);

    $authorization = app(AuthorizeBloodUnitRelease::class)->execute(
        bloodUnit: $unit,
        approver: $approver,
        reason: 'Emergency clinical escalation only, not stock release.',
        electronicSignature: true,
        independentApprover: $independentApprover,
        emergencyOverride: true,
        exceptions: ['emergency_release_request'],
    );

    expect($authorization->decision)->toBe(ReleaseAuthorizationDecision::EmergencyOverride)
        ->and($authorization->independent_approved_by)->toBe($independentApprover->id)
        ->and($authorization->released_by)->toBeNull()
        ->and($authorization->exceptions)->toContain('unsafe_result:HIV-1-2', 'emergency_release_request')
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Testing)
        ->and(BloodInventory::query()->count())->toBe(0);
});

test('exception and emergency decisions require an independent approver', function () {
    $center = BloodCenter::factory()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'status' => BloodUnitStatus::Testing,
    ]);
    $approver = laboratoryReleaseApprover($center);

    createRequiredReleaseResults($unit);

    expect(fn () => app(AuthorizeBloodUnitRelease::class)->execute(
        bloodUnit: $unit,
        approver: $approver,
        reason: 'Emergency request.',
        electronicSignature: true,
        emergencyOverride: true,
    ))->toThrow(ValidationException::class)
        ->and(ReleaseAuthorization::query()->count())->toBe(0)
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Testing);
});

test('routine release records criteria and moves only eligible units to available inventory', function () {
    $center = BloodCenter::factory()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::AbPositive,
        'status' => BloodUnitStatus::Testing,
    ]);
    $approver = laboratoryReleaseApprover($center);

    createRequiredReleaseResults($unit);

    $authorization = app(AuthorizeBloodUnitRelease::class)->execute(
        bloodUnit: $unit,
        approver: $approver,
        reason: 'All required testing complete and acceptable.',
        electronicSignature: true,
    );

    $inventory = BloodInventory::query()->where('blood_center_id', $center->id)->sole();

    expect($authorization->decision)->toBe(ReleaseAuthorizationDecision::RoutineRelease)
        ->and($authorization->released_by)->toBe($approver->id)
        ->and($authorization->electronic_signature)->toBeTrue()
        ->and($authorization->evaluated_tests)->toHaveCount(5)
        ->and($authorization->evaluated_tests[0])->toHaveKeys(['algorithm_version', 'method', 'quality_control_status', 'test_code', 'verified_by'])
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Available)
        ->and($unit->fresh()->current_location)->toBe('Available stock')
        ->and($unit->fresh()->quarantine->hasCompletedReleaseCriteria())->toBeTrue()
        ->and($inventory->available_units)->toBe(1)
        ->and(AuditLog::query()->where('action', 'laboratory.release_decision_recorded')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'blood_units.laboratory_released')->count())->toBe(1);
});

test('release blocks invalid repeated discrepant failed qc recalled and excursion affected units', function () {
    $center = BloodCenter::factory()->create();
    $approver = laboratoryReleaseApprover($center);
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::BPositive,
        'status' => BloodUnitStatus::Testing,
    ]);

    createRequiredReleaseResults($unit);

    releaseResultFor('HCV')->forceFill([
        'status' => LaboratoryTestResultStatus::Invalid,
    ])->save();
    releaseResultFor('SYPHILIS')->forceFill([
        'status' => LaboratoryTestResultStatus::Repeated,
    ])->save();
    releaseResultFor('ABO-RH')->forceFill([
        'interpretation' => LaboratoryTestInterpretation::Discrepant,
        'is_release_blocking' => true,
        'result_value' => 'ABO/Rh discrepant',
    ])->save();
    releaseResultFor('HBSAG')->qualityControlRun->forceFill([
        'status' => LaboratoryQualityControlStatus::Failed,
    ])->save();
    app(BloodUnitQuarantineService::class)->hold($unit, [
        BloodUnitQuarantineReason::Recalled,
        BloodUnitQuarantineReason::ColdChainExcursion,
    ], $approver);

    $authorization = app(AuthorizeBloodUnitRelease::class)->execute(
        bloodUnit: $unit,
        approver: $approver,
        reason: 'Safety block release review.',
        electronicSignature: true,
    );

    expect($authorization->decision)->toBe(ReleaseAuthorizationDecision::Rejected)
        ->and($authorization->released_by)->toBeNull()
        ->and($authorization->exceptions)->toContain(
            'unsafe_result:ABO-RH',
            'quality_control_not_acceptable:HBSAG',
            'invalid_result:HCV',
            'repeated_result:SYPHILIS',
            'unresolved_quarantine:recalled',
            'unresolved_quarantine:cold_chain_excursion',
        )
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Testing)
        ->and(BloodInventory::query()->count())->toBe(0);
});

test('expired units cannot be converted into released stock even when tests are clean', function () {
    $center = BloodCenter::factory()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'status' => BloodUnitStatus::Expired,
    ]);
    $approver = laboratoryReleaseApprover($center);

    createRequiredReleaseResults($unit);

    expect(fn () => app(AuthorizeBloodUnitRelease::class)->execute(
        bloodUnit: $unit,
        approver: $approver,
        reason: 'Expired unit release attempt.',
        electronicSignature: true,
    ))->toThrow(ValidationException::class)
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Expired)
        ->and(BloodInventory::query()->count())->toBe(0);
});

function laboratoryReleaseApprover(BloodCenter $center): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([RoleName::CenterStaff->value, RoleName::LaboratoryApproverQualityOfficer->value]);
    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $user,
    ]);

    return $user;
}

function laboratoryResultTechnician(BloodCenter $center): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([RoleName::CenterStaff->value, RoleName::LaboratoryTechnician->value]);
    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $user,
    ]);

    return $user;
}

/**
 * @param  list<string>  $missing
 * @param  array<string, array<string, mixed>>  $overrides
 */
function createRequiredReleaseResults(
    BloodUnit $unit,
    ?User $recorder = null,
    ?User $verifier = null,
    array $missing = [],
    array $overrides = [],
): void {
    $unit->load('bloodCenter');
    $recorder ??= laboratoryResultTechnician($unit->bloodCenter);
    $verifier ??= laboratoryResultTechnician($unit->bloodCenter);
    $episode = CollectionEpisode::factory()->create([
        'blood_center_id' => $unit->blood_center_id,
        'donation_id' => $unit->donation_id,
        'donor_id' => $unit->donor_id,
    ]);
    $specimen = Specimen::factory()->create([
        'collection_episode_id' => $episode->id,
        'specimen_type' => 'serology',
        'status' => SpecimenStatus::HandedOff,
    ]);
    $receipt = LaboratorySpecimenReceipt::factory()->create([
        'blood_center_id' => $unit->blood_center_id,
        'collection_episode_id' => $episode->id,
        'specimen_id' => $specimen->id,
    ]);
    $equipment = LaboratoryEquipment::factory()->create(['blood_center_id' => $unit->blood_center_id]);

    foreach (['ABO-RH', 'HIV-1-2', 'HBSAG', 'HCV', 'SYPHILIS'] as $testCode) {
        if (in_array($testCode, $missing, true)) {
            continue;
        }

        $catalog = LaboratoryTestCatalog::factory()->create([
            'category' => $testCode === 'ABO-RH' ? LaboratoryTestCategory::BloodGrouping : LaboratoryTestCategory::TtiScreening,
            'code' => $testCode,
            'name' => str($testCode)->replace('-', ' ')->title()->toString(),
            'specimen_type' => 'serology',
        ]);
        $reagent = LaboratoryReagentLot::factory()->create(['laboratory_test_catalog_id' => $catalog->id]);
        $qc = LaboratoryQualityControlRun::factory()->create([
            'laboratory_equipment_id' => $equipment->id,
            'laboratory_reagent_lot_id' => $reagent->id,
            'laboratory_test_catalog_id' => $catalog->id,
            'status' => LaboratoryQualityControlStatus::Passed,
        ]);
        $order = LaboratoryTestOrder::factory()->create([
            'laboratory_specimen_receipt_id' => $receipt->id,
            'laboratory_test_catalog_id' => $catalog->id,
            'specimen_id' => $specimen->id,
        ]);
        $run = LaboratoryTestRun::factory()->create([
            'laboratory_equipment_id' => $equipment->id,
            'laboratory_reagent_lot_id' => $reagent->id,
            'laboratory_test_catalog_id' => $catalog->id,
            'laboratory_test_order_id' => $order->id,
        ]);

        LaboratoryTestResult::factory()->create([
            'entered_by' => $recorder->id,
            'interpretation' => $testCode === 'ABO-RH' ? LaboratoryTestInterpretation::Negative : LaboratoryTestInterpretation::NonReactive,
            'is_release_blocking' => false,
            'laboratory_quality_control_run_id' => $qc->id,
            'laboratory_test_catalog_id' => $catalog->id,
            'laboratory_test_order_id' => $order->id,
            'laboratory_test_run_id' => $run->id,
            'result_value' => $testCode === 'ABO-RH' ? 'O positive confirmed' : 'non-reactive',
            'status' => LaboratoryTestResultStatus::Verified,
            'verified_at' => now(),
            'verified_by' => $verifier->id,
            ...($overrides[$testCode] ?? []),
        ]);
    }
}

function releaseResultFor(string $testCode): LaboratoryTestResult
{
    return LaboratoryTestResult::query()
        ->whereHas('testCatalog', fn ($query) => $query->where('code', $testCode))
        ->latest('id')
        ->firstOrFail();
}
