<?php

namespace App;

enum CollectionOutcome: string
{
    case Completed = 'completed';
    case Incomplete = 'incomplete';
    case Failed = 'failed';
    case Interrupted = 'interrupted';
    case UnderVolume = 'under_volume';
    case OverVolume = 'over_volume';

    public function createsCompatibilityUnit(): bool
    {
        return $this === self::Completed;
    }
}
