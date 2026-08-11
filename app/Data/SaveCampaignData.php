<?php

namespace App\Data;

use App\BloodGroup;
use App\CampaignStatus;
use App\CampaignType;
use Carbon\CarbonImmutable;

final readonly class SaveCampaignData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public CarbonImmutable $startDate,
        public CarbonImmutable $endDate,
        public ?string $location,
        public CampaignStatus $status,
        public CampaignType $campaignType,
        public ?BloodGroup $targetBloodGroup,
        public ?string $reason = null,
    ) {}
}
