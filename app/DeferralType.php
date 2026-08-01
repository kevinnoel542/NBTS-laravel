<?php

namespace App;

enum DeferralType: string
{
    case Temporary = 'temporary';
    case Permanent = 'permanent';
}
