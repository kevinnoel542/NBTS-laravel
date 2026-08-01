<?php

use App\Models\DonorProfile;
use App\Models\User;
use App\Services\DonorCardQrService;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->travelTo(CarbonImmutable::parse('2026-08-01 10:00:00'));
});

test('the qr service verifies an unmodified current donor card payload', function () {
    $donor = User::factory()->donor()->create();
    $profile = DonorProfile::factory()->create(['user_id' => $donor]);
    $service = app(DonorCardQrService::class);
    $issued = $service->issue($profile);

    expect($service->verify($issued['payload'])->is($profile))->toBeTrue()
        ->and($issued['expires_at']->toIso8601String())->toBe(now()->addMinutes(5)->toIso8601String());
});

test('the qr service rejects tampered payloads', function () {
    $donor = User::factory()->donor()->create();
    $profile = DonorProfile::factory()->create(['user_id' => $donor]);
    $service = app(DonorCardQrService::class);
    $issued = $service->issue($profile);
    $parts = explode('.', $issued['payload']);
    $parts[1] = substr($parts[1], 0, -1).($parts[1][-1] === 'A' ? 'B' : 'A');

    try {
        $service->verify(implode('.', $parts));
        $this->fail('A tampered QR payload was accepted.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['qr_payload'][0])->toBe(__('api.donor_card_qr_invalid'));
    }
});

test('the qr service rejects expired and inactive donor payloads', function () {
    $donor = User::factory()->donor()->create();
    $profile = DonorProfile::factory()->create(['user_id' => $donor]);
    $service = app(DonorCardQrService::class);
    $issued = $service->issue($profile, now()->addMinute());

    $this->travel(61)->seconds();

    try {
        $service->verify($issued['payload']);
        $this->fail('An expired QR payload was accepted.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['qr_payload'][0])->toBe(__('api.donor_card_qr_expired'));
    }

    $this->travelBack();
    $activePayload = $service->issue($profile)['payload'];
    $donor->forceFill(['is_active' => false])->save();

    try {
        $service->verify($activePayload);
        $this->fail('An inactive donor QR payload was accepted.');
    } catch (ValidationException $exception) {
        expect($exception->errors()['qr_payload'][0])->toBe(__('api.donor_card_qr_not_found'));
    }
});
