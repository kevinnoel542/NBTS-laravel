<?php

namespace App;

enum LowStockAlertStatus: string
{
    case Open = 'open';
    case Notified = 'notified';
    case CampaignCreated = 'campaign_created';
    case Resolved = 'resolved';

    public function isActive(): bool
    {
        return $this !== self::Resolved;
    }
}
