<?php

use App\Actions\Inventory\ReconcileInventory;
use App\Actions\Inventory\TransitionBloodUnit;
use App\BloodGroup;
use App\BloodUnitQuarantineReason;
use App\BloodUnitQuarantineStatus;
use App\BloodUnitStatus;
use App\CollectionContainerStatus;
use App\Exceptions\InvalidBloodUnitTransition;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\BloodUnitQuarantine;
use App\Models\CenterStaff;
use App\Models\CollectionContainer;
use App\Models\InventoryAdjustment;
use App\Models\User;
use App\Services\BloodUnitQuarantineService;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('collected and testing units cannot jump into available stock without completed release criteria', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    BloodInventory::factory()->create([
        'available_units' => 0,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'reserved_units' => 0,
    ]);
    $collectedUnit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'status' => BloodUnitStatus::Collected,
    ]);
    $testingUnit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'status' => BloodUnitStatus::Testing,
    ]);

    $action = app(TransitionBloodUnit::class);

    expect(fn () => $action->execute($collectedUnit, BloodUnitStatus::Available, $manager))
        ->toThrow(InvalidBloodUnitTransition::class)
        ->and(fn () => $action->execute($testingUnit, BloodUnitStatus::Available, $manager))
        ->toThrow(LogicException::class)
        ->and(BloodInventory::query()->firstOrFail()->available_units)->toBe(0)
        ->and(InventoryAdjustment::query()->count())->toBe(0);
});

test('available inventory only counts units with a completed quarantine release contract', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 7,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'reserved_units' => 0,
    ]);
    BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'status' => BloodUnitStatus::Available,
    ]);
    $heldAvailableUnit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'status' => BloodUnitStatus::Available,
    ]);
    app(BloodUnitQuarantineService::class)->hold(
        $heldAvailableUnit,
        [BloodUnitQuarantineReason::ReactiveScreening],
        $manager,
    );
    $releasedAvailableUnit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'status' => BloodUnitStatus::Available,
    ]);
    BloodUnitQuarantine::query()->create([
        'blood_unit_id' => $releasedAvailableUnit->id,
        'held_by' => $manager->id,
        'reasons' => [BloodUnitQuarantineReason::IncompleteReleaseCriteria->value],
        'release_criteria_completed_at' => now(),
        'released_by' => $manager->id,
        'status' => BloodUnitQuarantineStatus::Released,
    ]);

    $inspection = app(ReconcileInventory::class)->execute($inventory, $manager, repair: true, reason: 'Phase 7 hard quarantine reconciliation');

    expect($inspection['expected_available'])->toBe(1)
        ->and($inspection['available_delta'])->toBe(-6)
        ->and($inventory->refresh()->available_units)->toBe(1)
        ->and(InventoryAdjustment::query()->where('reason', 'inventory_reconciliation')->count())->toBe(1);
});

test('quarantine service exposes required hard hold reasons and container quarantine contracts', function () {
    $manager = User::factory()->centerManager()->create();
    $bloodUnit = BloodUnit::factory()->create(['status' => BloodUnitStatus::Testing]);
    $reasons = [
        BloodUnitQuarantineReason::IncompleteReleaseCriteria,
        BloodUnitQuarantineReason::ReactiveScreening,
        BloodUnitQuarantineReason::DiscrepantIdentity,
        BloodUnitQuarantineReason::FailedQualityControl,
        BloodUnitQuarantineReason::Expired,
        BloodUnitQuarantineReason::Recalled,
        BloodUnitQuarantineReason::Unlabelled,
        BloodUnitQuarantineReason::ColdChainExcursion,
    ];

    $quarantine = app(BloodUnitQuarantineService::class)->hold($bloodUnit, $reasons, $manager);
    $container = CollectionContainer::factory()->create([
        'status' => CollectionContainerStatus::Quarantined,
    ]);

    expect($quarantine->status)->toBe(BloodUnitQuarantineStatus::Held)
        ->and($quarantine->reasons)->toBe(collect($reasons)->map->value->all())
        ->and(app(BloodUnitQuarantineService::class)->canContributeToAvailableStock($bloodUnit->refresh()->load('quarantine')))->toBeFalse()
        ->and($container->isHardQuarantined())->toBeTrue();
});
