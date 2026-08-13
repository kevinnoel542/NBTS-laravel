<?php

namespace App;

enum PatientSpecimenStatus: string
{
    case Collected = 'collected';
    case Received = 'received';
    case Rejected = 'rejected';
    case Expired = 'expired';
}
