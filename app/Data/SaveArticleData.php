<?php

namespace App\Data;

use App\ArticleStatus;
use Carbon\CarbonImmutable;

final readonly class SaveArticleData
{
    public function __construct(
        public string $title,
        public string $slug,
        public string $category,
        public string $summary,
        public string $body,
        public ?string $authorName,
        public ?string $sourceName,
        public ?string $sourceUrl,
        public ?string $imagePath,
        public ?string $attachmentPath,
        public ?string $attachmentName,
        public ?string $attachmentMime,
        public bool $isFeatured,
        public ArticleStatus $status,
        public ?CarbonImmutable $publishedAt,
        public ?string $metaDescription,
        public ?string $reason = null,
    ) {}
}
