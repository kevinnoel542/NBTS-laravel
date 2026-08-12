<?php

namespace App;

enum CollectionEpisodeStatus: string
{
    case Prepared = 'prepared';
    case InProgress = 'in_progress';
    case Quarantined = 'quarantined';
    case Failed = 'failed';
    case Interrupted = 'interrupted';
    case Exception = 'exception';
    case Cancelled = 'cancelled';

    public function isFinal(): bool
    {
        return in_array($this, [
            self::Quarantined,
            self::Failed,
            self::Interrupted,
            self::Exception,
            self::Cancelled,
        ], true);
    }
}
