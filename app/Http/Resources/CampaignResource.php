<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampaignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'summary' => $this->description,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'start_date' => $this->start_date,
            'starts_at' => $this->start_date,
            'end_date' => $this->end_date,
            'ends_at' => $this->end_date,
            'status' => $this->status,
            'category' => $this->campaign_type,
            'type' => $this->campaign_type,
            'blood_group' => $this->target_blood_group,
            'blood_type' => $this->target_blood_group,
            'urgent' => $this->campaign_type === 'emergency',
            'blood_center' => new BloodCenterResource($this->whenLoaded('bloodCenter')),
            'center_name' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->name),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
