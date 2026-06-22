<?php

namespace Database\Seeders;

use App\Models\DonorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.manage',
            'roles.manage',
            'donors.view',
            'donors.manage',
            'centers.view',
            'centers.manage',
            'center_staff.manage',
            'appointments.view',
            'appointments.manage',
            'donations.view',
            'donations.record',
            'campaigns.view',
            'campaigns.manage',
            'notifications.manage',
            'inventory.view',
            'inventory.manage',
            'reports.view',
            'eligibility.check',
            'deferrals.manage',
            'loyalty.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $nbtsAdmin = Role::findOrCreate('nbts_admin', 'web');
        $centerManager = Role::findOrCreate('center_manager', 'web');
        $centerStaff = Role::findOrCreate('center_staff', 'web');
        $donor = Role::findOrCreate('donor', 'web');

        $superAdmin->syncPermissions($permissions);
        $nbtsAdmin->syncPermissions([
            'users.view',
            'donors.view',
            'donors.manage',
            'centers.view',
            'centers.manage',
            'center_staff.manage',
            'appointments.view',
            'appointments.manage',
            'donations.view',
            'donations.record',
            'campaigns.view',
            'campaigns.manage',
            'notifications.manage',
            'inventory.view',
            'reports.view',
            'eligibility.check',
            'deferrals.manage',
            'loyalty.manage',
        ]);
        $centerManager->syncPermissions([
            'donors.view',
            'centers.view',
            'center_staff.manage',
            'appointments.view',
            'appointments.manage',
            'donations.view',
            'donations.record',
            'inventory.view',
            'reports.view',
            'eligibility.check',
            'deferrals.manage',
        ]);
        $centerStaff->syncPermissions([
            'donors.view',
            'appointments.view',
            'donations.view',
            'donations.record',
            'inventory.view',
            'eligibility.check',
        ]);
        $donor->syncPermissions([]);

        $admin = User::where('email', 'admin@nbts.com')->first();

        if ($admin) {
            $admin->syncRoles([$superAdmin]);
            $admin->forceFill(['role' => 'admin', 'is_active' => true])->save();
        }

        User::query()
            ->where('role', 'donor')
            ->whereDoesntHave('roles')
            ->each(function (User $user) use ($donor): void {
                $user->assignRole($donor);
            });

        User::query()
            ->where('role', 'staff')
            ->whereDoesntHave('roles')
            ->each(function (User $user) use ($centerStaff): void {
                $user->assignRole($centerStaff);
            });

        User::role('donor')
            ->whereDoesntHave('donorProfile')
            ->each(function (User $user): void {
                $user->donorProfile()->create([
                    'donor_id' => $this->generateDonorId(),
                    'blood_group_status' => $user->blood_group ? 'user_selected' : 'unknown',
                ]);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function generateDonorId(): string
    {
        do {
            $donorId = 'DNR-' . now()->format('Y') . '-' . str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (DonorProfile::where('donor_id', $donorId)->exists());

        return $donorId;
    }
}
