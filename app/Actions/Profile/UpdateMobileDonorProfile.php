<?php

namespace App\Actions\Profile;

use App\Actions\Auth\EnsureDonorProfile;
use App\BloodGroup;
use App\BloodGroupStatus;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class UpdateMobileDonorProfile
{
    public function __construct(
        private EnsureDonorProfile $ensureDonorProfile,
        private AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function handle(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $donorProfile = $this->ensureDonorProfile->handle($lockedUser);

            if (isset($data['blood_group'])) {
                $bloodGroup = BloodGroup::from($data['blood_group']);

                if ($donorProfile->blood_group_status === BloodGroupStatus::StaffVerified
                    && $lockedUser->blood_group !== $bloodGroup) {
                    throw ValidationException::withMessages([
                        'blood_group' => [trans('api.staff_verified_blood_group_locked')],
                    ]);
                }
            }

            $userData = Arr::only($data, [
                'name',
                'phone',
                'blood_group',
                'gender',
                'date_of_birth',
                'region',
                'address',
            ]);

            if (isset($data['language'])) {
                $userData['locale'] = $data['language'];
            }

            if ($userData !== []) {
                $lockedUser->fill($userData)->save();
            }

            $profileData = Arr::only($data, [
                'preferred_center_id',
                'emergency_contact_name',
                'emergency_contact_phone',
                'push_notifications_enabled',
                'email_notifications_enabled',
                'sms_reminders_enabled',
                'share_anonymized_data',
                'language',
            ]);

            if (isset($data['blood_group'])
                && $donorProfile->blood_group_status !== BloodGroupStatus::StaffVerified) {
                $profileData['blood_group_status'] = BloodGroupStatus::UserSelected;
            }

            if ($profileData !== []) {
                $donorProfile->fill($profileData)->save();
            }

            $this->auditLogger->record(
                actor: $lockedUser,
                action: 'mobile.profile_updated',
                subject: $lockedUser,
                metadata: [
                    'changed_fields' => array_keys($data),
                ],
            );

            return $lockedUser->load(['roles', 'donorProfile.preferredCenter']);
        }, attempts: 3);
    }
}
