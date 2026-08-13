<?php

namespace App;

enum LaboratoryTestResultStatus: string
{
    case Preliminary = 'preliminary';
    case Verified = 'verified';
    case Invalid = 'invalid';
    case Repeated = 'repeated';
}
