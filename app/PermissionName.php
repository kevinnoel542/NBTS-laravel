<?php

namespace App;

enum PermissionName: string
{
    case ViewUsers = 'users.view';
    case ManageUsers = 'users.manage';
    case ManageRoles = 'roles.manage';
    case ViewDonors = 'donors.view';
    case RegisterDonors = 'donors.register';
    case ManageDonors = 'donors.manage';
    case ReviewDonorDuplicates = 'donors.duplicates.review';
    case ConfirmDonorIdentity = 'donors.identity.confirm';
    case ViewCenters = 'centers.view';
    case ManageCenters = 'centers.manage';
    case ManageCenterStaff = 'center_staff.manage';
    case ViewAppointments = 'appointments.view';
    case ManageAppointments = 'appointments.manage';
    case CheckEligibility = 'eligibility.check';
    case ManageDeferrals = 'deferrals.manage';
    case ManageScreeningProtocols = 'screening_protocols.manage';
    case ViewDonations = 'donations.view';
    case RecordDonations = 'donations.record';
    case PrepareCollections = 'collections.prepare';
    case ManageCollectionLabels = 'collection_labels.manage';
    case HandOffSpecimens = 'specimens.handoff';
    case RecordDonorReactions = 'donor_reactions.record';
    case ManageOfflineCollectionDevices = 'offline_collection_devices.manage';
    case ReconcileOfflineCollections = 'offline_collections.reconcile';
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
    case ViewOrganizations = 'organizations.view';
    case ManageOrganizations = 'organizations.manage';
    case ViewDepartments = 'departments.view';
    case ManageDepartments = 'departments.manage';
    case ViewAssignments = 'assignments.view';
    case ManageAssignments = 'assignments.manage';
    case ApproveAssignments = 'assignments.approve';
    case ViewSystemHealth = 'system_health.view';
    case ViewLaboratory = 'laboratory.view';
    case RecordLaboratoryTests = 'laboratory.tests.record';
    case ApproveLaboratoryRelease = 'laboratory.release.approve';
    case ViewComponents = 'components.view';
    case ProcessComponents = 'components.process';
    case ViewLogistics = 'logistics.view';
    case ManageLogistics = 'logistics.manage';
    case ManageColdChain = 'cold_chain.manage';
    case ViewHospitalRequests = 'hospital_requests.view';
    case ManageHospitalRequests = 'hospital_requests.manage';
    case ManageCompatibility = 'compatibility.manage';
    case ManageBloodIssue = 'blood_issue.manage';
    case RecordTransfusions = 'transfusions.record';
    case ViewQuality = 'quality.view';
    case ManageQuality = 'quality.manage';
    case ManageHaemovigilance = 'haemovigilance.manage';
    case ManageRecalls = 'recalls.manage';
    case ManageDataProtection = 'data_protection.manage';
    case ManageIntegrations = 'integrations.manage';
    case ManageSecurityOperations = 'security_operations.manage';
    case ManageChangeControls = 'change_controls.manage';
    case ManageIncidents = 'incidents.manage';

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
