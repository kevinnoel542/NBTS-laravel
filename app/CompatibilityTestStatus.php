<?php

namespace App;

enum CompatibilityTestStatus: string
{
    case Performed = 'performed';
    case Reviewed = 'reviewed';
    case Rejected = 'rejected';
    case EmergencyOverride = 'emergency_override';
}
