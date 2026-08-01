<?php

namespace App;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case NbtsAdmin = 'nbts_admin';
    case CenterManager = 'center_manager';
    case CenterStaff = 'center_staff';
    case Donor = 'donor';

    /**
     * @return list<string>
     */
    public static function staffValues(): array
    {
        return [
            self::SuperAdmin->value,
            self::NbtsAdmin->value,
            self::CenterManager->value,
            self::CenterStaff->value,
        ];
    }

    /**
     * Map the canonical role to the deployed transitional user role.
     */
    public function legacyValue(): string
    {
        return match ($this) {
            self::SuperAdmin, self::NbtsAdmin => 'admin',
            self::CenterManager, self::CenterStaff => 'staff',
            self::Donor => 'donor',
        };
    }
}
