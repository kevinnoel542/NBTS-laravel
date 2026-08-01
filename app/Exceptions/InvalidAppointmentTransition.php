<?php

namespace App\Exceptions;

use App\AppointmentStatus;
use DomainException;

class InvalidAppointmentTransition extends DomainException
{
    public static function from(AppointmentStatus $current, AppointmentStatus $requested): self
    {
        return new self("Appointment cannot transition from {$current->value} to {$requested->value}.");
    }
}
