<?php

namespace App;

enum DonationType: string
{
    case Appointment = 'appointment';
    case WalkIn = 'walk_in';
}
