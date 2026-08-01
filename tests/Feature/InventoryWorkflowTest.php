<?php

use App\Actions\Inventory\TransitionBloodUnit;
use App\BloodGroup;
use App\BloodUnitStatus;
use App\Exceptions\InvalidBloodUnitTransition;
use App\LowStockAlertStatus;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\CenterStaff;
use App\Models\InventoryAdjustment;
use App\Models\LowStockAlert;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('blood unit transitions adjust available and reserved inventory exactly once', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $firstUnit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'status' => BloodUnitStatus::Testing,
    ]);
    $secondUnit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'status' => BloodUnitStatus::Testing,
    ]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    BloodInventory::factory()->create([
        'available_units' => 0,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'minimum_threshold' => 2,
        'reserved_units' => 0,
    ]);

    $action = app(TransitionBloodUnit::class);
    $action->execute($firstUnit, BloodUnitStatus::Available, $manager);

    $openAlert = LowStockAlert::query()->firstOrFail();

    expect($openAlert->status)->toBe(LowStockAlertStatus::Open)
        ->and($openAlert->available_units)->toBe(1);

    $action->execute($secondUnit, BloodUnitStatus::Available, $manager);

    expect($openAlert->refresh()->status)->toBe(LowStockAlertStatus::Resolved)
        ->and(BloodInventory::query()->firstOrFail()->available_units)->toBe(2);

    $action->execute($firstUnit->refresh(), BloodUnitStatus::Reserved, $manager);
    $action->execute($firstUnit->refresh(), BloodUnitStatus::Used, $manager);

    $inventory = BloodInventory::query()->firstOrFail();

    expect($inventory->available_units)->toBe(1)
        ->and($inventory->reserved_units)->toBe(0)
        ->and(InventoryAdjustment::query()->count())->toBe(4)
        ->and((int) InventoryAdjustment::query()->sum('quantity_delta'))->toBe(1)
        ->and((int) InventoryAdjustment::query()->sum('reserved_quantity_delta'))->toBe(0)
        ->and(LowStockAlert::query()->where('status', LowStockAlertStatus::Open)->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'blood_units.status_changed')->count())->toBe(4);
});

test('repeating or reversing a terminal status does not double adjust inventory', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'status' => BloodUnitStatus::Testing,
    ]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $action = app(TransitionBloodUnit::class);
    $availableUnit = $action->execute($unit, BloodUnitStatus::Available, $manager);

    expect(fn () => $action->execute($availableUnit, BloodUnitStatus::Available, $manager))
        ->toThrow(InvalidBloodUnitTransition::class);

    $usedUnit = $action->execute($availableUnit->refresh(), BloodUnitStatus::Used, $manager);

    expect(fn () => $action->execute($usedUnit, BloodUnitStatus::Available, $manager))
        ->toThrow(InvalidBloodUnitTransition::class)
        ->and(InventoryAdjustment::query()->count())->toBe(2)
        ->and(BloodInventory::query()->firstOrFail()->available_units)->toBe(0);
});

test('inventory management requires permission and the matching center assignment', function () {
    $center = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $centerStaff = User::factory()->staff()->create();
    $otherManager = User::factory()->centerManager()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'status' => BloodUnitStatus::Testing,
    ]);

    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $centerStaff,
    ]);
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $otherCenter,
        'user_id' => $otherManager,
    ]);

    $action = app(TransitionBloodUnit::class);

    expect(fn () => $action->execute($unit, BloodUnitStatus::Available, $centerStaff))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => $action->execute($unit, BloodUnitStatus::Available, $otherManager))
        ->toThrow(AuthorizationException::class)
        ->and(InventoryAdjustment::query()->count())->toBe(0)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('an inconsistent transition cannot make either inventory balance negative', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::BPositive,
        'status' => BloodUnitStatus::Available,
    ]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    BloodInventory::factory()->create([
        'available_units' => 0,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::BPositive,
        'reserved_units' => 0,
    ]);

    expect(fn () => app(TransitionBloodUnit::class)->execute(
        $unit,
        BloodUnitStatus::Reserved,
        $manager,
    ))->toThrow(LogicException::class)
        ->and($unit->fresh()->status)->toBe(BloodUnitStatus::Available)
        ->and(InventoryAdjustment::query()->count())->toBe(0)
        ->and(AuditLog::query()->count())->toBe(0);
});
