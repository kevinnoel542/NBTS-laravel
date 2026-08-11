<?php

use App\Actions\Engagement\RefreshDonorRecognition;
use App\Actions\Engagement\SaveReward;
use App\Data\SaveRewardData;
use App\DonationStatus;
use App\Livewire\Operations\Workspace;
use App\Models\AuditLog;
use App\Models\BloodCenter;
use App\Models\Donation;
use App\Models\DonorBadge;
use App\Models\DonorProfile;
use App\Models\DonorReward;
use App\Models\Leaderboard;
use App\Models\Reward;
use App\Models\User;
use Database\Seeders\LoyaltySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([
        RolePermissionSeeder::class,
        LoyaltySeeder::class,
    ]);
});

test('recognition refresh awards milestones and updates the donor leaderboard idempotently', function () {
    $center = BloodCenter::factory()->create();
    $admin = User::factory()->nbtsAdmin()->create();
    $donor = User::factory()->donor()->create();

    DonorProfile::factory()->create(['user_id' => $donor]);
    Donation::factory()->count(3)->create([
        'blood_center_id' => $center,
        'recorded_by' => $admin,
        'status' => DonationStatus::Completed,
        'user_id' => $donor,
    ]);

    $firstRefresh = app(RefreshDonorRecognition::class)->execute($donor, $admin);
    $secondRefresh = app(RefreshDonorRecognition::class)->execute($donor, $admin);
    $profile = $donor->refresh()->donorProfile;

    expect($firstRefresh['points'])->toBe(300)
        ->and($firstRefresh['tier'])->toBe('Silver')
        ->and($firstRefresh['total_donations'])->toBe(3)
        ->and($firstRefresh['new_badges'])->toBe(2)
        ->and($firstRefresh['new_rewards'])->toBe(1)
        ->and($secondRefresh['new_badges'])->toBe(0)
        ->and($secondRefresh['new_rewards'])->toBe(0)
        ->and($profile->loyalty_points)->toBe(300)
        ->and($profile->loyalty_tier)->toBe('Silver')
        ->and($profile->total_donations)->toBe(3)
        ->and(DonorBadge::query()->where('user_id', $donor->id)->count())->toBe(2)
        ->and(DonorReward::query()->where('user_id', $donor->id)->count())->toBe(1)
        ->and(Leaderboard::query()->where('user_id', $donor->id)->value('rank'))->toBe(1)
        ->and(AuditLog::query()->where('action', 'loyalty.donor_recognition_refreshed')->count())->toBe(2);
});

test('staff without loyalty permission cannot refresh recognition', function () {
    $staff = User::factory()->staff()->create();
    $donor = User::factory()->donor()->create();

    DonorProfile::factory()->create(['user_id' => $donor]);

    expect(fn () => app(RefreshDonorRecognition::class)->execute($donor, $staff))
        ->toThrow(AuthorizationException::class)
        ->and(AuditLog::query()->count())->toBe(0);
});

test('an administrator can create and safely deactivate a reward', function () {
    $admin = User::factory()->nbtsAdmin()->create();

    $reward = app(SaveReward::class)->execute(
        $admin,
        new SaveRewardData(
            name: 'Five donation recognition pack',
            slug: 'five-donation-recognition-pack',
            description: 'A recognition pack for consistent donors.',
            donationThreshold: 5,
            isActive: true,
        ),
    );

    $updatedReward = app(SaveReward::class)->execute(
        $admin,
        new SaveRewardData(
            name: $reward->name,
            slug: $reward->slug,
            description: $reward->description,
            donationThreshold: $reward->donation_threshold,
            isActive: false,
            reason: 'Recognition pack is being replaced by the new annual programme.',
        ),
        $reward,
    );
    $updateAudit = AuditLog::query()->where('action', 'loyalty.reward_updated')->sole();

    expect($updatedReward->is_active)->toBeFalse()
        ->and(AuditLog::query()->where('action', 'loyalty.reward_created')->count())->toBe(1)
        ->and(AuditLog::query()->where('action', 'loyalty.reward_updated')->count())->toBe(1)
        ->and($updateAudit->metadata['reason'])
        ->toBe('Recognition pack is being replaced by the new annual programme.');
});

test('the engagement workspace creates a validated reward from its editor', function () {
    $admin = User::factory()->nbtsAdmin()->create();

    Livewire::actingAs($admin)
        ->test(Workspace::class, ['workspace' => 'engagement'])
        ->set('tab', 'rewards')
        ->call('openRewardEditor')
        ->set('rewardName', 'Emergency donor pin')
        ->set('rewardSlug', 'emergency-donor-pin')
        ->set('rewardDescription', 'Recognition for recurring emergency response donors.')
        ->set('rewardDonationThreshold', 8)
        ->set('rewardIsActive', true)
        ->call('saveReward')
        ->assertHasNoErrors()
        ->assertSet('rewardEditorId', null);

    expect(Reward::query()->where('slug', 'emergency-donor-pin')->value('donation_threshold'))->toBe(8)
        ->and(AuditLog::query()->where('action', 'loyalty.reward_created')->count())->toBe(1);
});
