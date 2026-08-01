<?php

use App\DonorRewardStatus;
use App\Models\Badge;
use App\Models\DonorBadge;
use App\Models\DonorReward;
use App\Models\Leaderboard;
use App\Models\Reward;
use App\Models\User;

test('donor loyalty relationships and enum casts hydrate through the deployed schema', function () {
    $donor = User::factory()->donor()->create();
    $badge = Badge::factory()->create();
    $reward = Reward::factory()->create();

    $donorBadge = DonorBadge::factory()->recycle([$donor, $badge])->create();
    $donorReward = DonorReward::factory()->recycle([$donor, $reward])->redeemed()->create();
    $leaderboard = Leaderboard::factory()->recycle($donor)->create([
        'period' => 'all_time',
    ]);

    expect($donorBadge->awarded_at)->not->toBeNull()
        ->and($donorReward->status)->toBe(DonorRewardStatus::Redeemed)
        ->and($donorReward->redeemed_at)->not->toBeNull()
        ->and($donor->donorBadges()->first()?->is($donorBadge))->toBeTrue()
        ->and($donor->badges()->first()?->is($badge))->toBeTrue()
        ->and($donor->donorRewards()->first()?->is($donorReward))->toBeTrue()
        ->and($donor->rewards()->first()?->is($reward))->toBeTrue()
        ->and($donor->leaderboardEntries()->first()?->is($leaderboard))->toBeTrue();
});

test('active badge and reward scopes exclude inactive definitions', function () {
    Badge::factory()->create();
    Badge::factory()->inactive()->create();
    Reward::factory()->create();
    Reward::factory()->inactive()->create();

    expect(Badge::query()->active()->count())->toBe(1)
        ->and(Reward::query()->active()->count())->toBe(1);
});
