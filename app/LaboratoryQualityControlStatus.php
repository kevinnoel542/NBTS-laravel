<?php

namespace App;

enum LaboratoryQualityControlStatus: string
{
    case Passed = 'passed';
    case Failed = 'failed';
    case Invalid = 'invalid';

    public function permitsResultUse(): bool
    {
        return $this === self::Passed;
    }
}
