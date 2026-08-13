<?php

namespace App;

enum HospitalRequestUrgency: string
{
    case Routine = 'routine';
    case Urgent = 'urgent';
    case Emergency = 'emergency';
    case MassiveHaemorrhage = 'massive_haemorrhage';
}
