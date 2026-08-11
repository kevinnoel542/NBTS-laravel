<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Leaderboard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Leaderboard */
final class LeaderboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $entry = $this->resource;
        assert($entry instanceof Leaderboard);

        return [
            'id' => $entry->id,
            'period' => $entry->period,
            'rank' => $entry->rank,
            'display_name' => 'Donor '.str_pad((string) $entry->rank, 3, '0', STR_PAD_LEFT),
            'donation_count' => $entry->donation_count,
            'loyalty_tier' => $entry->user->donorProfile->loyalty_tier ?? 'Pending',
            'is_current_user' => $request->user()?->is($entry->user) ?? false,
        ];
    }
}
