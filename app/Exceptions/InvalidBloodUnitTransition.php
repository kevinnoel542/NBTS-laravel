<?php

namespace App\Exceptions;

use App\BloodUnitStatus;
use DomainException;

class InvalidBloodUnitTransition extends DomainException
{
    public static function from(BloodUnitStatus $current, BloodUnitStatus $requested): self
    {
        return new self("Blood unit cannot transition from {$current->value} to {$requested->value}.");
    }
}
