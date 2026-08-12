<?php

namespace App;

enum OfflineCollectionDeviceStatus: string
{
    case Active = 'active';
    case Suspended = 'suspended';
    case Revoked = 'revoked';
}
