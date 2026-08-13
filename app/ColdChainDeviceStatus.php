<?php

namespace App;

enum ColdChainDeviceStatus: string
{
    case Active = 'active';
    case Maintenance = 'maintenance';
    case Alarm = 'alarm';
    case Retired = 'retired';
}
