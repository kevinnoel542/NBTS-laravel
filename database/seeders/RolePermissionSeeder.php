<?php

namespace Database\Seeders;

use App\Models\User;
use App\PermissionName;
use App\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::findOrCreate($permission->value, 'web');
        }

        $permissionRegistrar->forgetCachedPermissions();

        foreach ($this->rolePermissions() as $roleName => $permissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($permissions);
        }

        User::query()
            ->whereDoesntHave('roles')
            ->lazyById()
            ->each(function (User $user): void {
                $user->assignRole(match ($user->role) {
                    'admin' => RoleName::NbtsAdmin->value,
                    'staff' => RoleName::CenterStaff->value,
                    default => RoleName::Donor->value,
                });
            });

        $permissionRegistrar->forgetCachedPermissions();
    }

    /**
     * @return array<string, list<string>>
     */
    private function rolePermissions(): array
    {
        return [
            RoleName::SuperAdmin->value => PermissionName::values(),
            RoleName::NbtsAdmin->value => $this->values([
                PermissionName::ViewUsers,
                PermissionName::ViewDonors,
                PermissionName::RegisterDonors,
                PermissionName::ManageDonors,
                PermissionName::ViewCenters,
                PermissionName::ManageCenters,
                PermissionName::ManageCenterStaff,
                PermissionName::ViewAppointments,
                PermissionName::ManageAppointments,
                PermissionName::CheckEligibility,
                PermissionName::ManageDeferrals,
                PermissionName::ViewDonations,
                PermissionName::RecordDonations,
                PermissionName::ViewInventory,
                PermissionName::ManageInventory,
                PermissionName::ManageInventoryTransfers,
                PermissionName::ViewCampaigns,
                PermissionName::ManageCampaigns,
                PermissionName::ViewArticles,
                PermissionName::ManageArticles,
                PermissionName::ManageNotifications,
                PermissionName::ViewReports,
                PermissionName::ExportReports,
                PermissionName::ManageLoyalty,
                PermissionName::ViewAudits,
            ]),
            RoleName::CenterManager->value => $this->values([
                PermissionName::ViewDonors,
                PermissionName::RegisterDonors,
                PermissionName::ViewCenters,
                PermissionName::ManageCenterStaff,
                PermissionName::ViewAppointments,
                PermissionName::ManageAppointments,
                PermissionName::CheckEligibility,
                PermissionName::ManageDeferrals,
                PermissionName::ViewDonations,
                PermissionName::RecordDonations,
                PermissionName::ViewInventory,
                PermissionName::ManageInventory,
                PermissionName::ManageInventoryTransfers,
                PermissionName::ViewCampaigns,
                PermissionName::ViewReports,
                PermissionName::ExportReports,
            ]),
            RoleName::CenterStaff->value => $this->values([
                PermissionName::ViewDonors,
                PermissionName::RegisterDonors,
                PermissionName::ViewCenters,
                PermissionName::ViewAppointments,
                PermissionName::CheckEligibility,
                PermissionName::ViewDonations,
                PermissionName::RecordDonations,
                PermissionName::ViewInventory,
            ]),
            RoleName::Donor->value => [],
        ];
    }

    /**
     * @param  list<PermissionName>  $permissions
     * @return list<string>
     */
    private function values(array $permissions): array
    {
        return array_map(
            static fn (PermissionName $permission): string => $permission->value,
            $permissions,
        );
    }
}
