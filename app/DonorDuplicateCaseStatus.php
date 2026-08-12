<?php

namespace App;

enum DonorDuplicateCaseStatus: string
{
    case Pending = 'pending';
    case Merged = 'merged';
    case Rejected = 'rejected';
}
