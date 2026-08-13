<?php

namespace App;

enum HospitalReceiptStatus: string
{
    case Accepted = 'accepted';
    case Hold = 'hold';
    case Rejected = 'rejected';
}
