<?php

use App\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;

test('the center directory exposes only active centers with Flutter compatibility fields', function () {
    $matchingCenter = BloodCenter::factory()->create([
        'name' => 'Arusha National Blood Centre',
        'city' => 'Arusha',
        'phone' => '+255700100100',
        'opening_hours' => '08:00-17:00',
        'services' => ['Whole blood', 'Platelets'],
        'estimated_wait_minutes' => 15,
    ]);
    BloodCenter::factory()->create([
        'name' => 'Dar Centre',
        'city' => 'Dar es Salaam',
        'services' => ['Whole blood'],
    ]);
    BloodCenter::factory()->inactive()->create(['name' => 'Closed Arusha Centre']);

    $this->getJson(route('api.v1.blood-centers.index', [
        'q' => 'Arusha',
        'service' => 'Platelets',
    ]))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $matchingCenter->id)
        ->assertJsonPath('data.0.center_id', $matchingCenter->id)
        ->assertJsonPath('data.0.phone_number', '+255700100100')
        ->assertJsonPath('data.0.hours', '08:00-17:00')
        ->assertJsonPath('data.0.wait_time', '15 minutes')
        ->assertJsonPath('data.0.is_open', true)
        ->assertJsonPath('meta.total', 1);
});

test('active center details are public while inactive center details are hidden', function () {
    $activeCenter = BloodCenter::factory()->create();
    $inactiveCenter = BloodCenter::factory()->inactive()->create();

    $this->getJson(route('api.v1.blood-centers.show', $activeCenter))
        ->assertOk()
        ->assertJsonPath('data.id', $activeCenter->id);

    $this->getJson(route('api.v1.blood-centers.show', $inactiveCenter))->assertNotFound();
});

test('available slots expose stable aliases and account for current bookings', function () {
    $center = BloodCenter::factory()->create();
    $date = now()->addDay()->startOfDay();
    Appointment::factory()->create([
        'blood_center_id' => $center,
        'scheduled_at' => $date->setTime(9, 30),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $this->getJson(route('api.v1.blood-centers.available-slots', [
        'bloodCenter' => $center,
        'date' => $date->toDateString(),
    ]))->assertOk()
        ->assertJsonCount(6, 'data')
        ->assertJsonPath('data.0.time', '08:00')
        ->assertJsonPath('data.0.available', true)
        ->assertJsonPath('data.1.slot_time', '09:30')
        ->assertJsonPath('data.1.is_available', false)
        ->assertJsonPath('data.1.reason_code', 'full')
        ->assertJsonPath('data.1.reason', 'This appointment time is full.');
});

test('slot dates must be within the configured booking window', function () {
    $center = BloodCenter::factory()->create();

    $this->getJson(route('api.v1.blood-centers.available-slots', [
        'bloodCenter' => $center,
        'date' => now()->subDay()->toDateString(),
    ]))->assertUnprocessable()->assertJsonValidationErrors('date');

    $this->getJson(route('api.v1.blood-centers.available-slots', [
        'bloodCenter' => $center,
        'date' => now()->addDays(91)->toDateString(),
    ]))->assertUnprocessable()->assertJsonValidationErrors('date');

});
