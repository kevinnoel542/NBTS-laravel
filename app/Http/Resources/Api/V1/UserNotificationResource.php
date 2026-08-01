<?php

namespace App\Http\Resources\Api\V1;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $notification = $this->resource;
        assert($notification instanceof UserNotification);

        return [
            'id' => $notification->id,
            'title' => $notification->title,
            'body' => $notification->body,
            'message' => $notification->body,
            'type' => $notification->type,
            'action_url' => $notification->action_url,
            'data' => $notification->data ?? [],
            'read' => $notification->read_at !== null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'sent_at' => $notification->sent_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }
}
