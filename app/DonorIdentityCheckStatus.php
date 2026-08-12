<?php

namespace App;

enum DonorIdentityCheckStatus: string
{
    case Confirmed = 'confirmed';
    case Failed = 'failed';
    case Revoked = 'revoked';
}
