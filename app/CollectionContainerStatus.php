<?php

namespace App;

enum CollectionContainerStatus: string
{
    case Quarantined = 'quarantined';
    case Hold = 'hold';
    case Failed = 'failed';
    case Voided = 'voided';

    public function isHardQuarantined(): bool
    {
        return in_array($this, [
            self::Quarantined,
            self::Hold,
            self::Failed,
        ], true);
    }
}
