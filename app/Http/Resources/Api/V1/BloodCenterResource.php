<?php

namespace App\Http\Resources\Api\V1;

use App\Models\BloodCenter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class BloodCenterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bloodCenter = $this->resource;
        assert($bloodCenter instanceof BloodCenter);

        $waitTime = $bloodCenter->estimated_wait_minutes === null
            ? null
            : trans_choice('api.center_wait_minutes', $bloodCenter->estimated_wait_minutes, [
                'minutes' => $bloodCenter->estimated_wait_minutes,
            ]);

        return [
            'id' => $bloodCenter->id,
            'center_id' => $bloodCenter->id,
            'name' => $bloodCenter->name,
            'address' => $bloodCenter->address,
            'city' => $bloodCenter->city,
            'phone' => $bloodCenter->phone,
            'phone_number' => $bloodCenter->phone,
            'email' => $bloodCenter->email,
            'opening_hours' => $bloodCenter->opening_hours,
            'hours' => $bloodCenter->opening_hours,
            'services' => $bloodCenter->services ?? [],
            'capacity_label' => $bloodCenter->capacity_label,
            'wait_time' => $waitTime,
            'estimated_wait_minutes' => $bloodCenter->estimated_wait_minutes,
            'center_type' => $bloodCenter->center_type,
            'image_url' => $this->imageUrl($bloodCenter),
            'latitude' => $bloodCenter->latitude === null ? null : (float) $bloodCenter->latitude,
            'longitude' => $bloodCenter->longitude === null ? null : (float) $bloodCenter->longitude,
            'is_active' => $bloodCenter->is_active,
            'is_open' => $bloodCenter->is_active,
            'open' => $bloodCenter->is_active,
        ];
    }

    private function imageUrl(BloodCenter $bloodCenter): ?string
    {
        if ($bloodCenter->image_path === null || $bloodCenter->image_path === '') {
            return null;
        }

        if (str_starts_with($bloodCenter->image_path, 'http://')
            || str_starts_with($bloodCenter->image_path, 'https://')) {
            return $bloodCenter->image_path;
        }

        return Storage::disk('public')->url($bloodCenter->image_path);
    }
}
