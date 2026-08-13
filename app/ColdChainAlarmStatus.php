<?php

namespace App;

enum ColdChainAlarmStatus: string
{
    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case Escalated = 'escalated';
    case Closed = 'closed';
}
