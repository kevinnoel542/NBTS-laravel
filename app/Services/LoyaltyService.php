<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\DonorBadge;
use App\Models\DonorReward;
use App\Models\Leaderboard;
use App\Models\Reward;
use App\Models\User;

class LoyaltyService
{
    public function awardForDonor(User $donor): void
    {
        $donor->loadMissing('donorProfile');
        $totalDonations = (int) ($donor->donorProfile?->total_donations ?? 0);
        $points = $totalDonations * 100;
        $tier = match (true) {
            $totalDonations >= 10 => 'Platinum',
            $totalDonations >= 5 => 'Gold',
            $totalDonations >= 2 => 'Silver',
            $totalDonations >= 1 => 'Bronze',
            default => 'Pending',
        };

        $donor->donorProfile()->updateOrCreate(
            ['user_id' => $donor->id],
            [
                'donor_id' => $donor->donorProfile?->donor_id ?? $this->generateDonorId(),
                'loyalty_points' => $points,
                'loyalty_tier' => $tier,
            ]
        );

        Badge::where('is_active', true)
            ->where('donation_threshold', '<=', $totalDonations)
            ->get()
            ->each(function (Badge $badge) use ($donor): void {
                DonorBadge::firstOrCreate(
                    ['user_id' => $donor->id, 'badge_id' => $badge->id],
                    ['awarded_at' => now()]
                );
            });

        Reward::where('is_active', true)
            ->where('donation_threshold', '<=', $totalDonations)
            ->get()
            ->each(function (Reward $reward) use ($donor): void {
                DonorReward::firstOrCreate(
                    ['user_id' => $donor->id, 'reward_id' => $reward->id],
                    ['status' => 'earned', 'awarded_at' => now()]
                );
            });

        $this->refreshLeaderboard();
    }

    public function refreshLeaderboard(): void
    {
        User::role('donor')
            ->with('donorProfile')
            ->get()
            ->sortByDesc(fn (User $user) => $user->donorProfile?->total_donations ?? 0)
            ->values()
            ->each(function (User $user, int $index): void {
                Leaderboard::updateOrCreate(
                    ['user_id' => $user->id, 'period' => 'all_time'],
                    [
                        'donation_count' => (int) ($user->donorProfile?->total_donations ?? 0),
                        'rank' => $index + 1,
                    ]
                );
            });
    }

    private function generateDonorId(): string
    {
        do {
            $donorId = 'DNR-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (\App\Models\DonorProfile::where('donor_id', $donorId)->exists());

        return $donorId;
    }
}
