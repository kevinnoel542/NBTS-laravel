<?php

use App\AppointmentStatus;
use App\BloodGroup;
use App\BloodGroupStatus;
use App\BloodUnitStatus;
use App\DonationType;
use App\EligibilityStatus;
use App\LowStockAlertStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\BloodUnit;
use App\Models\CenterStaff;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\InventoryAdjustment;
use App\Models\LowStockAlert;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('deployed status codes are represented by backed enums with explicit transitions', function () {
    expect(BloodGroup::values())->toHaveCount(8)
        ->and(AppointmentStatus::Pending->canTransitionTo(AppointmentStatus::Confirmed))->toBeTrue()
        ->and(AppointmentStatus::Pending->canTransitionTo(AppointmentStatus::Completed))->toBeFalse()
        ->and(BloodUnitStatus::Collected->canTransitionTo(BloodUnitStatus::Testing))->toBeTrue()
        ->and(BloodUnitStatus::Testing->canTransitionTo(BloodUnitStatus::Available))->toBeTrue()
        ->and(BloodUnitStatus::Used->canTransitionTo(BloodUnitStatus::Available))->toBeFalse()
        ->and(LowStockAlertStatus::Resolved->isActive())->toBeFalse();
});

test('the donor to inventory relationship chain uses typed deployed records', function () {
    $donor = User::factory()->donor()->create();
    $staff = User::factory()->staff()->create();
    $center = BloodCenter::factory()->create();
    $profile = DonorProfile::factory()->create([
        'user_id' => $donor,
        'preferred_center_id' => $center,
        'blood_group_status' => BloodGroupStatus::StaffVerified,
    ]);
    $appointment = Appointment::factory()->confirmed()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'handled_by' => $staff,
    ]);
    $eligibility = EligibilityRecord::factory()->create([
        'user_id' => $donor,
        'checked_by' => $staff,
    ]);
    $donation = Donation::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'recorded_by' => $staff,
        'appointment_id' => $appointment,
        'donation_type' => DonationType::Appointment,
    ]);
    $bloodUnit = BloodUnit::factory()->create([
        'donation_id' => $donation,
        'donor_id' => $donor,
        'blood_center_id' => $center,
        'handled_by' => $staff,
        'blood_group' => BloodGroup::OPositive,
    ]);
    $adjustment = InventoryAdjustment::factory()->create([
        'blood_center_id' => $center,
        'blood_unit_id' => $bloodUnit,
        'adjusted_by' => $staff,
    ]);

    expect($donor->donorProfile->is($profile))->toBeTrue()
        ->and($appointment->donor->is($donor))->toBeTrue()
        ->and($appointment->donation->is($donation))->toBeTrue()
        ->and($eligibility->status)->toBe(EligibilityStatus::Eligible)
        ->and($donation->bloodUnit->is($bloodUnit))->toBeTrue()
        ->and($bloodUnit->inventoryAdjustments->first()->is($adjustment))->toBeTrue()
        ->and($profile->blood_group_status)->toBe(BloodGroupStatus::StaffVerified);
});

test('operational scopes expose only actively assigned center records', function () {
    $assignedCenter = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();

    CenterStaff::factory()->create([
        'user_id' => $staff,
        'blood_center_id' => $assignedCenter,
    ]);

    Appointment::factory()->create(['blood_center_id' => $assignedCenter]);
    Appointment::factory()->create(['blood_center_id' => $otherCenter]);
    Donation::factory()->create(['blood_center_id' => $assignedCenter]);
    Donation::factory()->create(['blood_center_id' => $otherCenter]);
    BloodInventory::factory()->create(['blood_center_id' => $assignedCenter]);
    BloodInventory::factory()->create(['blood_center_id' => $otherCenter]);

    expect(Appointment::query()->visibleTo($staff)->count())->toBe(1)
        ->and(Donation::query()->visibleTo($staff)->count())->toBe(1)
        ->and(BloodInventory::query()->visibleTo($staff)->count())->toBe(1);
});

test('deferral and inventory helpers return stable operational codes', function () {
    Deferral::factory()->create([
        'starts_at' => today()->subDay(),
        'ends_at' => today()->addDay(),
    ]);
    Deferral::factory()->create([
        'starts_at' => today()->subDays(5),
        'ends_at' => today()->subDay(),
    ]);
    $inventory = BloodInventory::factory()->create([
        'available_units' => 2,
        'reserved_units' => 1,
        'minimum_threshold' => 5,
    ]);
    $alert = LowStockAlert::factory()->create([
        'available_units' => 2,
        'minimum_threshold' => 5,
    ]);
    $decrease = InventoryAdjustment::factory()->create(['quantity_delta' => -1]);

    expect(Deferral::query()->effectiveOn()->count())->toBe(1)
        ->and($inventory->totalUnits())->toBe(3)
        ->and($inventory->stockGap())->toBe(3)
        ->and($inventory->stockStatus())->toBe('low')
        ->and($alert->severity())->toBe('high')
        ->and($decrease->direction())->toBe('decrease');
});
