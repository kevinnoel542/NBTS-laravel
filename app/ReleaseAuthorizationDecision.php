<?php

namespace App;

enum ReleaseAuthorizationDecision: string
{
    case RoutineRelease = 'routine_release';
    case Rejected = 'rejected';
    case EmergencyOverride = 'emergency_override';
}
