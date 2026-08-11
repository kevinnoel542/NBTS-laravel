<?php

namespace App\Data;

final readonly class SaveRewardData
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description,
        public int $donationThreshold,
        public bool $isActive,
        public ?string $reason = null,
    ) {}
}
