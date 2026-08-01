<?php

namespace App\Http\Resources\Api\V1;

use App\CampaignStatus;
use App\CampaignType;
use App\Models\BloodCenter;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class CampaignResource extends JsonResource
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
        $isActive = $campaign->status === CampaignStatus::Ongoing
            && $campaign->start_date->lessThanOrEqualTo(now())
            && $campaign->end_date->greaterThanOrEqualTo(now());

        return [
            'id' => $campaign->id,
            'campaign_id' => $campaign->id,
            'title' => $campaign->title,
            'summary' => $campaign->description,
            'description' => $campaign->description,
            'image_url' => $this->publicFileUrl($campaign->image_path),
            'starts_at' => $campaign->start_date->toIso8601String(),
            'start_date' => $campaign->start_date->toIso8601String(),
            'ends_at' => $campaign->end_date->toIso8601String(),
            'end_date' => $campaign->end_date->toIso8601String(),
            'status' => $campaign->status->value,
            'status_label' => __('operations.status.'.$campaign->status->value),
            'is_upcoming' => $campaign->status === CampaignStatus::Upcoming,
            'is_active' => $isActive,
            'category' => $campaign->campaign_type->value,
            'type' => $campaign->campaign_type->value,
            'type_label' => __('operations.types.'.$campaign->campaign_type->value),
            'blood_group' => $campaign->target_blood_group?->value,
            'blood_type' => $campaign->target_blood_group?->value,
            'target_blood_group' => $campaign->target_blood_group?->value,
            'urgent' => $campaign->campaign_type === CampaignType::Emergency,
            'is_emergency' => $campaign->campaign_type === CampaignType::Emergency,
            'location' => $campaign->location,
            'blood_center_id' => $campaign->blood_center_id,
            'center_id' => $campaign->blood_center_id,
            'center_name' => $bloodCenter?->name,
            'center_address' => $bloodCenter?->address,
            'blood_center' => $bloodCenter instanceof BloodCenter
                ? new BloodCenterResource($bloodCenter)
                : null,
        ];
    }

    private function publicFileUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
