<?php

namespace App;

enum EligibilityStatus: string
{
    case Eligible = 'eligible';
    case NotYetEligible = 'not_yet_eligible';
    case TemporarilyDeferred = 'temporarily_deferred';
    case PermanentlyDeferred = 'permanently_deferred';

    public function allowsDonation(): bool
    {
        return $this === self::Eligible;
    }
}
