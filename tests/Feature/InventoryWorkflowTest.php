<?php

use App\Actions\Inventory\AdjustInventory;
use App\Actions\Inventory\ProcessExpiredBloodUnits;
use App\Actions\Inventory\ReconcileInventory;
use App\Actions\Inventory\TransitionBloodUnit;
use App\BloodGroup;
use App\BloodUnitStatus;
use App\Data\AdjustInventoryData;
use App\Exceptions\InvalidBloodUnitTransition;
use App\Livewire\Operations\Workspace;
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
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

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

test('an authorized manual adjustment updates both balances with a complete audit trail', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 2,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::AbNegative,
        'reserved_units' => 1,
    ]);

    $adjustment = app(AdjustInventory::class)->execute(
        $inventory,
        new AdjustInventoryData(
            availableDelta: 1,
            reservedDelta: -1,
            reason: 'Verified physical count after the morning handover.',
            notes: 'Count witnessed by the center manager.',
        ),
        $manager,
    );

    expect($inventory->refresh()->available_units)->toBe(3)
        ->and($inventory->reserved_units)->toBe(0)
        ->and($adjustment->quantity_delta)->toBe(1)
        ->and($adjustment->reserved_quantity_delta)->toBe(-1)
        ->and($adjustment->adjusted_by)->toBe($manager->id)
        ->and(AuditLog::query()->where('action', 'inventory.manually_adjusted')->count())->toBe(1);

    expect(fn () => app(AdjustInventory::class)->execute(
        $inventory->refresh(),
        new AdjustInventoryData(
            availableDelta: -4,
            reservedDelta: 0,
            reason: 'Attempt to reduce stock below a valid physical balance.',
        ),
        $manager,
    ))->toThrow(ValidationException::class)
        ->and($inventory->refresh()->available_units)->toBe(3);
});

test('expiry processing is center scoped and removes due available units from stock', function () {
    $center = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 1,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::BPositive,
        'reserved_units' => 0,
    ]);
    $dueUnit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::BPositive,
        'expiry_date' => today()->subDay(),
        'status' => BloodUnitStatus::Available,
    ]);
    $outsideUnit = BloodUnit::factory()->create([
        'blood_center_id' => $otherCenter,
        'blood_group' => BloodGroup::BPositive,
        'expiry_date' => today()->subDay(),
        'status' => BloodUnitStatus::Available,
    ]);

    $processed = app(ProcessExpiredBloodUnits::class)->execute($manager);

    expect($processed)->toBe(1)
        ->and($dueUnit->refresh()->status)->toBe(BloodUnitStatus::Expired)
        ->and($outsideUnit->refresh()->status)->toBe(BloodUnitStatus::Available)
        ->and($inventory->refresh()->available_units)->toBe(0)
        ->and(InventoryAdjustment::query()->where('reason', 'unit_status_expired')->count())->toBe(1);
});

test('rejected and expired blood units require a final disposal confirmation', function (BloodUnitStatus $initialStatus) {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'status' => $initialStatus,
    ]);

    $disposed = app(TransitionBloodUnit::class)->execute(
        $unit,
        BloodUnitStatus::Discarded,
        $manager,
        'Controlled disposal witnessed and recorded by authorized staff.',
    );

    expect($disposed->status)->toBe(BloodUnitStatus::Discarded)
        ->and($disposed->handled_by)->toBe($manager->id);
})->with([
    'rejected unit' => BloodUnitStatus::Rejected,
    'expired unit' => BloodUnitStatus::Expired,
]);

test('inventory reconciliation reports and repairs aggregate drift from traceable unit states', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 7,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::ONegative,
        'reserved_units' => 4,
    ]);
    BloodUnit::factory()->count(2)->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::ONegative,
        'status' => BloodUnitStatus::Available,
    ]);
    BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::ONegative,
        'status' => BloodUnitStatus::Reserved,
    ]);

    $reconcile = app(ReconcileInventory::class);
    $inspection = $reconcile->execute($inventory, $manager);

    expect($inspection['mismatch'])->toBeTrue()
        ->and($inspection['available_delta'])->toBe(-5)
        ->and($inspection['reserved_delta'])->toBe(-3)
        ->and($inventory->refresh()->available_units)->toBe(7);

    $repair = $reconcile->execute(
        inventory: $inventory,
        actor: $manager,
        repair: true,
        reason: 'Reconciled against the signed physical unit register.',
    );

    expect($repair['repaired'])->toBeTrue()
        ->and($inventory->refresh()->available_units)->toBe(2)
        ->and($inventory->reserved_units)->toBe(1)
        ->and(InventoryAdjustment::query()->where('reason', 'inventory_reconciliation')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'inventory.reconciled')->count())->toBe(1);
});

test('the reconciliation command requires an authorized actor and can repair a scoped center', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 5,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'reserved_units' => 0,
    ]);
    BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::APositive,
        'status' => BloodUnitStatus::Available,
    ]);

    $this->artisan('inventory:reconcile', [
        '--actor' => $manager->id,
        '--center' => $center->id,
        '--repair' => true,
        '--reason' => 'Scheduled reconciliation against traceable blood units.',
    ])->assertSuccessful();

    expect($inventory->refresh()->available_units)->toBe(1);
});

test('the Livewire inventory drawer applies controlled corrections and exposes reconciliation', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 2,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::BNegative,
        'reserved_units' => 1,
    ]);

    Livewire::actingAs($manager)
        ->test(Workspace::class, ['workspace' => 'blood-operations'])
        ->set('tab', 'inventory')
        ->call('openRecord', $inventory->id)
        ->assertSee(__('console.inventory.adjust_title'))
        ->set('inventoryAvailableDelta', '1')
        ->set('inventoryReservedDelta', '-1')
        ->set('inventoryAdjustmentReason', 'Signed correction following a controlled physical count.')
        ->set('inventoryAdjustmentNotes', 'Witnessed during shift handover.')
        ->call('adjustActiveInventory')
        ->assertHasNoErrors()
        ->assertSet('activeRecordId', null)
        ->assertSet('notice', __('console.inventory.adjusted'));

    expect($inventory->refresh()->available_units)->toBe(3)
        ->and($inventory->reserved_units)->toBe(0);
});

test('the Livewire expiry queue processes only due units in the active center scope', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);
    BloodInventory::factory()->create([
        'available_units' => 1,
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'reserved_units' => 0,
    ]);
    $unit = BloodUnit::factory()->create([
        'blood_center_id' => $center,
        'blood_group' => BloodGroup::OPositive,
        'expiry_date' => today(),
        'status' => BloodUnitStatus::Available,
    ]);

    Livewire::actingAs($manager)
        ->test(Workspace::class, ['workspace' => 'blood-operations'])
        ->set('tab', 'expiry')
        ->assertSee(__('console.inventory.process_expired'))
        ->call('processExpiredUnits')
        ->assertHasNoErrors()
        ->assertSet('notice', __('console.inventory.expired_processed', ['count' => 1]));

    expect($unit->refresh()->status)->toBe(BloodUnitStatus::Expired);
});
