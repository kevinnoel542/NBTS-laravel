<?php

use App\Actions\Donors\ConfirmDonorIdentity;
use App\Actions\Donors\CreateDonorAtCenter;
use App\Actions\Donors\ReviewDonorDuplicate;
use App\DonorDuplicateCaseStatus;
use App\DonorIdentityCheckStatus;
use App\DonorIdentityMethod;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\DonorDuplicateCase;
use App\Models\DonorIdentityAlias;
use App\Models\DonorIdentityCheck;
use App\Models\DonorProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
    $this->center = BloodCenter::factory()->create();
    $this->actor = User::factory()->centerManager()->create();
    CenterStaff::factory()->manager()->create(['user_id' => $this->actor, 'blood_center_id' => $this->center]);
});

test('registration blocks likely duplicates unless the override is documented', function () {
    $existing = User::factory()->donor()->create([
        'name' => 'Amina Juma Salim',
        'phone' => '+255712000001',
        'date_of_birth' => '1994-02-16',
        'region' => 'Dar es Salaam',
    ]);
    DonorProfile::factory()->create(['user_id' => $existing, 'preferred_center_id' => $this->center]);
    $data = [
        'name' => 'Amina Juma Salim',
        'phone' => '+255712000099',
        'email' => 'amina.new@example.test',
        'date_of_birth' => '1994-02-16',
        'region' => 'Dar es Salaam',
        'privacy_notice_version' => config('phase-six.privacy_notice_version'),
        'consent_confirmed' => true,
    ];

    expect(fn () => app(CreateDonorAtCenter::class)->handle($this->actor, $this->center, $data))
        ->toThrow(ValidationException::class);

    $candidate = app(CreateDonorAtCenter::class)->handle($this->actor, $this->center, $data + [
        'allow_possible_duplicate' => true,
        'possible_duplicate_reason' => 'Reception confirmed this is a separate person pending supervisor review.',
    ]);

    expect($candidate->donorProfile->identity_review_required)->toBeTrue()
        ->and($candidate->donorProfile->consented_at)->not->toBeNull()
        ->and(DonorDuplicateCase::query()->where('candidate_donor_id', $candidate->id)->pending()->count())->toBe(1);
});

test('duplicate review clears blocks or preserves a merged source as an alias', function () {
    $primary = User::factory()->donor()->create();
    $candidate = User::factory()->donor()->create();
    DonorProfile::factory()->create(['user_id' => $primary, 'preferred_center_id' => $this->center]);
    DonorProfile::factory()->create(['user_id' => $candidate, 'preferred_center_id' => $this->center, 'identity_review_required' => true]);
    $case = DonorDuplicateCase::factory()->create([
        'primary_donor_id' => $primary,
        'candidate_donor_id' => $candidate,
        'blood_center_id' => $this->center,
    ]);

    $reviewed = app(ReviewDonorDuplicate::class)->handle(
        $this->actor,
        $case,
        DonorDuplicateCaseStatus::Merged,
        'Both profiles were verified as the same donor using the registration records.',
    );

    expect($reviewed->status)->toBe(DonorDuplicateCaseStatus::Merged)
        ->and(DonorIdentityAlias::query()->where('source_donor_id', $candidate->id)->exists())->toBeTrue()
        ->and($candidate->fresh()->is_active)->toBeFalse()
        ->and($primary->fresh()->is_active)->toBeTrue();
});

test('identity confirmation is center scoped, expiring and records failures', function () {
    $donor = User::factory()->donor()->create();
    $profile = DonorProfile::factory()->create(['user_id' => $donor, 'preferred_center_id' => $this->center]);

    expect(fn () => app(ConfirmDonorIdentity::class)->handle(
        $this->actor,
        $donor,
        $this->center,
        DonorIdentityMethod::DonorId,
        'WRONG-ID',
    ))->toThrow(ValidationException::class);

    expect(DonorIdentityCheck::query()->where('donor_id', $donor->id)->where('status', DonorIdentityCheckStatus::Failed)->exists())->toBeTrue();

    $check = app(ConfirmDonorIdentity::class)->handle(
        $this->actor,
        $donor,
        $this->center,
        DonorIdentityMethod::DonorId,
        $profile->donor_id,
    );

    expect($check->status)->toBe(DonorIdentityCheckStatus::Confirmed)
        ->and($check->expires_at->greaterThan(now()))->toBeTrue()
        ->and($check->reference_suffix)->toBe(substr($profile->donor_id, -12));
});
