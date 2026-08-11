<?php

namespace App\Http\Resources\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
final class LoyaltyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $donor = $this->resource;
        assert($donor instanceof User);
        $profile = $donor->donorProfile;
        $leaderboard = $donor->leaderboardEntries->firstWhere('period', 'all_time');

        return [
            'points' => (int) ($profile->loyalty_points ?? 0),
            'loyalty_points' => (int) ($profile->loyalty_points ?? 0),
            'tier' => $profile->loyalty_tier ?? 'Pending',
            'loyalty_tier' => $profile->loyalty_tier ?? 'Pending',
            'total_donations' => (int) ($profile->total_donations ?? 0),
            'rank' => $leaderboard?->rank,
            'badges' => $donor->donorBadges
                ->sortByDesc('awarded_at')
                ->values()
                ->map(fn ($award): array => [
                    'id' => $award->badge->id,
                    'name' => $award->badge->name,
                    'slug' => $award->badge->slug,
                    'description' => $award->badge->description,
                    'icon' => $award->badge->icon,
                    'donation_threshold' => $award->badge->donation_threshold,
                    'awarded_at' => $award->awarded_at->toIso8601String(),
                ])
                ->all(),
            'rewards' => $donor->donorRewards
                ->sortByDesc('awarded_at')
                ->values()
                ->map(fn ($award): array => [
                    'id' => $award->reward->id,
                    'name' => $award->reward->name,
                    'slug' => $award->reward->slug,
                    'description' => $award->reward->description,
                    'donation_threshold' => $award->reward->donation_threshold,
                    'status' => $award->status->value,
                    'awarded_at' => $award->awarded_at->toIso8601String(),
                    'redeemed_at' => $award->redeemed_at?->toIso8601String(),
                ])
                ->all(),
        ];
    }
}
