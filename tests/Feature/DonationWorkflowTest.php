<?php

use App\Actions\Donations\RecordDonation;
use App\AppointmentStatus;
use App\BloodGroup;
use App\BloodUnitStatus;
use App\Data\RecordDonationData;
use App\DonationType;
use App\EligibilityStatus;
use App\Gender;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\CenterStaff;
use App\Models\Deferral;
use App\Models\Donation;
use App\Models\DonorProfile;
use App\Models\EligibilityRecord;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('appointment donation completion updates donor history and creates a collected unit atomically', function () {
    $donationDate = CarbonImmutable::today();
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $donor = createDonationReadyDonor(Gender::Male, $donationDate);
    $appointment = Appointment::factory()->confirmed()->create([
        'blood_center_id' => $center,
        'user_id' => $donor,
    ]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $donation = app(RecordDonation::class)->execute(new RecordDonationData(
        donorId: $donor->id,
        bloodCenterId: $center->id,
        donationType: DonationType::Appointment,
        bloodGroup: BloodGroup::OPositive,
        volumeMl: 450,
        donationDate: $donationDate,
        bloodGroupVerified: true,
        appointmentId: $appointment->id,
        notes: 'Whole blood donation completed.',
    ), $manager);

    $donor->refresh();
    $profile = $donor->donorProfile;
    $bloodUnit = $donation->bloodUnit;

    expect($donation->appointment->status)->toBe(AppointmentStatus::Completed)
        ->and($donation->recorded_by)->toBe($manager->id)
        ->and($donor->blood_group)->toBe(BloodGroup::OPositive)
        ->and($donor->last_donation?->toDateString())->toBe($donationDate->toDateString())
        ->and($profile->eligibility_status)->toBe(EligibilityStatus::NotYetEligible)
        ->and($profile->next_eligible_donation_date?->toDateString())
        ->toBe($donationDate->addMonthsNoOverflow(3)->toDateString())
        ->and($profile->total_donations)->toBe(1)
        ->and($bloodUnit->status)->toBe(BloodUnitStatus::Collected)
        ->and($bloodUnit->expiry_date->toDateString())->toBe($donationDate->addDays(35)->toDateString())
        ->and(BloodInventory::query()->count())->toBe(0)
        ->and(AuditLog::query()->where('action', 'donations.completed')->count())->toBe(1);
});

test('walk in donation uses the official four month interval for female donors', function () {
    $donationDate = CarbonImmutable::today();
    $center = BloodCenter::factory()->create();
    $staff = User::factory()->staff()->create();
    $donor = createDonationReadyDonor(Gender::Female, $donationDate);

    CenterStaff::factory()->create([
        'blood_center_id' => $center,
        'user_id' => $staff,
    ]);

    $donation = app(RecordDonation::class)->execute(new RecordDonationData(
        donorId: $donor->id,
        bloodCenterId: $center->id,
        donationType: DonationType::WalkIn,
        bloodGroup: BloodGroup::APositive,
        volumeMl: 450,
        donationDate: $donationDate,
        bloodGroupVerified: true,
    ), $staff);

    expect($donation->appointment_id)->toBeNull()
        ->and($donor->refresh()->donorProfile->next_eligible_donation_date?->toDateString())
        ->toBe($donationDate->addMonthsNoOverflow(4)->toDateString());
});

test('donation recording rejects missing center scope and blood group verification', function () {
    $donationDate = CarbonImmutable::today();
    $center = BloodCenter::factory()->create();
    $otherCenter = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $donor = createDonationReadyDonor(Gender::Male, $donationDate);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $otherCenter,
        'user_id' => $manager,
    ]);

    $validData = new RecordDonationData(
        donorId: $donor->id,
        bloodCenterId: $center->id,
        donationType: DonationType::WalkIn,
        bloodGroup: BloodGroup::BPositive,
        volumeMl: 450,
        donationDate: $donationDate,
        bloodGroupVerified: true,
    );

    expect(fn () => app(RecordDonation::class)->execute($validData, $manager))
        ->toThrow(AuthorizationException::class);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $unverifiedData = new RecordDonationData(
        donorId: $donor->id,
        bloodCenterId: $center->id,
        donationType: DonationType::WalkIn,
        bloodGroup: BloodGroup::BPositive,
        volumeMl: 450,
        donationDate: $donationDate,
        bloodGroupVerified: false,
    );

    expect(fn () => app(RecordDonation::class)->execute($unverifiedData, $manager))
        ->toThrow(ValidationException::class)
        ->and(Donation::query()->count())->toBe(0)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('future eligibility dates active deferrals and missing same day screening block donation', function () {
    $donationDate = CarbonImmutable::today();
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $futureDonor = createDonationReadyDonor(Gender::Male, $donationDate);
    $futureDonor->donorProfile()->update([
        'next_eligible_donation_date' => $donationDate->addDay(),
    ]);

    $deferredDonor = createDonationReadyDonor(Gender::Male, $donationDate);
    Deferral::factory()->create([
        'user_id' => $deferredDonor,
        'starts_at' => $donationDate,
        'ends_at' => $donationDate->addMonth(),
    ]);

    $unscreenedDonor = User::factory()->donor()->create(['gender' => Gender::Male]);
    DonorProfile::factory()->create(['user_id' => $unscreenedDonor]);

    foreach ([$futureDonor, $deferredDonor, $unscreenedDonor] as $donor) {
        $data = new RecordDonationData(
            donorId: $donor->id,
            bloodCenterId: $center->id,
            donationType: DonationType::WalkIn,
            bloodGroup: BloodGroup::ONegative,
            volumeMl: 450,
            donationDate: $donationDate,
            bloodGroupVerified: true,
        );

        expect(fn () => app(RecordDonation::class)->execute($data, $manager))
            ->toThrow(ValidationException::class);
    }

    expect(Donation::query()->count())->toBe(0)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('an appointment can produce only one donation and one blood unit', function () {
    $donationDate = CarbonImmutable::today();
    $center = BloodCenter::factory()->create();
    $manager = User::factory()->centerManager()->create();
    $donor = createDonationReadyDonor(Gender::Male, $donationDate);
    $appointment = Appointment::factory()->confirmed()->create([
        'blood_center_id' => $center,
        'user_id' => $donor,
    ]);

    CenterStaff::factory()->manager()->create([
        'blood_center_id' => $center,
        'user_id' => $manager,
    ]);

    $data = new RecordDonationData(
        donorId: $donor->id,
        bloodCenterId: $center->id,
        donationType: DonationType::Appointment,
        bloodGroup: BloodGroup::AbPositive,
        volumeMl: 450,
        donationDate: $donationDate,
        bloodGroupVerified: true,
        appointmentId: $appointment->id,
    );

    app(RecordDonation::class)->execute($data, $manager);

    $appointment->forceFill(['status' => AppointmentStatus::Confirmed])->save();
    $donor->donorProfile()->update([
        'eligibility_status' => EligibilityStatus::Eligible,
        'next_eligible_donation_date' => null,
    ]);

    expect(fn () => app(RecordDonation::class)->execute($data, $manager))
        ->toThrow(ValidationException::class)
        ->and(Donation::query()->where('appointment_id', $appointment->id)->count())->toBe(1)
        ->and($appointment->donation->bloodUnit)->not->toBeNull();
});

function createDonationReadyDonor(Gender $gender, CarbonImmutable $donationDate): User
{
    $donor = User::factory()->donor()->create(['gender' => $gender]);

    DonorProfile::factory()->create([
        'eligibility_status' => EligibilityStatus::Eligible,
        'next_eligible_donation_date' => null,
        'user_id' => $donor,
    ]);
    EligibilityRecord::factory()->create([
        'created_at' => $donationDate->setTime(8, 0),
        'status' => EligibilityStatus::Eligible,
        'updated_at' => $donationDate->setTime(8, 0),
        'user_id' => $donor,
    ]);

    return $donor;
}
