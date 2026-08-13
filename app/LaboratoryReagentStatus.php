<?php

namespace App;

enum LaboratoryReagentStatus: string
{
    case Usable = 'usable';
    case Quarantined = 'quarantined';
    case Recalled = 'recalled';
    case Expired = 'expired';

    public function permitsTestingUse(): bool
    {
        return $this === self::Usable;
    }
}
