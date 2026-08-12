<?php

use App\Actions\Assignments\CreateStaffAssignment;
use App\Actions\Assignments\SetStaffAssignmentStatus;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\OrganizationUnit;
use App\Models\StaffAssignment;
use App\Models\User;
use App\OrganizationUnitType;
use App\PermissionName;
use App\RoleName;
use App\StaffAssignmentStatus;
use Database\Seeders\OrganizationStructureSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('all target and compatibility roles are seeded from the canonical catalogue', function () {
    expect(Role::query()->pluck('name')->all())->toEqualCanonicalizing(RoleName::values())
        ->and(RoleName::targetValues())->toHaveCount(26)
        ->and(RoleName::cases())->toHaveCount(28);
});

test('the active assignment selects permissions without leaking another assignment role', function () {
    $staffMember = User::factory()->staff()->create();
    $centerUnit = OrganizationUnit::factory()->create();
    $otherCenterUnit = OrganizationUnit::factory()->create();

    $receptionAssignment = StaffAssignment::factory()
        ->for($staffMember)
        ->for($centerUnit)
        ->forRole(RoleName::ReceptionOfficer)
        ->create();
    $inventoryAssignment = StaffAssignment::factory()
        ->for($staffMember)
        ->for($otherCenterUnit)
        ->forRole(RoleName::InventoryOfficer)
        ->create();

    session(['operations.assignment' => $receptionAssignment->id]);

    expect($staffMember->can(PermissionName::RegisterDonors->value))->toBeTrue()
        ->and($staffMember->can(PermissionName::ManageInventory->value))->toBeFalse();

    session(['operations.assignment' => $inventoryAssignment->id]);

    expect($staffMember->can(PermissionName::ManageInventory->value))->toBeTrue()
        ->and($staffMember->can(PermissionName::RegisterDonors->value))->toBeFalse();
});

test('expired suspended and cross-account assignments cannot authorize access', function () {
    $staffMember = User::factory()->staff()->create();
    $otherStaffMember = User::factory()->staff()->create();
    $organizationUnit = OrganizationUnit::factory()->create();

    foreach (['expired', 'suspended'] as $state) {
        $assignment = StaffAssignment::factory()
            ->for($staffMember)
            ->for($organizationUnit)
            ->forRole(RoleName::InventoryOfficer)
            ->{$state}()
            ->create();

        session(['operations.assignment' => $assignment->id]);

        expect($staffMember->can(PermissionName::ManageInventory->value))->toBeFalse();
    }

    $otherAssignment = StaffAssignment::factory()
        ->for($otherStaffMember)
        ->for($organizationUnit)
        ->forRole(RoleName::InventoryOfficer)
        ->create();

    session(['operations.assignment' => $otherAssignment->id]);

    expect($staffMember->can(PermissionName::ManageInventory->value))->toBeFalse();
});

test('center visibility follows only the selected effective assignment scope', function () {
    $staffMember = User::factory()->staff()->create();
    $assignedUnit = OrganizationUnit::factory()->create();
    $otherUnit = OrganizationUnit::factory()->create();
    $assignedCenter = BloodCenter::factory()->create(['organization_unit_id' => $assignedUnit->id]);
    $otherCenter = BloodCenter::factory()->create(['organization_unit_id' => $otherUnit->id]);
    $assignment = StaffAssignment::factory()
        ->for($staffMember)
        ->for($assignedUnit)
        ->forRole(RoleName::ReceptionOfficer)
        ->create();

    session(['operations.assignment' => $assignment->id]);

    expect($staffMember->hasCenterAccess($assignedCenter))->toBeTrue()
        ->and($staffMember->hasCenterAccess($otherCenter))->toBeFalse()
        ->and(BloodCenter::query()->visibleTo($staffMember)->pluck('id')->all())->toBe([$assignedCenter->id]);
});

