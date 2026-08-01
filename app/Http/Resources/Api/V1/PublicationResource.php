<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

final class PublicationResource extends JsonResource
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
            'publication_id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'category' => $article->category,
            'summary' => $article->summary,
            'author_name' => $article->author_name,
            'source_name' => $article->source_name,
            'source_url' => $article->source_url,
            'image_url' => $this->publicFileUrl($article->image_path),
            'download_url' => $this->publicFileUrl($article->attachment_path),
            'attachment_url' => $this->publicFileUrl($article->attachment_path),
            'file_name' => $article->attachment_name,
            'attachment_name' => $article->attachment_name,
            'mime_type' => $article->attachment_mime,
            'attachment_mime' => $article->attachment_mime,
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
