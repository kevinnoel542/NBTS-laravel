<?php

namespace App;

enum QualityAuditStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case FindingsOpen = 'findings_open';
    case Closed = 'closed';
}
