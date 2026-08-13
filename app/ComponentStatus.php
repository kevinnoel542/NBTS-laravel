<?php

namespace App;

enum ComponentStatus: string
{
    case Quarantined = 'quarantined';
    case Released = 'released';
    case Available = 'available';
    case Reserved = 'reserved';
    case Allocated = 'allocated';
    case InTransit = 'in_transit';
    case Issued = 'issued';
    case Returned = 'returned';
    case InvestigationHold = 'investigation_hold';
    case Recalled = 'recalled';
    case Expired = 'expired';
    case Discarded = 'discarded';
    case Transfused = 'transfused';

    public function contributesToAvailableInventory(): bool
    {
        return $this === self::Available;
    }
}
