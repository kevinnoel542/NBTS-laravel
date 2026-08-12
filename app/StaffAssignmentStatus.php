<?php

namespace App;

enum StaffAssignmentStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Expired = 'expired';
    case Revoked = 'revoked';
}
