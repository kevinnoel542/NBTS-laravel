<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rank' => $this->rank,
            'period' => $this->period,
            'period_label' => $this->period_label,
            'donation_count' => (int) $this->donation_count,
            'donor' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'region' => $this->user?->region,
                'donor_id' => $this->user?->donorProfile?->donor_id,
                'loyalty_tier' => $this->user?->donorProfile?->loyalty_tier,
                'loyalty_points' => $this->user?->donorProfile?->loyalty_points,
            ],
            'updated_at' => $this->updated_at,
        ];
    }
}