test('assignment creation validates unit scope and prevents clinical self assignment', function () {
    $actor = User::factory()->superAdmin()->create();
    $staffMember = User::factory()->staff()->create();
    $centerUnit = OrganizationUnit::factory()->create();
    $hospitalUnit = OrganizationUnit::factory()->hospital()->create();

    $assignment = app(CreateStaffAssignment::class)->handle(
        actor: $actor,
        staffMember: $staffMember,
        roleName: RoleName::ReceptionOfficer,
        organizationUnit: $centerUnit,
        reason: 'Approved for the reception coverage rotation.',
    );

    expect($assignment->status)->toBe(StaffAssignmentStatus::Active)
        ->and($assignment->approved_by)->toBe($actor->id);

    expect(fn () => app(CreateStaffAssignment::class)->handle(
        actor: $actor,
        staffMember: $staffMember,
        roleName: RoleName::ReceptionOfficer,
        organizationUnit: $hospitalUnit,
        reason: 'This intentionally uses an invalid organization scope.',
    ))->toThrow(ValidationException::class);

    $clinicalActor = User::factory()->staff()->create();
    $nationalUnit = OrganizationUnit::factory()->national()->create();
    StaffAssignment::factory()
        ->for($clinicalActor)
        ->for($nationalUnit)
        ->forRole(RoleName::NationalOperationsAdministrator)
        ->create();

    expect(fn () => app(CreateStaffAssignment::class)->handle(
        actor: $clinicalActor,
        staffMember: $clinicalActor,
        roleName: RoleName::LaboratoryTechnician,
        organizationUnit: $centerUnit,
        reason: 'Self assignment must be rejected for clinical authority.',
    ))->toThrow(AuthorizationException::class);
});

test('revoking an assignment removes its authority and records who revoked it', function () {
    $actor = User::factory()->superAdmin()->create();
    $staffMember = User::factory()->staff()->create();
    $organizationUnit = OrganizationUnit::factory()->create();
    $assignment = StaffAssignment::factory()
        ->for($staffMember)
        ->for($organizationUnit)
        ->forRole(RoleName::InventoryOfficer)
        ->create();

    session(['operations.assignment' => $assignment->id]);
    expect($staffMember->can(PermissionName::ManageInventory->value))->toBeTrue();

    $assignment = app(SetStaffAssignmentStatus::class)->handle(
        actor: $actor,
        assignment: $assignment,
        status: StaffAssignmentStatus::Revoked,
        reason: 'Assignment ended after the approved rotation changed.',
    );

    expect($assignment->status)->toBe(StaffAssignmentStatus::Revoked)
        ->and($assignment->revoked_by)->toBe($actor->id)
        ->and($assignment->revoked_at)->not->toBeNull()
        ->and($staffMember->can(PermissionName::ManageInventory->value))->toBeFalse();
});

test('hospital profiles cannot be assigned to blood center units', function () {
    $actor = User::factory()->superAdmin()->create();
    $staffMember = User::factory()->staff()->create();
    $centerUnit = OrganizationUnit::factory()->create(['type' => OrganizationUnitType::BloodCenter]);

    expect(fn () => app(CreateStaffAssignment::class)->handle(
        actor: $actor,
        staffMember: $staffMember,
        roleName: RoleName::HospitalBloodBankOfficer,
        organizationUnit: $centerUnit,
        reason: 'Hospital authority must remain inside a hospital unit.',
    ))->toThrow(ValidationException::class);
});

test('the compatibility backfill is dry-run safe and idempotent', function () {
    $bloodCenter = BloodCenter::factory()->create();
    $staffMember = User::factory()->centerManager()->create();

    CenterStaff::factory()->manager()->create([
        'user_id' => $staffMember,
        'blood_center_id' => $bloodCenter,
    ]);
    $this->seed(OrganizationStructureSeeder::class);

    $this->artisan('nbts:backfill-staff-assignments', ['--dry-run' => true])->assertSuccessful();
    expect(StaffAssignment::query()->count())->toBe(0);

    $this->artisan('nbts:backfill-staff-assignments')->assertSuccessful();
    $this->artisan('nbts:backfill-staff-assignments')->assertSuccessful();

    expect(StaffAssignment::query()->count())->toBe(1)
        ->and(StaffAssignment::query()->sole()->organization_unit_id)->toBe($bloodCenter->fresh()->organization_unit_id);
});
