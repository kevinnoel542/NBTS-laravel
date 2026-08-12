<?php

namespace Database\Seeders;

use App\Actions\Auth\EnsureDonorProfile;
use App\AppointmentStatus;
use App\BloodGroup;
use App\DonorIdentityCheckStatus;
use App\DonorIdentityMethod;
use App\EligibilityStatus;
use App\Models\Appointment;
use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\DonorIdentityCheck;
use App\Models\EligibilityRecord;
use App\Models\OrganizationUnit;
use App\Models\ScreeningProtocol;
use App\Models\StaffAssignment;
use App\Models\User;
use App\OrganizationUnitType;
use App\RoleName;
use App\StaffAssignmentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

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

        $primaryCenter->forceFill(['offline_collection_enabled' => true])->save();

        $admin = $this->demoUser(
            email: 'admin@nbts.test',
            name: 'NBTS System Admin',
            phone: '+255700000001',
            legacyRole: 'admin',
            role: RoleName::SuperAdmin,
        );
        $nbtsAdmin = $this->demoUser(
            email: 'nbts-admin@nbts.test',
            name: 'Rehema Msuya',
            phone: '+255700000005',
            legacyRole: 'admin',
            role: RoleName::NbtsAdmin,
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
        $nbtsAdmin->forceFill(['region' => 'Dar es Salaam'])->save();

        $nationalUnit = OrganizationUnit::query()
            ->where('type', OrganizationUnitType::National)
            ->firstOrFail();
        $centerUnit = $primaryCenter->organizationUnit()->firstOrFail();

        foreach ([
            [$admin, RoleName::SuperAdmin, $nationalUnit],
            [$nbtsAdmin, RoleName::NbtsAdmin, $nationalUnit],
            [$manager, RoleName::CenterManager, $centerUnit],
            [$staff, RoleName::CenterStaff, $centerUnit],
        ] as [$user, $assignmentRole, $organizationUnit]) {
            $this->staffAssignment($user, $assignmentRole, $organizationUnit);
        }

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
        $profile->forceFill([
            'eligibility_status' => EligibilityStatus::Eligible,
            'next_eligible_donation_date' => null,
            'preferred_center_id' => $primaryCenter->id,
            'privacy_notice_version' => config('phase-six.privacy_notice_version'),
            'consented_at' => now(),
            'consent_recorded_by' => $staff->id,
            'consent_source' => 'local_demo_seed',
            'identity_review_required' => false,
        ])->save();

        $donor->forceFill(['blood_group' => BloodGroup::OPositive])->save();

        $appointment = Appointment::query()->updateOrCreate(
            [
                'notes' => 'NBTS demo workflow '.today()->toDateString(),
                'user_id' => $donor->id,
            ],
            [
                'blood_center_id' => $primaryCenter->id,
                'confirmed_at' => now(),
                'checked_in_at' => now(),
                'handled_by' => $staff->id,
                'scheduled_at' => today()->setTime(9, 30),
                'status' => AppointmentStatus::CheckedIn,
            ],
        );

        $identity = DonorIdentityCheck::query()->updateOrCreate(
            [
                'appointment_id' => $appointment->id,
                'donor_id' => $donor->id,
            ],
            [
                'blood_center_id' => $primaryCenter->id,
                'method' => DonorIdentityMethod::DonorId,
                'reference_suffix' => mb_substr($profile->donor_id, -12),
                'status' => DonorIdentityCheckStatus::Confirmed,
                'confirmed_by' => $staff->id,
                'confirmed_at' => now(),
                'expires_at' => now()->addHours((int) config('phase-six.identity_confirmation_hours', 12)),
                'source_mode' => 'online',
                'failure_reason' => null,
            ],
        );

        $protocol = ScreeningProtocol::query()->effective()->latest('version')->first();

        EligibilityRecord::query()->updateOrCreate(
            [
                'notes' => 'NBTS demo screening '.today()->toDateString(),
                'user_id' => $donor->id,
            ],
            [
                'age' => $donor->date_of_birth?->age,
                'answers' => ['consent_confirmed' => true, 'feels_well' => true, 'self_exclusion' => false],
                'appointment_id' => $appointment->id,
                'blood_center_id' => $primaryCenter->id,
                'checked_by' => $staff->id,
                'decision_code' => 'local_demo_eligible',
                'identity_check_id' => $identity->id,
                'questionnaire_version' => $protocol === null ? null : $protocol->code.'@'.$protocol->version,
                'rule_version' => $protocol === null ? null : $protocol->code.'@'.$protocol->version,
                'screened_at' => now(),
                'screening_protocol_id' => $protocol?->id,
                'source_mode' => 'online',
                'status' => EligibilityStatus::Eligible,
                'weight_kg' => 64.5,
            ],
        );
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

    private function staffAssignment(
        User $user,
        RoleName $roleName,
        OrganizationUnit $organizationUnit,
    ): StaffAssignment {
        $role = Role::findByName($roleName->value, 'web');

        return StaffAssignment::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'organization_unit_id' => $organizationUnit->id,
                'department_id' => null,
                'starts_at' => null,
            ],
            [
                'work_location_id' => null,
                'shift' => null,
                'ends_at' => null,
                'status' => StaffAssignmentStatus::Active,
                'approved_by' => null,
                'reason' => 'Local compatibility account for controlled construction and browser QA.',
            ],
        );
    }
}
