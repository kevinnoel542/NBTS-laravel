<?php

namespace App;

enum OrganizationUnitType: string
{
    case National = 'national';
    case Zone = 'zone';
    case Region = 'region';
    case BloodCenter = 'blood_center';
    case Hospital = 'hospital';
}
