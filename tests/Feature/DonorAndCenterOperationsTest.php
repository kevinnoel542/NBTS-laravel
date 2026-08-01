<?php

use App\Actions\Centers\AssignCenterStaff;
use App\Actions\Centers\SaveBloodCenter;
use App\Actions\Centers\SetCenterStaffStatus;
use App\Actions\Donors\CreateDonorAtCenter;
use App\Actions\Profile\UpdateMobileDonorProfile;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\DonorProfile;
use App\Models\User;
use App\RoleName;
use App\Services\DonorCardQrService;
use App\Services\DonorSearchService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('assigned reception staff can create a donor with a unique profile and preferred center', function () {
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();
    CenterStaff::factory()->create([
        'user_id' => $staff,
        'blood_center_id' => $center,
    ]);

    $donor = app(CreateDonorAtCenter::class)->handle($staff, $center, [
        'name' => 'Zawadi Mushi',
        'phone' => '+255713000999',
        'email' => 'ZAWADI@example.test',
        'gender' => 'female',
        'date_of_birth' => '1998-06-15',
        'region' => 'Arusha',
        'locale' => 'sw',
    ]);

    expect($donor->hasRole(RoleName::Donor->value))->toBeTrue()
        ->and($donor->email)->toBe('zawadi@example.test')
        ->and($donor->donorProfile)->not->toBeNull()
        ->and($donor->donorProfile->donor_id)->toMatch('/^DNR-\d{4}-[A-Z0-9]{8}$/')
        ->and($donor->donorProfile->preferred_center_id)->toBe($center->id)
        ->and(AuditLog::query()->where('action', 'donor.registered_at_center')->count())->toBe(1);

    expect(fn () => app(CreateDonorAtCenter::class)->handle($staff, $center, [
        'name' => 'Duplicate Donor',
        'phone' => '+255713000999',
    ]))->toThrow(ValidationException::class);
});

test('donor text search is center scoped while a signed qr supports presented-donor lookup', function () {
    $assignedCenter = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();
    CenterStaff::factory()->create([
        'user_id' => $staff,
        'blood_center_id' => $assignedCenter,
    ]);
    $linkedDonor = User::factory()->donor()->create([
        'name' => 'Amina Mhando',
        'email' => 'amina@example.test',
        'phone' => '+255754123456',
    ]);
    $linkedProfile = DonorProfile::factory()->create([
        'user_id' => $linkedDonor,
        'preferred_center_id' => $assignedCenter,
        'donor_id' => 'DNR-2026-AMINA001',
    ]);
    $unlinkedDonor = User::factory()->donor()->create(['name' => 'Unlinked Presented Donor']);
    $unlinkedProfile = DonorProfile::factory()->create([
        'user_id' => $unlinkedDonor,
        'preferred_center_id' => $otherCenter,
    ]);
    $search = app(DonorSearchService::class);

    foreach (['Amina', 'amina@example.test', '754123456', $linkedProfile->donor_id] as $term) {
        expect($search->search($staff, $term, $assignedCenter)->modelKeys())->toBe([$linkedDonor->id]);
    }

    expect($search->search($staff, 'Unlinked', $assignedCenter))->toBeEmpty();

    $payload = app(DonorCardQrService::class)->issue($unlinkedProfile)['payload'];

    expect($search->search($staff, $payload, $assignedCenter)->modelKeys())->toBe([$unlinkedDonor->id])
        ->and(fn () => $search->search($staff, 'Amina', $otherCenter))->toThrow(AuthorizationException::class);
});

test('donors can select only an active preferred center through the shared profile action', function () {
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create(['user_id' => $donor]);
    $activeCenter = BloodCenter::factory()->create();

    $updated = app(UpdateMobileDonorProfile::class)->handle($donor, [
        'preferred_center_id' => $activeCenter->id,
    ]);

    expect($updated->donorProfile->preferred_center_id)->toBe($activeCenter->id)
        ->and(AuditLog::query()->where('action', 'mobile.profile_updated')->count())->toBe(1);
});

test('national administrators manage centers and manager assignments are nationally restricted', function () {
    $admin = User::factory()->nbtsAdmin()->create();
    $manager = User::factory()->centerManager()->create();
    $candidate = User::factory()->staff()->create();
    $center = app(SaveBloodCenter::class)->handle($admin, [
        'name' => 'Arusha Regional Blood Centre',
        'address' => 'Sokoine Road, Arusha',
        'city' => 'Arusha',
        'phone' => '+255272500001',
        'email' => 'arusha@nbts.go.tz',
        'opening_hours' => 'Mon - Fri 08:00 - 17:00',
        'services' => ['Whole blood', 'Donor screening'],
        'is_active' => true,
    ]);

    $managerAssignment = app(AssignCenterStaff::class)->handle(
        $admin,
        $center,
        $manager,
        RoleName::CenterManager,
    );

    expect($center->is_active)->toBeTrue()
        ->and($managerAssignment->position)->toBe(RoleName::CenterManager->value)
        ->and($manager->fresh()->hasRole(RoleName::CenterManager->value))->toBeTrue();

    expect(fn () => app(AssignCenterStaff::class)->handle(
        $manager,
        $center,
        $candidate,
        RoleName::CenterManager,
    ))->toThrow(AuthorizationException::class);

    $staffAssignment = app(AssignCenterStaff::class)->handle(
        $manager,
        $center,
        $candidate,
        RoleName::CenterStaff,
    );

    expect($candidate->fresh()->hasRole(RoleName::CenterStaff->value))->toBeTrue()
        ->and(AuditLog::query()->where('action', 'center_staff.assigned')->count())->toBe(2);

    app(SetCenterStaffStatus::class)->handle($manager, $staffAssignment, false);

    expect($staffAssignment->fresh()->is_active)->toBeFalse()
        ->and($candidate->fresh()->roles)->toBeEmpty()
        ->and(AuditLog::query()->where('action', 'center_staff.deactivated')->count())->toBe(1);

    expect(fn () => app(SetCenterStaffStatus::class)->handle($manager, $managerAssignment, false))
        ->toThrow(AuthorizationException::class);

    $updatedCenter = app(SaveBloodCenter::class)->handle($admin, ['is_active' => false], $center);

    expect($updatedCenter->is_active)->toBeFalse()
        ->and(AuditLog::query()->whereIn('action', ['blood_center.created', 'blood_center.updated'])->count())->toBe(2);
});
