<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $article = $this->resource;
        assert($article instanceof Article);

        return [
            'id' => $article->id,
            'article_id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'category' => $article->category,
            'summary' => $article->summary,
            'excerpt' => $article->summary,
            'body' => $article->body,
            'content' => $article->body,
            'author_name' => $article->author_name,
            'source_name' => $article->source_name,
            'source_url' => $article->source_url,
            'image_url' => $this->publicFileUrl($article->image_path),
            'attachment_url' => $this->publicFileUrl($article->attachment_path),
            'attachment_name' => $article->attachment_name,
            'attachment_mime' => $article->attachment_mime,
            'is_featured' => $article->is_featured,
            'reading_time_minutes' => $article->reading_time_minutes,
            'meta_description' => $article->meta_description,
            'status' => $article->status->value,
            'status_label' => __('operations.status.'.$article->status->value),
            'published_at' => $article->published_at?->toIso8601String(),
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
