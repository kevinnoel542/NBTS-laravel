<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'rank' => $this->rank,
            'period' => $this->period,
            'donation_count' => $this->donation_count,
            'donor' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'region' => $this->user?->region,
            ],
        ];
    }
}
