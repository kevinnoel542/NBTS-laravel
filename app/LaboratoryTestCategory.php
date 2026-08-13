<?php

namespace App;

enum LaboratoryTestCategory: string
{
    case TtiScreening = 'tti_screening';
    case BloodGrouping = 'blood_grouping';
    case Confirmatory = 'confirmatory';
    case QualityControl = 'quality_control';
}
