<?php

namespace App\Data;

use App\BloodGroup;
use App\DonationType;
use Carbon\CarbonImmutable;
use InvalidArgumentException;

final readonly class RecordDonationData
{
    public function __construct(
        public int $donorId,
        public int $bloodCenterId,
        public DonationType $donationType,
        public BloodGroup $bloodGroup,
        public int $volumeMl,
        public CarbonImmutable $donationDate,
        public bool $bloodGroupVerified,
        public ?int $appointmentId = null,
        public ?string $notes = null,
    ) {
        if ($this->volumeMl <= 0) {
            throw new InvalidArgumentException('Donation volume must be greater than zero.');
        }

        if ($this->donationType === DonationType::Appointment && $this->appointmentId === null) {
            throw new InvalidArgumentException('Appointment donations require an appointment ID.');
        }

        if ($this->donationType === DonationType::WalkIn && $this->appointmentId !== null) {
            throw new InvalidArgumentException('Walk-in donations cannot reference an appointment.');
        }
    }
}
