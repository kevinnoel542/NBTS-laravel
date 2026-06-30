<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BadgeResource;
use App\Http\Resources\LeaderboardResource;
use App\Http\Resources\RewardResource;
use App\Models\Leaderboard;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user()->load([
            'donorProfile',
            'donorBadges.badge',
            'donorRewards.reward',
        ]);

        return response()->json([
            'stats' => [
                'total_donations' => $user->donorProfile?->total_donations ?? 0,
                'next_eligible_donation_date' => $user->donorProfile?->next_eligible_donation_date,
            ],
            'badges' => BadgeResource::collection($user->donorBadges->pluck('badge')->filter()->values()),
            'rewards' => RewardResource::collection($user->donorRewards->pluck('reward')->filter()->values()),
        ]);
    }

    public function leaderboard(LoyaltyService $loyaltyService)
    {
        $loyaltyService->refreshLeaderboard();

        $rows = Leaderboard::with('user.donorProfile')
            ->where('period', 'all_time')
            ->orderBy('rank')
            ->limit(50)
            ->get();

        return LeaderboardResource::collection($rows);
    }
}
