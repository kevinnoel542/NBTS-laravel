<?php

namespace App\Models;

use App\ArticleStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $category
 * @property string|null $summary
 * @property string|null $body
 * @property string|null $author_name
 * @property string|null $source_name
 * @property string|null $source_url
 * @property string|null $image_path
 * @property string|null $attachment_path
 * @property string|null $attachment_name
 * @property string|null $attachment_mime
 * @property bool $is_featured
 * @property int $reading_time_minutes
 * @property string|null $meta_description
 * @property ArticleStatus $status
 * @property CarbonImmutable|null $published_at
 */
#[Fillable([
    'title',
    'slug',
    'category',
    'summary',
    'body',
    'author_name',
    'source_name',
    'source_url',
    'image_path',
    'attachment_path',
    'attachment_name',
    'attachment_mime',
    'is_featured',
    'reading_time_minutes',
    'meta_description',
    'status',
    'published_at',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'reading_time_minutes' => 'integer',
            'status' => ArticleStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            $article->slug = self::uniqueSlug($article->slug ?: $article->title, $article->id);
            $article->reading_time_minutes = self::readingTimeMinutes($article->body ?: $article->summary);

            if ($article->status === ArticleStatus::Published && $article->published_at === null) {
                $article->published_at = now();
            }
        });
    }

    /**
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', ArticleStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * @param  Builder<Article>  $query
     * @return Builder<Article>
     */
    public function scopeOrderedForPublic(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_featured')
            ->latest('published_at')
            ->latest('id');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === ArticleStatus::Published
            && $this->published_at?->lessThanOrEqualTo(now()) === true;
    }

    private static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private static function readingTimeMinutes(?string $content): int
    {
        $wordCount = str_word_count(trim(strip_tags((string) $content)));

        return max(1, (int) ceil($wordCount / 220));
    }
}
