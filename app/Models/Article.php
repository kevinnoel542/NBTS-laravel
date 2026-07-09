<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_ARCHIVED = 'archived';

    public const CATEGORIES = [
        'Taarifa ya Umma',
        'Elimu ya Mchangiaji',
        'Kampeni',
        'Afya',
        'Tahadhari',
        'Matukio',
    ];

    protected $fillable = [
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
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'reading_time_minutes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Article $article): void {
            $article->slug = static::uniqueSlug($article->slug ?: $article->title, $article->id);
            $article->reading_time_minutes = static::readingTimeMinutes($article->body ?: $article->summary);

            if ($article->status === self::STATUS_PUBLISHED && $article->published_at === null) {
                $article->published_at = now();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_PUBLISHED)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function scopeOrderedForPublic(Builder $query): Builder
    {
        return $query
            ->orderByRaw('published_at IS NULL ASC')
            ->latest('published_at')
            ->latest('id');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && ($this->published_at === null || $this->published_at->lessThanOrEqualTo(now()));
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? asset('storage/' . $this->image_path) : null;
    }

    public function attachmentUrl(): ?string
    {
        return $this->attachment_path ? asset('storage/' . $this->attachment_path) : null;
    }

    public function publicUrl(): string
    {
        return route('news.show', $this);
    }

    private static function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'news';
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
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
