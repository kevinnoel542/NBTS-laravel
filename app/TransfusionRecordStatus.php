<?php

namespace App;

enum TransfusionRecordStatus: string
{
    case Started = 'started';
    case Completed = 'completed';
    case Interrupted = 'interrupted';
    case ReturnedUnused = 'returned_unused';
    case DiscardedUnused = 'discarded_unused';
    case OverdueOutcome = 'overdue_outcome';
}
