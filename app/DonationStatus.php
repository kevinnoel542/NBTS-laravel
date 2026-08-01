<?php

namespace App;

enum DonationStatus: string
{
    case Completed = 'completed';
    case Failed = 'failed';
}
