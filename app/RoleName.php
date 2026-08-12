<?php

namespace App;

enum RoleName: string
{
    case SuperAdmin = 'super_admin';
    case NbtsAdmin = 'nbts_admin';
    case CenterManager = 'center_manager';
    case CenterStaff = 'center_staff';
    case Donor = 'donor';
    case IctSecurityOperator = 'ict_security_operator';
    case NationalOperationsAdministrator = 'national_operations_administrator';
    case NationalQualityHaemovigilanceOfficer = 'national_quality_haemovigilance_officer';
    case NationalInventoryLogisticsCoordinator = 'national_inventory_logistics_coordinator';
    case NationalDonorEngagementContentOfficer = 'national_donor_engagement_content_officer';
    case DataProtectionGovernanceOfficer = 'data_protection_governance_officer';
    case NationalAuditorInspector = 'national_auditor_inspector';
    case ReceptionOfficer = 'reception_officer';
    case ScreeningCounsellingOfficer = 'screening_counselling_officer';
    case CollectionPhlebotomyOfficer = 'collection_phlebotomy_officer';
    case LaboratoryTechnician = 'laboratory_technician';
    case LaboratoryApproverQualityOfficer = 'laboratory_approver_quality_officer';
    case ComponentProcessingOfficer = 'component_processing_officer';
    case InventoryOfficer = 'inventory_officer';
    case LogisticsColdChainOfficer = 'logistics_cold_chain_officer';
    case CenterHaemovigilanceQualityOfficer = 'center_haemovigilance_quality_officer';
    case CenterReadOnlyAuditor = 'center_read_only_auditor';
    case HospitalClinicianRequester = 'hospital_clinician_requester';
    case HospitalBloodBankOfficer = 'hospital_blood_bank_officer';
    case CompatibilityCrossmatchOfficer = 'compatibility_crossmatch_officer';
    case TransfusionNurseOfficer = 'transfusion_nurse_officer';
    case HospitalHaemovigilanceOfficer = 'hospital_haemovigilance_officer';
    case HospitalReadOnlyReviewer = 'hospital_read_only_reviewer';

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(
            static fn (self $role): string => $role->value,
            self::cases(),
        );
    }

    /** @return list<string> */
    public static function targetValues(): array
    {
        return array_values(array_filter(
            self::values(),
            static fn (string $role): bool => ! in_array($role, [self::NbtsAdmin->value, self::CenterStaff->value], true),
        ));
    }

    /**
     * @return list<string>
     */
    public static function staffValues(): array
    {
        return array_values(array_filter(
            self::values(),
            static fn (string $role): bool => $role !== self::Donor->value,
        ));
    }

    /** @return list<string> */
    public static function nationalValues(): array
    {
        return [
            self::SuperAdmin->value,
            self::NbtsAdmin->value,
            self::IctSecurityOperator->value,
            self::NationalOperationsAdministrator->value,
            self::NationalQualityHaemovigilanceOfficer->value,
            self::NationalInventoryLogisticsCoordinator->value,
            self::NationalDonorEngagementContentOfficer->value,
            self::DataProtectionGovernanceOfficer->value,
            self::NationalAuditorInspector->value,
        ];
    }

    /** @return list<string> */
    public static function centerValues(): array
    {
        return [
            self::CenterManager->value,
            self::CenterStaff->value,
            self::ReceptionOfficer->value,
            self::ScreeningCounsellingOfficer->value,
            self::CollectionPhlebotomyOfficer->value,
            self::LaboratoryTechnician->value,
            self::LaboratoryApproverQualityOfficer->value,
            self::ComponentProcessingOfficer->value,
            self::InventoryOfficer->value,
            self::LogisticsColdChainOfficer->value,
            self::CenterHaemovigilanceQualityOfficer->value,
            self::CenterReadOnlyAuditor->value,
        ];
    }

    /** @return list<string> */
    public static function hospitalValues(): array
    {
        return [
            self::HospitalClinicianRequester->value,
            self::HospitalBloodBankOfficer->value,
            self::CompatibilityCrossmatchOfficer->value,
            self::TransfusionNurseOfficer->value,
            self::HospitalHaemovigilanceOfficer->value,
            self::HospitalReadOnlyReviewer->value,
        ];
    }

    public function dashboardConfiguration(): string
    {
        return match ($this) {
            self::SuperAdmin, self::IctSecurityOperator => 'system_control',
            self::NbtsAdmin, self::NationalOperationsAdministrator => 'national_operations',
            self::NationalQualityHaemovigilanceOfficer,
            self::DataProtectionGovernanceOfficer,
            self::NationalAuditorInspector => 'national_quality_governance',
            self::NationalInventoryLogisticsCoordinator => 'national_inventory_logistics',
            self::NationalDonorEngagementContentOfficer => 'engagement_content',
            self::CenterManager => 'center_management',
            self::CenterStaff, self::ReceptionOfficer => 'reception',
            self::ScreeningCounsellingOfficer => 'screening_counselling',
            self::CollectionPhlebotomyOfficer => 'collection',
            self::LaboratoryTechnician,
            self::LaboratoryApproverQualityOfficer,
            self::ComponentProcessingOfficer => 'laboratory_components',
            self::InventoryOfficer, self::LogisticsColdChainOfficer => 'center_inventory_logistics',
            self::CenterHaemovigilanceQualityOfficer,
            self::CenterReadOnlyAuditor => 'center_quality_haemovigilance',
            self::HospitalClinicianRequester,
            self::HospitalBloodBankOfficer,
            self::CompatibilityCrossmatchOfficer,
            self::TransfusionNurseOfficer,
            self::HospitalHaemovigilanceOfficer,
            self::HospitalReadOnlyReviewer => 'hospital_operations',
            self::Donor => 'donor_home',
        };
    }

    /** @return list<OrganizationUnitType> */
    public function organizationUnitTypes(): array
    {
        return match (true) {
            in_array($this->value, self::nationalValues(), true) => [OrganizationUnitType::National],
            in_array($this->value, self::centerValues(), true) => [OrganizationUnitType::BloodCenter],
            in_array($this->value, self::hospitalValues(), true) => [OrganizationUnitType::Hospital],
            default => [],
        };
    }

    public function isClinical(): bool
    {
        return in_array($this, [
            self::ScreeningCounsellingOfficer,
            self::CollectionPhlebotomyOfficer,
            self::LaboratoryTechnician,
            self::LaboratoryApproverQualityOfficer,
            self::ComponentProcessingOfficer,
            self::CompatibilityCrossmatchOfficer,
            self::TransfusionNurseOfficer,
            self::HospitalHaemovigilanceOfficer,
        ], true);
    }

    /**
     * Map the canonical role to the deployed transitional user role.
     */
    public function legacyValue(): string
    {
        return match ($this) {
            self::Donor => 'donor',
            self::SuperAdmin,
            self::NbtsAdmin,
            self::IctSecurityOperator,
            self::NationalOperationsAdministrator,
            self::NationalQualityHaemovigilanceOfficer,
            self::NationalInventoryLogisticsCoordinator,
            self::NationalDonorEngagementContentOfficer,
            self::DataProtectionGovernanceOfficer,
            self::NationalAuditorInspector => 'admin',
            default => 'staff',
        };
    }
}
