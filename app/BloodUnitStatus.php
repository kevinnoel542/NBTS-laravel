<?php

namespace App;

enum BloodUnitStatus: string
{
    case Collected = 'collected';
    case Testing = 'testing';
    case Available = 'available';
    case Reserved = 'reserved';
    case Transferred = 'transferred';
    case Used = 'used';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Discarded = 'discarded';

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, match ($this) {
            self::Collected => [self::Testing, self::Rejected, self::Discarded],
            self::Testing => [self::Available, self::Rejected, self::Discarded],
            self::Available => [self::Reserved, self::Transferred, self::Used, self::Expired, self::Discarded],
            self::Reserved => [self::Available, self::Transferred, self::Used, self::Expired, self::Discarded],
            self::Transferred => [self::Testing, self::Available, self::Reserved, self::Used, self::Expired, self::Discarded],
            self::Used, self::Rejected, self::Expired, self::Discarded => [],
        }, true);
    }

    public function contributesToAvailableInventory(): bool
    {
        return $this === self::Available;
    }
}
