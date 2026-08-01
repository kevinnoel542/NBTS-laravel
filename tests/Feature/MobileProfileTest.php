<?php

use App\BloodGroupStatus;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\DonorProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('current user and profile routes expose the Flutter compatibility fields', function () {
    $center = BloodCenter::factory()->create(['name' => 'Arusha Donation Centre']);
    $donor = User::factory()->donor()->create([
        'phone' => '+255700000003',
        'blood_group' => 'B+',
        'gender' => 'female',
        'region' => 'Arusha',
        'date_of_birth' => '1994-05-10',
        'locale' => 'sw',
    ]);
    DonorProfile::factory()->create([
        'user_id' => $donor,
        'preferred_center_id' => $center,
        'donor_id' => 'DNR-COMPAT-1',
        'loyalty_points' => 120,
        'loyalty_tier' => 'Silver',
    ]);
    $token = $donor->createToken('Profile Phone', ['donor:read'])->plainTextToken;

    foreach ([route('api.v1.me'), route('api.v1.user'), route('api.v1.profile.show')] as $url) {
        $this->withToken($token)->getJson($url)
            ->assertOk()
            ->assertJsonPath('data.donor_id', 'DNR-COMPAT-1')
            ->assertJsonPath('data.preferred_center', 'Arusha Donation Centre')
            ->assertJsonPath('data.preferred_center_id', $center->id)
            ->assertJsonPath('data.loyalty_points', 120)
            ->assertJsonPath('data.loyalty_tier', 'Silver')
            ->assertJsonPath('data.language', 'sw')
            ->assertJsonPath('data.profile_complete', true);

        $this->app['auth']->forgetGuards();
    }
});

test('a donor can update profile and preference fields through a scoped token', function () {
    $center = BloodCenter::factory()->create();
    $donor = User::factory()->donor()->create([
        'phone' => '+255700000004',
        'blood_group' => 'A+',
    ]);
    DonorProfile::factory()->create(['user_id' => $donor]);
    $token = $donor->createToken('Profile Phone', ['donor:write'])->plainTextToken;

    $this->withToken($token)->putJson(route('api.v1.profile.update'), [
        'name' => 'Updated Donor',
        'phone' => '+255700000005',
        'blood_group' => 'AB-',
        'gender' => 'male',
        'date_of_birth' => '1991-06-07',
        'region' => 'Dodoma',
        'address' => 'Area C',
        'preferred_center_id' => $center->id,
        'emergency_contact_name' => 'Family Contact',
        'emergency_contact_phone' => '+255700000006',
        'push_notifications_enabled' => false,
        'sms_reminders_enabled' => false,
        'share_anonymized_data' => true,
        'language' => 'Swahili',
    ])->assertOk()
        ->assertJsonPath('data.name', 'Updated Donor')
        ->assertJsonPath('data.blood_group', 'AB-')
        ->assertJsonPath('data.language', 'sw')
        ->assertJsonPath('data.preferred_center_id', $center->id);

    $donor->refresh();
    $profile = $donor->donorProfile;

    expect($donor->phone)->toBe('+255700000005')
        ->and($donor->locale)->toBe('sw')
        ->and($profile?->blood_group_status)->toBe(BloodGroupStatus::UserSelected)
        ->and($profile?->preferred_center_id)->toBe($center->id)
        ->and($profile?->push_notifications_enabled)->toBeFalse()
        ->and($profile?->sms_reminders_enabled)->toBeFalse()
        ->and($profile?->share_anonymized_data)->toBeTrue();

    $audit = AuditLog::query()->where('action', 'mobile.profile_updated')->sole();

    expect($audit->metadata['changed_fields'])->toContain('phone', 'blood_group', 'language')
        ->and(json_encode($audit->metadata))->not->toContain('+255700000005', 'Area C');
});

test('donors cannot change a staff verified blood group', function () {
    $donor = User::factory()->donor()->create(['blood_group' => 'O+']);
    DonorProfile::factory()->create([
        'user_id' => $donor,
        'blood_group_status' => BloodGroupStatus::StaffVerified,
        'blood_group_verified' => true,
    ]);
    $token = $donor->createToken('Profile Phone', ['donor:write'])->plainTextToken;

    $this->withToken($token)->putJson(route('api.v1.profile.update'), [
        'blood_group' => 'A+',
    ])->assertUnprocessable()->assertJsonValidationErrors('blood_group');

    expect($donor->refresh()->blood_group?->value)->toBe('O+')
        ->and(AuditLog::query()->where('action', 'mobile.profile_updated')->exists())->toBeFalse();
});

test('profile updates require an active center unique phone and write ability', function () {
    $inactiveCenter = BloodCenter::factory()->inactive()->create();
    $otherDonor = User::factory()->donor()->create(['phone' => '+255700000007']);
    $donor = User::factory()->donor()->create(['phone' => '+255700000008']);
    DonorProfile::factory()->create(['user_id' => $donor]);
    $readToken = $donor->createToken('Read Phone', ['donor:read'])->plainTextToken;
    $writeToken = $donor->createToken('Write Phone', ['donor:write'])->plainTextToken;

    $this->withToken($readToken)->putJson(route('api.v1.profile.update'), [
        'name' => 'Forbidden Update',
    ])->assertForbidden();
    $this->app['auth']->forgetGuards();

    $this->withToken($writeToken)->putJson(route('api.v1.profile.update'), [
        'phone' => $otherDonor->phone,
        'preferred_center_id' => $inactiveCenter->id,
    ])->assertUnprocessable()->assertJsonValidationErrors(['phone', 'preferred_center_id']);
});

test('a donor can replace a local profile photo without deleting remote Firebase photos', function () {
    Storage::fake('public');
    $donor = User::factory()->donor()->create([
        'profile_photo_path' => 'profile-photos/old-photo.jpg',
    ]);
    DonorProfile::factory()->create(['user_id' => $donor]);
    Storage::disk('public')->put('profile-photos/old-photo.jpg', 'old');
    $token = $donor->createToken('Photo Phone', ['donor:write'])->plainTextToken;

    $response = $this->withToken($token)->post(
        route('api.v1.profile.photo'),
        ['photo' => UploadedFile::fake()->image('donor.jpg', 600, 600)],
        ['Accept' => 'application/json'],
    );

    $response->assertOk()->assertJsonPath('data.id', $donor->id);

    $donor->refresh();

    expect($donor->profile_photo_path)->toStartWith('profile-photos/'.$donor->id.'/')
        ->and(Storage::disk('public')->exists($donor->profile_photo_path))->toBeTrue()
        ->and(Storage::disk('public')->missing('profile-photos/old-photo.jpg'))->toBeTrue()
        ->and(AuditLog::query()->where('action', 'mobile.profile_photo_updated')->count())->toBe(1);

    $donor->forceFill(['profile_photo_path' => 'https://example.test/firebase-photo.jpg'])->save();
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->post(
        route('api.v1.profile.photo'),
        ['photo' => UploadedFile::fake()->image('replacement.jpg', 600, 600)],
        ['Accept' => 'application/json'],
    )->assertOk();

    Storage::disk('public')->assertMissing('https://example.test/firebase-photo.jpg');
});
