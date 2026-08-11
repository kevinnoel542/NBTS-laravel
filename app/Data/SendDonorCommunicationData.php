<?php

namespace App\Data;

use App\BloodGroup;

final readonly class SendDonorCommunicationData
{
    public function __construct(
        public string $title,
        public string $body,
        public string $type,
        public ?string $actionUrl,
        public ?int $bloodCenterId,
        public ?BloodGroup $bloodGroup,
        public bool $eligibleDonorsOnly = true,
    ) {}
}
