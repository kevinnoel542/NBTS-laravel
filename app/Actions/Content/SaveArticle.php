<?php

namespace App\Actions\Content;

use App\ArticleStatus;
use App\Data\SaveArticleData;
use App\Models\Article;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class SaveArticle
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function execute(User $actor, SaveArticleData $data, ?Article $article = null): Article
    {
        return DB::transaction(function () use ($actor, $data, $article): Article {
            $lockedArticle = $article === null
                ? new Article
                : Article::query()->lockForUpdate()->findOrFail($article->id);

            Gate::forUser($actor)->authorize(
                $lockedArticle->exists ? 'update' : 'create',
                $lockedArticle->exists ? $lockedArticle : Article::class,
            );

            $previousStatus = $lockedArticle->exists ? $lockedArticle->status : null;

            if (
                $previousStatus === ArticleStatus::Published
                && $data->status !== ArticleStatus::Published
                && blank($data->reason)
            ) {
                throw ValidationException::withMessages([
                    'articleReason' => [__('console.content.unpublish_reason_required')],
                ]);
            }

            $lockedArticle->fill([
                'attachment_mime' => $data->attachmentMime,
                'attachment_name' => $data->attachmentName,
                'attachment_path' => $data->attachmentPath,
                'author_name' => $data->authorName,
                'body' => $data->body,
                'category' => $data->category,
                'image_path' => $data->imagePath,
                'is_featured' => $data->isFeatured,
                'meta_description' => $data->metaDescription,
                'published_at' => $data->publishedAt,
                'slug' => $data->slug,
                'source_name' => $data->sourceName,
                'source_url' => $data->sourceUrl,
                'status' => $data->status,
                'summary' => $data->summary,
                'title' => $data->title,
            ])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: $article === null ? 'content.article_created' : 'content.article_updated',
                subject: $lockedArticle,
                metadata: [
                    'category' => $data->category,
                    'from_status' => $previousStatus?->value,
                    'has_attachment' => $data->attachmentPath !== null,
                    'has_image' => $data->imagePath !== null,
                    'published_at' => $lockedArticle->published_at?->toIso8601String(),
                    'reason' => $data->reason,
                    'to_status' => $data->status->value,
                ],
            );

            return $lockedArticle->refresh();
        }, attempts: 3);
    }
}
