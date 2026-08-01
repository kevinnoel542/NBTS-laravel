<?php

namespace Database\Seeders;

use App\Actions\Auth\EnsureDonorProfile;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use App\RoleName;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Password123!';

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $primaryCenter = BloodCenter::query()->orderBy('id')->first();

        if ($primaryCenter === null) {
            return;
        }

        $admin = $this->demoUser(
            email: 'admin@nbts.test',
            name: 'NBTS System Admin',
            phone: '+255700000001',
            legacyRole: 'admin',
            role: RoleName::SuperAdmin,
        );
        $manager = $this->demoUser(
            email: 'manager@nbts.test',
            name: 'Asha Mrema',
            phone: '+255700000002',
            legacyRole: 'staff',
            role: RoleName::CenterManager,
        );
        $staff = $this->demoUser(
            email: 'staff@nbts.test',
            name: 'Joseph Kileo',
            phone: '+255700000003',
            legacyRole: 'staff',
            role: RoleName::CenterStaff,
        );
        $donor = $this->demoUser(
            email: 'donor@nbts.test',
            name: 'Neema John',
            phone: '+255700000101',
            legacyRole: 'donor',
            role: RoleName::Donor,
        );

        $admin->forceFill(['region' => 'Dar es Salaam'])->save();

        foreach ([
            [$manager, RoleName::CenterManager],
            [$staff, RoleName::CenterStaff],
        ] as [$user, $position]) {
            CenterStaff::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'blood_center_id' => $primaryCenter->id,
                ],
                [
                    'position' => $position->value,
                    'is_active' => true,
                ],
            );
        }

        $donor->forceFill([
            'gender' => 'female',
            'date_of_birth' => '1995-04-17',
            'region' => 'Dar es Salaam',
            'address' => 'Sinza, Dar es Salaam',
        ])->save();

        $profile = app(EnsureDonorProfile::class)->handle($donor);
        $profile->forceFill(['preferred_center_id' => $primaryCenter->id])->save();
    }

    private function demoUser(
        string $email,
        string $name,
        string $phone,
        string $legacyRole,
        RoleName $role,
    ): User {
        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $user = new User;
            $user->forceFill([
                'email' => $email,
                'password' => Hash::make(self::DEMO_PASSWORD),
                'email_verified_at' => now(),
            ]);
        }

        $user->forceFill([
            'name' => $name,
            'phone' => $phone,
            'role' => $legacyRole,
            'is_active' => true,
            'locale' => $user->locale ?: 'en',
        ])->save();
        $user->syncRoles([$role->value]);

        return $user;
    }
}
