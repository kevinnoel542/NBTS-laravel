<?php

namespace App;

enum BloodGroup: string
{
    case APositive = 'A+';
    case ANegative = 'A-';
    case BPositive = 'B+';
    case BNegative = 'B-';
    case AbPositive = 'AB+';
    case AbNegative = 'AB-';
    case OPositive = 'O+';
    case ONegative = 'O-';

    /** @return list<string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
