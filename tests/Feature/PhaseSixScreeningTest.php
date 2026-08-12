<?php

use App\Actions\Eligibility\RecordEligibilityScreening;
use App\Data\RecordEligibilityScreeningData;
use App\EligibilityStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\Deferral;
use App\Models\DonorIdentityCheck;
use App\Models\DonorProfile;
use App\Models\ScreeningProtocol;
use App\Models\User;
use App\Models\UserNotification;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\ScreeningProtocolSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed([RolePermissionSeeder::class, ScreeningProtocolSeeder::class]);
    $this->center = BloodCenter::factory()->create();
    $this->actor = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create(['user_id' => $this->actor, 'blood_center_id' => $this->center]);
    $this->donor = User::factory()->donor()->create(['date_of_birth' => now()->subYears(30)]);
    DonorProfile::factory()->create(['user_id' => $this->donor, 'preferred_center_id' => $this->center]);
    $this->appointment = Appointment::factory()->confirmed()->create(['user_id' => $this->donor, 'blood_center_id' => $this->center]);
    $this->identity = DonorIdentityCheck::factory()->create(['donor_id' => $this->donor, 'blood_center_id' => $this->center, 'appointment_id' => $this->appointment]);
    $this->protocol = ScreeningProtocol::query()->effective()->firstOrFail();
});

test('screening stores protocol, identity, source and decision snapshots', function () {
    $record = app(RecordEligibilityScreening::class)->execute(new RecordEligibilityScreeningData(
        donorId: $this->donor->id,
        status: EligibilityStatus::Eligible,
        age: 30,
        weightKg: 68.4,
        answers: ['consent_confirmed' => true, 'feels_well' => true, 'self_exclusion' => false],
        bloodCenterId: $this->center->id,
        appointmentId: $this->appointment->id,
        identityCheckId: $this->identity->id,
        screeningProtocolId: $this->protocol->id,
        hemoglobinGdl: 13.2,
        observations: ['pulse' => 74],
        decisionCode: 'routine_eligible',
    ), $this->actor);

    expect($record->status)->toBe(EligibilityStatus::Eligible)
        ->and($record->questionnaire_version)->toBe($this->protocol->code.'@'.$this->protocol->version)
        ->and($record->identity_check_id)->toBe($this->identity->id)
        ->and($record->screened_at)->not->toBeNull()
        ->and($record->source_mode)->toBe('online');
});

test('protocol rules block an unsafe eligible decision without a controlled override', function () {
    expect(fn () => app(RecordEligibilityScreening::class)->execute(new RecordEligibilityScreeningData(
        donorId: $this->donor->id,
        status: EligibilityStatus::Eligible,
        age: 30,
        weightKg: 42,
        answers: ['consent_confirmed' => true, 'feels_well' => true, 'self_exclusion' => false],
        bloodCenterId: $this->center->id,
        appointmentId: $this->appointment->id,
        identityCheckId: $this->identity->id,
        screeningProtocolId: $this->protocol->id,
    ), $this->actor))->toThrow(ValidationException::class);
});

test('confidential self exclusion creates a controlled temporary deferral', function () {
    $record = app(RecordEligibilityScreening::class)->execute(new RecordEligibilityScreeningData(
        donorId: $this->donor->id,
        status: EligibilityStatus::Eligible,
        age: 30,
        weightKg: 68,
        answers: ['consent_confirmed' => true, 'feels_well' => true, 'self_exclusion' => true],
        deferralReason: 'Confidential self-exclusion selected during private screening.',
        deferralEndsAt: now()->addMonth()->toImmutable(),
        bloodCenterId: $this->center->id,
        appointmentId: $this->appointment->id,
        identityCheckId: $this->identity->id,
        screeningProtocolId: $this->protocol->id,
        selfExcluded: true,
        counsellingNotes: 'Private counselling completed.',
        referral: 'Return to the private counselling desk before a future donation.',
        reentryDate: now()->addMonth()->toImmutable(),
    ), $this->actor);

    $notification = UserNotification::query()->where('user_id', $this->donor->id)->sole();

    expect($record->status)->toBe(EligibilityStatus::TemporarilyDeferred)
        ->and($record->self_excluded)->toBeTrue()
        ->and($record->referral)->toContain('private counselling')
        ->and(Deferral::query()->where('user_id', $this->donor->id)->effectiveOn()->exists())->toBeTrue()
        ->and($notification->body)->not->toContain('self-exclusion', 'deferred', 'Confidential');
});
