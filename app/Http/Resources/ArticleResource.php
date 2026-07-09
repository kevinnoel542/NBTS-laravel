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
            'slug' => $this->slug,
            'category' => $this->category,
            'summary' => $this->summary,
            'body' => $this->body,
            'author_name' => $this->author_name,
            'source_name' => $this->source_name,
            'source_url' => $this->source_url,
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'attachment_url' => $this->attachment_path ? asset('storage/' . $this->attachment_path) : null,
            'attachment_name' => $this->attachment_name,
            'is_featured' => $this->is_featured,
            'reading_time_minutes' => $this->reading_time_minutes,
            'public_url' => $this->slug ? route('news.show', $this) : null,
            'status' => $this->status,
            'published_at' => $this->published_at,
        ];
    }
}
