<?php

use App\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function donorApiToken(User $donor, array $abilities = ['donor:read', 'donor:write']): string
{
    return $donor->createToken('Appointment Phone', $abilities)->plainTextToken;
}

test('a donor can book a configured slot using the legacy center alias', function () {
    $center = BloodCenter::factory()->create();
    $donor = User::factory()->donor()->create();
    $token = donorApiToken($donor);
    $scheduledAt = now()->addDay()->startOfDay()->setTime(8, 0);

    $this->withToken($token)->postJson(route('api.v1.appointments.store'), [
        'center_id' => $center->id,
        'scheduled_at' => $scheduledAt->toIso8601String(),
        'notes' => 'Morning appointment',
    ])->assertCreated()
        ->assertJsonPath('data.center_id', $center->id)
        ->assertJsonPath('data.center_name', $center->name)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.notes', 'Morning appointment');

    $appointment = Appointment::query()->sole();

    expect($appointment->user_id)->toBe($donor->id)
        ->and($appointment->status)->toBe(AppointmentStatus::Pending)
        ->and(AuditLog::query()->where('action', 'appointments.booked')->count())->toBe(1);
});

test('booking prevents a second active appointment and full center slot', function () {
    $center = BloodCenter::factory()->create();
    $firstDonor = User::factory()->donor()->create();
    $secondDonor = User::factory()->donor()->create();
    $scheduledAt = now()->addDay()->startOfDay()->setTime(9, 30);
    Appointment::factory()->create([
        'user_id' => $firstDonor,
        'blood_center_id' => $center,
        'scheduled_at' => $scheduledAt,
        'status' => AppointmentStatus::Pending,
    ]);

    $this->withToken(donorApiToken($secondDonor))->postJson(route('api.v1.appointments.store'), [
        'blood_center_id' => $center->id,
        'scheduled_at' => $scheduledAt->toIso8601String(),
    ])->assertUnprocessable()->assertJsonValidationErrors('scheduled_at');
    $this->app['auth']->forgetGuards();

    $this->withToken(donorApiToken($firstDonor))->postJson(route('api.v1.appointments.store'), [
        'blood_center_id' => $center->id,
        'scheduled_at' => $scheduledAt->addHours(3)->toIso8601String(),
    ])->assertUnprocessable()->assertJsonValidationErrors('scheduled_at');

    expect(Appointment::query()->count())->toBe(1)
        ->and(AuditLog::query()->exists())->toBeFalse();
});

test('booking rejects inactive centers invalid times and dates outside the window', function () {
    $center = BloodCenter::factory()->create();
    $inactiveCenter = BloodCenter::factory()->inactive()->create();
    $donor = User::factory()->donor()->create();
    $token = donorApiToken($donor);
    $tomorrow = now()->addDay()->startOfDay();

    foreach ([
        [$inactiveCenter->id, $tomorrow->setTime(8, 0)],
        [$center->id, $tomorrow->setTime(8, 15)],
        [$center->id, now()->addDays(91)->startOfDay()->setTime(8, 0)],
    ] as [$centerId, $scheduledAt]) {
        $this->withToken($token)->postJson(route('api.v1.appointments.store'), [
            'blood_center_id' => $centerId,
            'scheduled_at' => $scheduledAt->toIso8601String(),
        ])->assertUnprocessable();

        $this->app['auth']->forgetGuards();
    }

    expect(Appointment::query()->count())->toBe(0);
});

test('a donor can list view and retrieve the nearest upcoming appointment', function () {
    $center = BloodCenter::factory()->create();
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $token = donorApiToken($donor, ['donor:read']);
    $nearest = Appointment::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'scheduled_at' => now()->addDay()->startOfDay()->setTime(8, 0),
        'status' => AppointmentStatus::Confirmed,
    ]);
    Appointment::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'scheduled_at' => now()->addDays(2)->startOfDay()->setTime(8, 0),
    ]);
    $otherAppointment = Appointment::factory()->create([
        'user_id' => $otherDonor,
        'blood_center_id' => $center,
    ]);

    $this->withToken($token)->getJson(route('api.v1.appointments.index'))
        ->assertOk()
        ->assertJsonCount(2, 'data');
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson(route('api.v1.appointments.upcoming'))
        ->assertOk()
        ->assertJsonPath('data.id', $nearest->id);
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson(route('api.v1.appointments.show', $nearest))
        ->assertOk()
        ->assertJsonPath('data.id', $nearest->id);
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson(route('api.v1.appointments.show', $otherAppointment))
        ->assertForbidden();
});

test('a donor can reschedule a confirmed appointment and then cancel it', function () {
    $firstCenter = BloodCenter::factory()->create();
    $secondCenter = BloodCenter::factory()->create();
    $donor = User::factory()->donor()->create();
    $token = donorApiToken($donor);
    $appointment = Appointment::factory()->confirmed()->create([
        'user_id' => $donor,
        'blood_center_id' => $firstCenter,
        'scheduled_at' => now()->addDay()->startOfDay()->setTime(8, 0),
    ]);
    $newTime = now()->addDays(2)->startOfDay()->setTime(11, 0);

    $this->withToken($token)->putJson(route('api.v1.appointments.update', $appointment), [
        'center_id' => $secondCenter->id,
        'scheduled_at' => $newTime->toIso8601String(),
        'notes' => 'Changed center',
    ])->assertOk()
        ->assertJsonPath('data.center_id', $secondCenter->id)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.notes', 'Changed center');

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Pending)
        ->and($appointment->confirmed_at)->toBeNull()
        ->and($appointment->rescheduled_at)->not->toBeNull();

    $this->app['auth']->forgetGuards();
    $this->withToken($token)->postJson(route('api.v1.appointments.cancel', $appointment))
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');

    expect($appointment->refresh()->cancelled_at)->not->toBeNull()
        ->and(AuditLog::query()->where('action', 'appointments.rescheduled')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'appointments.cancelled')->count())->toBe(1);

    $this->app['auth']->forgetGuards();
    $this->withToken($token)->postJson(route('api.v1.appointments.cancel', $appointment))
        ->assertForbidden();
});

test('appointment mutations require donor write ability and ownership', function () {
    $center = BloodCenter::factory()->create();
    $owner = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $appointment = Appointment::factory()->create([
        'user_id' => $owner,
        'blood_center_id' => $center,
        'scheduled_at' => now()->addDay()->startOfDay()->setTime(13, 0),
    ]);

    $this->withToken(donorApiToken($owner, ['donor:read']))
        ->postJson(route('api.v1.appointments.cancel', $appointment))
        ->assertForbidden();
    $this->app['auth']->forgetGuards();

    $this->withToken(donorApiToken($otherDonor))
        ->postJson(route('api.v1.appointments.cancel', $appointment))
        ->assertForbidden();

    expect($appointment->refresh()->status)->toBe(AppointmentStatus::Pending);
});
