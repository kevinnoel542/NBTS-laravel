<?php

namespace App;

enum CollectionContainerStatus: string
{
    case Quarantined = 'quarantined';
    case Hold = 'hold';
    case Failed = 'failed';
    case Voided = 'voided';
}
