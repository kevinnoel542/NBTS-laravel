<?php

use App\BloodGroup;
use App\ComponentReservationStatus;
use App\ComponentStatus;
use App\Models\BloodCenter;
use App\Models\BloodComponent;
use App\Models\ComponentInventoryAdjustment;
use App\Models\ComponentProductCatalog;
use App\Models\ComponentReservation;
use App\Models\User;
use App\RoleName;
use App\Services\ComponentInventoryService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('component inventory allocates FEFO and prevents double reservation', function () {
    $center = BloodCenter::factory()->create();
    $catalog = ComponentProductCatalog::factory()->create(['code' => 'RCC-FEFO']);
    $actor = inventoryOfficer();
    $later = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->addDays(20),
        'status' => ComponentStatus::Available,
    ]);
    $earlier = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'blood_group' => BloodGroup::OPositive,
        'component_product_catalog_id' => $catalog->id,
        'expiry_date' => today()->addDays(5),
        'status' => ComponentStatus::Available,
    ]);

    $reservation = app(ComponentInventoryService::class)->reserveFefo(
        center: $center,
        catalog: $catalog,
        bloodGroup: BloodGroup::OPositive->value,
        actor: $actor,
        reason: 'Hospital allocation request',
    );

    expect($reservation->blood_component_id)->toBe($earlier->id)
        ->and($earlier->fresh()->status)->toBe(ComponentStatus::Reserved)
        ->and($later->fresh()->status)->toBe(ComponentStatus::Available);

    ComponentReservation::query()->where('blood_component_id', $earlier->id)->update(['reserved_until' => now()->subMinute()]);
    $released = app(ComponentInventoryService::class)->releaseStaleReservations();

    expect($released)->toBe(1)
        ->and($earlier->fresh()->status)->toBe(ComponentStatus::Available)
        ->and($reservation->fresh()->status)->toBe(ComponentReservationStatus::Expired);
});

test('component reconciliation expiry return disposal and manual adjustment evidence are enforced', function () {
    $center = BloodCenter::factory()->create();
    $actor = inventoryOfficer();
    $witness = inventoryOfficer();
    $available = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'status' => ComponentStatus::Available,
    ]);
    $expired = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'expiry_date' => today()->subDay(),
        'status' => ComponentStatus::Available,
    ]);
    $returned = BloodComponent::factory()->create([
        'blood_center_id' => $center->id,
        'status' => ComponentStatus::InTransit,
    ]);

    $expiredCount = app(ComponentInventoryService::class)->expireEligible($actor);
    $assessment = app(ComponentInventoryService::class)->assessReturn(
        component: $returned,
        actor: $actor,
        temperatureMin: 2.5,
        temperatureMax: 5.5,
        packageCondition: 'intact',
        chainOfCustody: [['from' => 'Hospital', 'to' => 'Center', 'at' => now()->toIso8601String()]],
    );
    $disposal = app(ComponentInventoryService::class)->dispose(
        component: $expired,
        actor: $actor,
        witness: $witness,
        reason: 'expiry',
        method: 'biohazard destruction',
        location: 'Waste room',
        evidenceReference: 'WASTE-001',
    );
    $reconciliation = app(ComponentInventoryService::class)->reconcile($center, [
        ComponentStatus::Available->value => 2,
        ComponentStatus::Discarded->value => 1,
    ]);

    expect($expiredCount)->toBe(1)
        ->and($assessment->accepted_for_restock)->toBeTrue()
        ->and($returned->fresh()->status)->toBe(ComponentStatus::Available)
        ->and($disposal->reason)->toBe('expiry')
        ->and($expired->fresh()->status)->toBe(ComponentStatus::Discarded)
        ->and($reconciliation['mismatch'])->toBeFalse()
        ->and(ComponentInventoryAdjustment::query()->where('evidence_reference', 'Automatic component expiry')->exists())->toBeTrue();

    expect(fn () => app(ComponentInventoryService::class)->transition($available, ComponentStatus::InvestigationHold, $actor, '', ''))
        ->toThrow(ValidationException::class);
});

function inventoryOfficer(): User
{
    $user = User::factory()->staff()->create();
    $user->syncRoles([RoleName::InventoryOfficer->value]);

    return $user;
}
