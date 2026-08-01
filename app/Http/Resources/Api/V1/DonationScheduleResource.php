<?php

namespace App\Http\Resources\Api\V1;

use App\CampaignType;
use App\Models\BloodCenter;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class DonationScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $campaign = $this->resource;
        assert($campaign instanceof Campaign);
        $bloodCenter = $campaign->relationLoaded('bloodCenter')
            ? $campaign->bloodCenter
            : null;

        return [
            'id' => $campaign->id,
            'schedule_id' => $campaign->id,
            'campaign_id' => $campaign->id,
            'title' => $campaign->title,
            'description' => $campaign->description,
            'starts_at' => $campaign->start_date->toIso8601String(),
            'start_date' => $campaign->start_date->toIso8601String(),
            'ends_at' => $campaign->end_date->toIso8601String(),
            'end_date' => $campaign->end_date->toIso8601String(),
            'status' => $campaign->status->value,
            'location' => $campaign->location ?? $bloodCenter?->address,
            'blood_group' => $campaign->target_blood_group?->value,
            'blood_type' => $campaign->target_blood_group?->value,
            'urgent' => $campaign->campaign_type === CampaignType::Emergency,
            'blood_center_id' => $campaign->blood_center_id,
            'center_id' => $campaign->blood_center_id,
            'center_name' => $bloodCenter?->name,
            'center_address' => $bloodCenter?->address,
            'city' => $bloodCenter?->city,
            'opening_hours' => $bloodCenter?->opening_hours,
            'blood_center' => $bloodCenter instanceof BloodCenter
                ? new BloodCenterResource($bloodCenter)
                : null,
        ];
    }
}
