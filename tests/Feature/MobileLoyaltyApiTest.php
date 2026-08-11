<?php

use App\DonorRewardStatus;
use App\Models\Badge;
use App\Models\DonorBadge;
use App\Models\DonorProfile;
use App\Models\DonorReward;
use App\Models\Leaderboard;
use App\Models\Reward;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function mobileLoyaltyToken(User $donor, array $abilities = ['donor:read']): string
{
    return $donor->createToken('Loyalty Phone', $abilities)->plainTextToken;
}

test('a donor receives their private loyalty awards and all time rank', function () {
    $donor = User::factory()->donor()->create();
    DonorProfile::factory()->create([
        'loyalty_points' => 500,
        'loyalty_tier' => 'Gold',
        'total_donations' => 5,
        'user_id' => $donor,
    ]);
    $badge = Badge::factory()->create([
        'donation_threshold' => 3,
        'name' => 'Three donation badge',
        'slug' => 'three-donation-badge',
    ]);
    $reward = Reward::factory()->create([
        'donation_threshold' => 5,
        'name' => 'Five donation reward',
        'slug' => 'five-donation-reward',
    ]);
    DonorBadge::factory()->create([
        'badge_id' => $badge,
        'user_id' => $donor,
    ]);
    DonorReward::factory()->create([
        'reward_id' => $reward,
        'status' => DonorRewardStatus::Earned,
        'user_id' => $donor,
    ]);
    Leaderboard::factory()->create([
        'period' => 'all_time',
        'rank' => 2,
        'user_id' => $donor,
    ]);

    $this->withToken(mobileLoyaltyToken($donor))
        ->getJson(route('api.v1.loyalty.show'))
        ->assertOk()
        ->assertJsonPath('data.points', 500)
        ->assertJsonPath('data.loyalty_points', 500)
        ->assertJsonPath('data.tier', 'Gold')
        ->assertJsonPath('data.loyalty_tier', 'Gold')
        ->assertJsonPath('data.total_donations', 5)
        ->assertJsonPath('data.rank', 2)
        ->assertJsonPath('data.badges.0.slug', 'three-donation-badge')
        ->assertJsonPath('data.rewards.0.slug', 'five-donation-reward')
        ->assertJsonPath('data.rewards.0.status', 'earned');
});

test('the leaderboard is paginated and includes only privacy-consented anonymized donors', function () {
    $currentDonor = User::factory()->donor()->create(['name' => 'Private Current Donor']);
    $visibleDonor = User::factory()->donor()->create(['name' => 'Visible Real Name']);
    $hiddenDonor = User::factory()->donor()->create(['name' => 'Hidden Real Name']);
    DonorProfile::factory()->create([
        'loyalty_tier' => 'Gold',
        'share_anonymized_data' => true,
        'user_id' => $currentDonor,
    ]);
    DonorProfile::factory()->create([
        'loyalty_tier' => 'Silver',
        'share_anonymized_data' => true,
        'user_id' => $visibleDonor,
    ]);
    DonorProfile::factory()->create([
        'share_anonymized_data' => false,
        'user_id' => $hiddenDonor,
    ]);
    Leaderboard::factory()->create([
        'donation_count' => 7,
        'period' => 'all_time',
        'rank' => 1,
        'user_id' => $currentDonor,
    ]);
    Leaderboard::factory()->create([
        'donation_count' => 4,
        'period' => 'all_time',
        'rank' => 2,
        'user_id' => $visibleDonor,
    ]);
    Leaderboard::factory()->create([
        'donation_count' => 3,
        'period' => 'all_time',
        'rank' => 3,
        'user_id' => $hiddenDonor,
    ]);

    $response = $this->withToken(mobileLoyaltyToken($currentDonor))
        ->getJson(route('api.v1.leaderboard.index', ['per_page' => 1]))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.display_name', 'Donor 001')
        ->assertJsonPath('data.0.donation_count', 7)
        ->assertJsonPath('data.0.loyalty_tier', 'Gold')
        ->assertJsonPath('data.0.is_current_user', true)
        ->assertJsonPath('meta.total', 2)
        ->assertJsonPath('meta.current_user_rank', 1)
        ->assertJsonPath('meta.period', 'all_time');

    expect($response->content())
        ->not->toContain('Private Current Donor')
        ->not->toContain('Visible Real Name')
        ->not->toContain('Hidden Real Name');
});

test('loyalty endpoints enforce donor read abilities roles and bounded filters', function () {
    $donor = User::factory()->donor()->create();
    $staff = User::factory()->staff()->create();

    $this->getJson(route('api.v1.loyalty.show'))->assertUnauthorized();

    $this->withToken(mobileLoyaltyToken($donor, ['donor:write']))
        ->getJson(route('api.v1.loyalty.show'))
        ->assertForbidden();

    $this->app['auth']->forgetGuards();

    $this->withToken(mobileLoyaltyToken($staff))
        ->getJson(route('api.v1.leaderboard.index'))
        ->assertForbidden();

    $this->app['auth']->forgetGuards();

    $this->withToken(mobileLoyaltyToken($donor))
        ->getJson(route('api.v1.leaderboard.index', ['per_page' => 51, 'period' => 'monthly']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['per_page', 'period']);
});
