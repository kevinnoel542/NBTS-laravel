<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'summary' => $this->summary,
            'body' => $this->body,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'status' => $this->status,
            'published_at' => $this->published_at,
        ];
    }
}
