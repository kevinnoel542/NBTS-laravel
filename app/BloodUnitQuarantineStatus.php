<?php

namespace App;

enum BloodUnitQuarantineStatus: string
{
    case Held = 'held';
    case Released = 'released';
}
