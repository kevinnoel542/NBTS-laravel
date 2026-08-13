<?php

namespace App;

enum LaboratorySpecimenReceiptStatus: string
{
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Exception = 'exception';
}
