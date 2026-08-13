<?php

namespace App;

enum EmergencyReleaseStatus: string
{
    case Authorized = 'authorized';
    case Acknowledged = 'acknowledged';
    case RetrospectiveCompleted = 'retrospective_completed';
    case Cancelled = 'cancelled';
}
