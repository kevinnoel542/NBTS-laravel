<?php

namespace App;

enum LogisticsMovementStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Packed = 'packed';
    case InTransit = 'in_transit';
    case Received = 'received';
    case PartiallyReceived = 'partially_received';
    case Rejected = 'rejected';
    case Lost = 'lost';
    case Closed = 'closed';
}
