<?php

namespace App\Data;

final readonly class SubmitOfflineCollectionData
{
    /** @param array<string, mixed> $payload */
    public function __construct(
        public string $clientSubmissionId,
        public int $deviceId,
        public int $identifierBatchId,
        public string $donationIdentifier,
        public array $payload,
    ) {}
}
