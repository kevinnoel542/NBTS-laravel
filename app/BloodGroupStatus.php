<?php

namespace App;

enum BloodGroupStatus: string
{
    case Unknown = 'unknown';
    case UserSelected = 'user_selected';
    case StaffVerified = 'staff_verified';
}
