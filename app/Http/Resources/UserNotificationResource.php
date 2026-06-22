<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'message' => $this->body,
            'body' => $this->body,
            'type' => $this->type,
            'action_url' => $this->action_url,
            'data' => $this->data ?? [],
            'read' => (bool) $this->read_at,
            'read_at' => $this->read_at,
            'sent_at' => $this->sent_at,
            'created_at' => $this->created_at,
        ];
    }
}
