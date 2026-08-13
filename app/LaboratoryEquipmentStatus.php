<?php

namespace App;

enum LaboratoryEquipmentStatus: string
{
    case Active = 'active';
    case Downtime = 'downtime';
    case Maintenance = 'maintenance';
    case Retired = 'retired';
}
