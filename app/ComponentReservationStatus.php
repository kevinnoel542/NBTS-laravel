<?php

namespace App;

enum ComponentReservationStatus: string
{
    case Active = 'active';
    case Fulfilled = 'fulfilled';
    case Released = 'released';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
}
