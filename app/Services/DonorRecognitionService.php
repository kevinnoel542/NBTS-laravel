<?php

namespace App\Services;

use App\DonationStatus;
use App\DonorRewardStatus;
use App\Models\Badge;
use App\Models\Donation;
use App\Models\DonorBadge;
use App\Models\DonorProfile;
use App\Models\DonorReward;
use App\Models\Leaderboard;
use App\Models\Reward;
use App\Models\User;
use App\RoleName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class DonorRecognitionService
{
    /**
     * @return array{points: int, tier: string, total_donations: int, new_badges: int, new_rewards: int}
     */
    public function refreshDonor(User $donor, bool $refreshLeaderboard = true): array
    {
        $recognition = DB::transaction(function () use ($donor): array {
            $lockedProfile = DonorProfile::query()
                ->where('user_id', $donor->id)
                ->lockForUpdate()
                ->firstOrFail();
            $totalDonations = Donation::query()
                ->where('user_id', $donor->id)
                ->where('status', DonationStatus::Completed)
                ->count();
            $points = $totalDonations * 100;
            $tier = $this->tierFor($totalDonations);

            $lockedProfile->forceFill([
                'loyalty_points' => $points,
                'loyalty_tier' => $tier,
                'total_donations' => $totalDonations,
            ])->save();

            $newBadges = 0;
            Badge::query()
                ->active()
                ->where('donation_threshold', '<=', $totalDonations)
                ->each(function (Badge $badge) use ($donor, &$newBadges): void {
                    $award = DonorBadge::query()->firstOrCreate(
                        ['badge_id' => $badge->id, 'user_id' => $donor->id],
                        ['awarded_at' => now()],
                    );
                    $newBadges += (int) $award->wasRecentlyCreated;
                });

            $newRewards = 0;
            Reward::query()
                ->active()
                ->where('donation_threshold', '<=', $totalDonations)
                ->each(function (Reward $reward) use ($donor, &$newRewards): void {
                    $award = DonorReward::query()->firstOrCreate(
                        ['reward_id' => $reward->id, 'user_id' => $donor->id],
                        ['awarded_at' => now(), 'status' => DonorRewardStatus::Earned],
                    );
                    $newRewards += (int) $award->wasRecentlyCreated;
                });

            return [
                'new_badges' => $newBadges,
                'new_rewards' => $newRewards,
                'points' => $points,
                'tier' => $tier,
                'total_donations' => $totalDonations,
            ];
        }, attempts: 3);

        if ($refreshLeaderboard) {
            $this->refreshLeaderboard();
        }

        return $recognition;
    }

    public function refreshLeaderboard(): int
    {
        return DB::transaction(function (): int {
            $donors = User::query()
                ->whereHas('roles', fn (Builder $roleQuery): Builder => $roleQuery->where('name', RoleName::Donor->value))
                ->whereHas('donorProfile')
                ->with('donorProfile')
                ->get()
                ->sortByDesc(fn (User $donor): int => (int) $donor->donorProfile?->total_donations)
                ->values();

            foreach ($donors as $index => $donor) {
                Leaderboard::query()->updateOrCreate(
                    ['period' => 'all_time', 'user_id' => $donor->id],
                    [
                        'donation_count' => (int) $donor->donorProfile?->total_donations,
                        'rank' => $index + 1,
                    ],
                );
            }

            return $donors->count();
        }, attempts: 3);
    }

    private function tierFor(int $totalDonations): string
    {
        return match (true) {
            $totalDonations >= 10 => 'Platinum',
            $totalDonations >= 5 => 'Gold',
            $totalDonations >= 2 => 'Silver',
            $totalDonations >= 1 => 'Bronze',
            default => 'Pending',
        };
    }
}
