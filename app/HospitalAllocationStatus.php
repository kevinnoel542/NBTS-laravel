<?php

namespace App;

enum HospitalAllocationStatus: string
{
    case Allocated = 'allocated';
    case Issued = 'issued';
    case Dispatched = 'dispatched';
    case Received = 'received';
    case Returned = 'returned';
    case Cancelled = 'cancelled';
}
