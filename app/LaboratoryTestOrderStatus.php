<?php

namespace App;

enum LaboratoryTestOrderStatus: string
{
    case Ordered = 'ordered';
    case InProgress = 'in_progress';
    case Resulted = 'resulted';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';
}
