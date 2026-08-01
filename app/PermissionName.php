<?php

namespace App;

enum PermissionName: string
{
    case ViewUsers = 'users.view';
    case ManageUsers = 'users.manage';
    case ManageRoles = 'roles.manage';
    case ViewDonors = 'donors.view';
    case ManageDonors = 'donors.manage';
    case ViewCenters = 'centers.view';
    case ManageCenters = 'centers.manage';
    case ManageCenterStaff = 'center_staff.manage';
    case ViewAppointments = 'appointments.view';
    case ManageAppointments = 'appointments.manage';
    case CheckEligibility = 'eligibility.check';
    case ManageDeferrals = 'deferrals.manage';
    case ViewDonations = 'donations.view';
    case RecordDonations = 'donations.record';
    case ViewInventory = 'inventory.view';
    case ManageInventory = 'inventory.manage';
    case ManageInventoryTransfers = 'inventory_transfers.manage';
    case ViewCampaigns = 'campaigns.view';
    case ManageCampaigns = 'campaigns.manage';
    case ViewArticles = 'articles.view';
    case ManageArticles = 'articles.manage';
    case ManageNotifications = 'notifications.manage';
    case ViewReports = 'reports.view';
    case ExportReports = 'reports.export';
    case ManageLoyalty = 'loyalty.manage';
    case ViewAudits = 'audits.view';
    case ManageBackups = 'backups.manage';
    case ManageSettings = 'settings.manage';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $permission): string => $permission->value,
            self::cases(),
        );
    }
}
