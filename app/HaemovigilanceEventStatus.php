<?php

namespace App;

enum HaemovigilanceEventStatus: string
{
    case Open = 'open';
    case Investigating = 'investigating';
    case Escalated = 'escalated';
    case Closed = 'closed';
}
