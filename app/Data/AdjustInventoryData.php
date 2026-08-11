<?php

namespace App\Data;

final readonly class AdjustInventoryData
{
    public function __construct(
        public int $availableDelta,
        public int $reservedDelta,
        public string $reason,
        public ?string $notes = null,
    ) {}
}
