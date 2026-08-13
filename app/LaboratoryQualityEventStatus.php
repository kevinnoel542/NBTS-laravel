<?php

namespace App;

enum LaboratoryQualityEventStatus: string
{
    case Open = 'open';
    case Investigating = 'investigating';
    case Closed = 'closed';
}
