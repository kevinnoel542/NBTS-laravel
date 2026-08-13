<?php

namespace App;

enum CompatibilityResult: string
{
    case Compatible = 'compatible';
    case Incompatible = 'incompatible';
    case Indeterminate = 'indeterminate';
    case EmergencyUncrossmatched = 'emergency_uncrossmatched';
}
