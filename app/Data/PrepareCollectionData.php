<?php

namespace App\Data;

final readonly class PrepareCollectionData
{
    public function __construct(
        public int $donorId,
        public int $bloodCenterId,
        public ?int $appointmentId,
        public int $identityCheckId,
        public int $eligibilityRecordId,
        public string $bagType,
        public string $bagLot,
        public string $donationMethod = 'whole_blood',
        public int $plannedVolumeMl = 450,
        public ?string $deviceIdentifier = null,
        public string $sourceMode = 'online',
        public ?string $donationIdentifier = null,
        public ?string $notes = null,
    ) {}
}
