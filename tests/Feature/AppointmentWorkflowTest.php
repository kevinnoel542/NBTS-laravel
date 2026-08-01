<?php

use App\Actions\Appointments\TransitionAppointment;
use App\AppointmentStatus;
use App\Exceptions\InvalidAppointmentTransition;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('an assigned manager can confirm and complete an appointment with chained audit evidence', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $appointment = Appointment::factory()->create(['blood_center_id' => $center]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $action = app(TransitionAppointment::class);
    $confirmedAppointment = $action->execute(
        appointment: $appointment,
        status: AppointmentStatus::Confirmed,
        actor: $manager,
        notes: 'Donor attendance confirmed.',
    );
    $completedAppointment = $action->execute(
        appointment: $confirmedAppointment,
        status: AppointmentStatus::Completed,
        actor: $manager,
    );
    $auditLogs = AuditLog::query()->orderBy('id')->get();

    expect($confirmedAppointment->status)->toBe(AppointmentStatus::Confirmed)
        ->and($confirmedAppointment->confirmed_at)->not->toBeNull()
        ->and($completedAppointment->status)->toBe(AppointmentStatus::Completed)
        ->and($completedAppointment->handled_by)->toBe($manager->id)
        ->and($auditLogs)->toHaveCount(2)
        ->and($auditLogs->first()->metadata)->toMatchArray([
            'from_status' => 'pending',
            'to_status' => 'confirmed',
        ])
        ->and($auditLogs->last()->previous_hash)->toBe($auditLogs->first()->record_hash)
        ->and($auditLogs->last()->record_hash)->not->toBe($auditLogs->first()->record_hash);
});

test('appointment transitions reject staff without management permission or center scope', function () {
    $center = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $centerStaff = User::factory()->staff()->create();
    $otherManager = User::factory()->centerManager()->create();
    $appointment = Appointment::factory()->create(['blood_center_id' => $center]);

    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $centerStaff,
    ]);
    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $otherCenter,
        'user_id' => $otherManager,
    ]);

    $action = app(TransitionAppointment::class);

    expect(fn () => $action->execute($appointment, AppointmentStatus::Confirmed, $centerStaff))
        ->toThrow(AuthorizationException::class)
        ->and(fn () => $action->execute($appointment, AppointmentStatus::Confirmed, $otherManager))
        ->toThrow(AuthorizationException::class)
        ->and($appointment->fresh()->status)->toBe(AppointmentStatus::Pending)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('invalid appointment transitions roll back without audit evidence', function () {
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $appointment = Appointment::factory()->create(['blood_center_id' => $center]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    expect(fn () => app(TransitionAppointment::class)->execute(
        $appointment,
        AppointmentStatus::Completed,
        $manager,
    ))->toThrow(InvalidAppointmentTransition::class)
        ->and($appointment->fresh()->status)->toBe(AppointmentStatus::Pending)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('audit records cannot be changed or deleted through the model', function () {
    $auditLog = AuditLog::factory()->create();

    expect(fn () => $auditLog->forceFill(['action' => 'changed'])->save())
        ->toThrow(LogicException::class)
        ->and(fn () => $auditLog->delete())
        ->toThrow(LogicException::class);
});

test('donors can view only their own appointments while staff views assigned center records', function () {
    $center = BloodCenter::factory()->create();
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $manager = User::factory()->centerManager()->create();
    $appointment = Appointment::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $donor,
    ]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    expect(Gate::forUser($donor)->allows('view', $appointment))->toBeTrue()
        ->and(Gate::forUser($otherDonor)->denies('view', $appointment))->toBeTrue()
        ->and(Gate::forUser($manager)->allows('view', $appointment))->toBeTrue();
});
