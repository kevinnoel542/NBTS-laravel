<?php

use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use App\PermissionName;
use App\RoleName;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('the canonical role and permission matrix is seeded idempotently', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(Permission::query()->count())->toBe(count(PermissionName::cases()))
        ->and(Role::query()->pluck('name')->all())->toEqualCanonicalizing(array_map(
            static fn (RoleName $role): string => $role->value,
            RoleName::cases(),
        ));

    expect(Role::findByName(RoleName::SuperAdmin->value)->hasPermissionTo(PermissionName::ManageBackups->value))->toBeTrue()
        ->and(Role::findByName(RoleName::SuperAdmin->value)->hasPermissionTo(PermissionName::ApproveLaboratoryRelease->value))->toBeFalse()
        ->and(Role::findByName(RoleName::Donor->value)->permissions)->toBeEmpty();
});

test('national and center roles receive distinct sensitive permissions', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $nbtsAdmin = User::factory()->nbtsAdmin()->create();
    $centerManager = User::factory()->centerManager()->create();
    $centerStaff = User::factory()->staff()->create();
    $donor = User::factory()->donor()->create();

    expect($superAdmin->can(PermissionName::ManageBackups->value))->toBeTrue()
        ->and($superAdmin->can('unregistered.infrastructure.ability'))->toBeFalse()
        ->and($superAdmin->can(PermissionName::ApproveLaboratoryRelease->value))->toBeFalse()
        ->and($nbtsAdmin->can(PermissionName::ManageCenters->value))->toBeTrue()
        ->and($nbtsAdmin->can(PermissionName::ManageBackups->value))->toBeFalse()
        ->and($centerManager->can(PermissionName::ManageCenterStaff->value))->toBeTrue()
        ->and($centerManager->can(PermissionName::ManageCenters->value))->toBeFalse()
        ->and($centerStaff->can(PermissionName::RecordDonations->value))->toBeTrue()
        ->and($centerStaff->can(PermissionName::ManageInventory->value))->toBeFalse()
        ->and($donor->can(PermissionName::ViewDonors->value))->toBeFalse();
});

test('center access requires an active assignment unless the user has national scope', function () {
    $assignedCenter = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $nationalAdmin = User::factory()->nbtsAdmin()->create();

    CenterStaff::factory()->manager()->create([
        'user_id' => $manager,
        'blood_center_id' => $assignedCenter,
    ]);

    expect($manager->hasCenterAccess($assignedCenter))->toBeTrue()
        ->and($manager->hasCenterAccess($otherCenter))->toBeFalse()
        ->and($nationalAdmin->hasCenterAccess($assignedCenter))->toBeTrue()
        ->and(BloodCenter::query()->visibleTo($manager)->pluck('id')->all())->toBe([$assignedCenter->id]);

    $manager->centerStaffAssignments()->update(['is_active' => false]);

    expect($manager->hasCenterAccess($assignedCenter))->toBeFalse();
});

test('blood center policy separates public information from scoped operations', function () {
    $activeCenter = BloodCenter::factory()->create();
    $inactiveCenter = BloodCenter::factory()->inactive()->create();
    $manager = User::factory()->centerManager()->create();
    $nationalAdmin = User::factory()->nbtsAdmin()->create();

    CenterStaff::factory()->manager()->create([
        'user_id' => $manager,
        'blood_center_id' => $activeCenter,
    ]);

    expect(Gate::forUser($manager)->allows('viewOperations', $activeCenter))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('viewOperations', $inactiveCenter))->toBeTrue()
        ->and(Gate::forUser($manager)->denies('update', $activeCenter))->toBeTrue()
        ->and(Gate::forUser($nationalAdmin)->allows('update', $activeCenter))->toBeTrue()
        ->and(Gate::forUser(null)->allows('view', $activeCenter))->toBeTrue()
        ->and(Gate::forUser(null)->denies('view', $inactiveCenter))->toBeTrue();
});

test('inactive super administrators do not bypass authorization', function () {
    $inactiveSuperAdmin = User::factory()->superAdmin()->inactive()->create();

    expect(Gate::forUser($inactiveSuperAdmin)->denies(PermissionName::ManageBackups->value))->toBeTrue();
});
