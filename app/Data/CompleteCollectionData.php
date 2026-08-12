<?php

namespace App\Data;

use App\BloodGroup;
use App\CollectionOutcome;

final readonly class CompleteCollectionData
{
    public function __construct(
        public CollectionOutcome $outcome,
        public BloodGroup $bloodGroup,
        public int $actualVolumeMl,
        public bool $aftercareConfirmed,
        public bool $donorAcknowledged,
        public ?string $notes = null,
    ) {}
}
