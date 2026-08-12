<?php

namespace App;

enum OfflineCollectionSubmissionStatus: string
{
    case Received = 'received';
    case Reconciled = 'reconciled';
    case Conflict = 'conflict';
    case Rejected = 'rejected';
}
