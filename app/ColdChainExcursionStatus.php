<?php

namespace App;

enum ColdChainExcursionStatus: string
{
    case Open = 'open';
    case Investigating = 'investigating';
    case Closed = 'closed';
}
