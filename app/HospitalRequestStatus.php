<?php

namespace App;

enum HospitalRequestStatus: string
{
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case PartiallyFilled = 'partially_filled';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';
    case DowntimeCaptured = 'downtime_captured';
}
