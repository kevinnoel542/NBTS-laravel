<?php

use App\BloodGroup;
use App\DonationStatus;
use App\DonationType;
use App\EligibilityStatus;
use App\Models\BloodCenter;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\User;
use App\Services\DonorCardQrService;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function donorJourneyApiToken(User $donor, array $abilities = ['donor:read', 'donor:write']): string
{
    return $donor->createToken('Donor Journey Phone', $abilities)->plainTextToken;
}

test('a donor can retrieve a compatible card with a short lived verifiable qr payload', function () {
    $this->travelTo(now()->startOfMinute());
    $center = BloodCenter::factory()->create(['name' => 'Mwananyamala Blood Center']);
    $donor = User::factory()->donor()->create([
        'name' => 'Asha Mrema',
        'phone' => '+255712345678',
        'blood_group' => BloodGroup::OPositive,
        'region' => 'Dar es Salaam',
    ]);
    $profile = DonorProfile::factory()->create([
        'user_id' => $donor,
        'preferred_center_id' => $center,
        'donor_id' => 'DNR-2026-ASHA0001',
        'next_eligible_donation_date' => today()->addMonth(),
        'eligibility_status' => EligibilityStatus::NotYetEligible,
        'loyalty_points' => 250,
        'loyalty_tier' => 'Silver',
    ]);
    Donation::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'donation_date' => today()->subMonths(6),
        'volume_ml' => 450,
    ]);
    Donation::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'donation_date' => today()->subMonths(2),
        'volume_ml' => 500,
    ]);
    Donation::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'status' => DonationStatus::Failed,
        'donation_date' => today()->subMonth(),
        'volume_ml' => 450,
    ]);

    $response = $this->withToken(donorJourneyApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.donor-card.show'))
        ->assertOk()
        ->assertJsonPath('data.donor_id', 'DNR-2026-ASHA0001')
        ->assertJsonPath('data.name', 'Asha Mrema')
        ->assertJsonPath('data.blood_group', 'O+')
        ->assertJsonPath('data.donor.preferred_center', 'Mwananyamala Blood Center')
        ->assertJsonPath('data.stats.total_donations', 2)
        ->assertJsonPath('data.stats.total_volume_ml', 950)
        ->assertJsonPath('data.stats.eligibility_status', 'not_yet_eligible')
        ->assertJsonPath('data.stats.loyalty_tier', 'Silver');

    $qrPayload = $response->json('data.qr_payload');

    expect($qrPayload)->toBeString()->toStartWith('nbtsqr.')
        ->and($response->json('data.qr_expires_at'))->toBe(now()->addMinutes(5)->toIso8601String())
        ->and(app(DonorCardQrService::class)->verify($qrPayload)->is($profile))->toBeTrue();
});

test('a donor card request safely creates a missing legacy donor profile', function () {
    $donor = User::factory()->donor()->create();

    $response = $this->withToken(donorJourneyApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.donor-card.show'))
        ->assertOk();

    expect($response->json('data.donor_id'))->toBeString()->toStartWith('DNR-')
        ->and($donor->donorProfile()->exists())->toBeTrue();
});

test('eligibility reflects active deferrals and keeps clinical screening explicit', function () {
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'user_id' => $donor,
        'eligibility_status' => EligibilityStatus::Eligible,
    ]);
    Deferral::factory()->create([
        'user_id' => $donor,
        'reason' => 'Temporary medication deferral',
        'starts_at' => today()->subDay(),
        'ends_at' => today()->addDays(14),
    ]);

    $this->withToken(donorJourneyApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.eligibility.show'))
        ->assertOk()
        ->assertJsonPath('data.status', 'temporarily_deferred')
        ->assertJsonPath('data.eligible', false)
        ->assertJsonPath('data.reasons.0', 'Temporary medication deferral')
        ->assertJsonPath('data.next_eligible_donation_date', today()->addDays(14)->toDateString())
        ->assertJsonPath('data.clinical_screening_required', true);
});

test('eligibility interval guidance is localized without translating stable codes', function () {
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'user_id' => $donor,
        'eligibility_status' => EligibilityStatus::Eligible,
        'next_eligible_donation_date' => today()->addMonth(),
    ]);

    $this->withHeader('X-Locale', 'sw')
        ->withToken(donorJourneyApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.eligibility.show'))
        ->assertOk()
        ->assertJsonPath('data.status', 'not_yet_eligible')
        ->assertJsonPath('data.eligible', false)
        ->assertJsonPath(
            'data.message',
            'Muda wa chini unaotakiwa tangu uchangiaji uliopita bado haujatimia.',
        );
});

test('a donor sees only their paginated donation history in flutter compatible fields', function () {
    $center = BloodCenter::factory()->create(['name' => 'Temeke Blood Center']);
    $donor = User::factory()->donor()->create();
    $otherDonor = User::factory()->donor()->create();
    $oldest = Donation::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'donation_date' => today()->subMonths(8),
    ]);
    $newest = Donation::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'donation_type' => DonationType::Appointment,
        'donation_date' => today()->subMonth(),
    ]);
    Donation::factory()->create([
        'user_id' => $otherDonor,
        'blood_center_id' => $center,
        'donation_date' => today(),
    ]);

    $this->withToken(donorJourneyApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.donations.index', ['per_page' => 1]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $newest->id)
        ->assertJsonPath('data.0.center_name', 'Temeke Blood Center')
        ->assertJsonPath('data.0.blood_group', 'O+')
        ->assertJsonPath('data.0.blood_type', 'O+')
        ->assertJsonPath('data.0.donation_type', 'appointment')
        ->assertJsonPath('data.0.donation_date', today()->subMonth()->toDateString())
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.per_page', 1);

    expect($oldest->id)->not->toBe($newest->id);
});

test('donation summary uses completed donor records as the source of truth', function () {
    $center = BloodCenter::factory()->create();
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'user_id' => $donor,
        'total_donations' => 99,
    ]);
    Donation::factory()->count(2)->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'volume_ml' => 450,
    ]);
    Donation::factory()->create([
        'user_id' => $donor,
        'blood_center_id' => $center,
        'status' => DonationStatus::Failed,
        'volume_ml' => 800,
    ]);

    $this->withToken(donorJourneyApiToken($donor, ['donor:read']))
        ->getJson(route('api.v1.donations.summary'))
        ->assertOk()
        ->assertJsonPath('data.total_donations', 2)
        ->assertJsonPath('data.total_volume_ml', 900)
        ->assertJsonPath('data.total_volume_liters', 0.9)
        ->assertJsonPath('data.lives_touched', 6)
        ->assertJsonPath('data.lives_touched_is_estimate', true);
});

test('donor journey endpoints require a donor read token and donor role', function () {
    $donor = User::factory()->donor()->create();
    $staff = User::factory()->staff()->create();

    $this->getJson(route('api.v1.donor-card.show'))->assertUnauthorized();

    $this->withToken(donorJourneyApiToken($donor, ['donor:write']))
        ->getJson(route('api.v1.eligibility.show'))
        ->assertForbidden();

    $this->app['auth']->forgetGuards();

    $this->withToken(donorJourneyApiToken($staff, ['donor:read']))
        ->getJson(route('api.v1.donations.index'))
        ->assertForbidden();
});
