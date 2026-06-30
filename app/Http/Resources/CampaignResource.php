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
        $statusLabel = $this->status === 'ongoing'
            ? 'Active'
            : str($this->status)->replace('_', ' ')->title()->toString();
        $type = $this->campaign_type ?: 'standard';

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'summary' => $this->description,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'start_date' => $this->start_date,
            'starts_at' => $this->start_date,
            'start_date_label' => optional($this->start_date)->format('M d, Y'),
            'start_time_label' => optional($this->start_date)->format('h:i A'),
            'end_date' => $this->end_date,
            'ends_at' => $this->end_date,
            'end_date_label' => optional($this->end_date)->format('M d, Y'),
            'end_time_label' => optional($this->end_date)->format('h:i A'),
            'status' => $this->status,
            'status_label' => $statusLabel,
            'is_upcoming' => $this->status === 'upcoming',
            'is_active' => $this->status === 'ongoing',
            'is_completed' => $this->status === 'completed',
            'is_cancelled' => $this->status === 'cancelled',
            'category' => $type,
            'type' => $type,
            'type_label' => str($type)->replace('_', ' ')->title()->toString(),
            'blood_group' => $this->target_blood_group,
            'blood_type' => $this->target_blood_group,
            'target_blood_group' => $this->target_blood_group,
            'urgent' => $type === 'emergency',
            'is_emergency' => $type === 'emergency',
            'location' => $this->location,
            'blood_center' => new BloodCenterResource($this->whenLoaded('bloodCenter')),
            'blood_center_id' => $this->blood_center_id,
            'center_id' => $this->blood_center_id,
            'center_name' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->name),
            'center_address' => $this->whenLoaded('bloodCenter', fn () => $this->bloodCenter?->address),
            'low_stock_alert_id' => $this->low_stock_alert_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
