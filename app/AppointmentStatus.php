<?php

namespace App;

enum AppointmentStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case CheckedIn = 'checked_in';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, match ($this) {
            self::Pending => [self::Confirmed, self::Cancelled],
            self::Confirmed => [self::CheckedIn, self::Completed, self::Cancelled, self::NoShow],
            self::CheckedIn => [self::Completed, self::Cancelled],
            self::Completed, self::Cancelled, self::NoShow => [],
        }, true);
    }
}
