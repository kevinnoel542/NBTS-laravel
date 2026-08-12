<?php

namespace App;

enum OrganizationUnitStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case TemporarilyClosed = 'temporarily_closed';
    case Retired = 'retired';
}
