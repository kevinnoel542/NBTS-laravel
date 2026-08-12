<?php

namespace App;

enum SpecimenStatus: string
{
    case Expected = 'expected';
    case Collected = 'collected';
    case HandedOff = 'handed_off';
    case Rejected = 'rejected';
    case Missing = 'missing';
}
