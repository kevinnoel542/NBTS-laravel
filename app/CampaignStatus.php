<?php

namespace App;

enum CampaignStatus: string
{
    case Upcoming = 'upcoming';
    case Ongoing = 'ongoing';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function isDiscoverable(): bool
    {
        return in_array($this, [self::Upcoming, self::Ongoing], true);
    }
}
