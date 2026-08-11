<?php

namespace App\Data;

use App\DeferralType;
use App\EligibilityStatus;
use Carbon\CarbonImmutable;

final readonly class RecordEligibilityScreeningData
{
    /** @param array<string, bool|string|int|float|null> $answers */
    public function __construct(
        public int $donorId,
        public EligibilityStatus $status,
        public int $age,
        public float $weightKg,
        public array $answers = [],
        public ?CarbonImmutable $nextEligibleDate = null,
        public ?DeferralType $deferralType = null,
        public ?string $deferralReason = null,
        public ?CarbonImmutable $deferralEndsAt = null,
        public ?string $notes = null,
    ) {}
}
