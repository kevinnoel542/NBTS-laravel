<?php

namespace App\Actions\Donors;

use App\Actions\Auth\EnsureDonorProfile;
use App\Models\BloodCenter;
use App\Models\DonorDuplicateCase;
use App\Models\DonorProfile;
use App\Models\User;
use App\RoleName;
use App\Services\DonorDuplicateDetector;
use App\Support\AuditLogger;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class CreateDonorAtCenter
{
    public function __construct(
        private EnsureDonorProfile $ensureDonorProfile,
        private DonorDuplicateDetector $duplicateDetector,
        private AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     phone: string,
     *     email?: string|null,
     *     blood_group?: string|null,
     *     gender?: string|null,
     *     date_of_birth?: string|null,
     *     region?: string|null,
     *     address?: string|null,
     *     locale?: string,
     *     privacy_notice_version?: string|null,
     *     consent_confirmed?: bool,
     *     allow_possible_duplicate?: bool,
     *     possible_duplicate_reason?: string|null,
     *     push_notifications_enabled?: bool,
     *     email_notifications_enabled?: bool,
     *     sms_reminders_enabled?: bool
     * }  $data
     */
    public function handle(User $actor, BloodCenter $bloodCenter, array $data): User
    {
        Gate::forUser($actor)->authorize('registerAt', [DonorProfile::class, $bloodCenter]);

        $phone = trim($data['phone']);
        $email = isset($data['email']) && trim((string) $data['email']) !== ''
            ? Str::lower(trim((string) $data['email']))
            : null;

        $this->ensureIdentityIsAvailable($phone, $email);

        $matches = $this->duplicateDetector->matches([
            'name' => $data['name'],
            'phone' => $phone,
            'email' => $email,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'region' => $data['region'] ?? null,
        ]);
        $usesPhaseSixConsent = array_key_exists('consent_confirmed', $data)
            || array_key_exists('privacy_notice_version', $data);

        if ($usesPhaseSixConsent && ! ($data['consent_confirmed'] ?? false)) {
            throw ValidationException::withMessages([
                'consent_confirmed' => ['The donor must acknowledge the current privacy notice before registration.'],
            ]);
        }

        if ($usesPhaseSixConsent
            && ($data['privacy_notice_version'] ?? null) !== config('phase-six.privacy_notice_version')) {
            throw ValidationException::withMessages([
                'privacy_notice_version' => ['The current construction privacy notice must be presented and acknowledged.'],
            ]);
        }

        if ($matches->isNotEmpty() && ! ($data['allow_possible_duplicate'] ?? false)) {
            throw ValidationException::withMessages([
                'possible_duplicate' => ['A possible donor match was found. Review it or provide a documented override to continue.'],
            ]);
        }

        if ($matches->isNotEmpty() && mb_strlen(trim((string) ($data['possible_duplicate_reason'] ?? ''))) < 10) {
            throw ValidationException::withMessages([
                'possible_duplicate_reason' => ['A reason of at least 10 characters is required to register a possible duplicate.'],
            ]);
        }

        return DB::transaction(function () use ($actor, $bloodCenter, $data, $phone, $email, $matches, $usesPhaseSixConsent): User {
            BloodCenter::query()->lockForUpdate()->findOrFail($bloodCenter->id);

            $user = new User;
            $user->forceFill(Arr::only($data, [
                'name',
                'blood_group',
                'gender',
                'date_of_birth',
                'region',
                'address',
                'locale',
            ]) + [
                'email' => $email,
                'phone' => $phone,
                'password' => Str::password(48),
                'role' => RoleName::Donor->legacyValue(),
                'is_active' => true,
                'locale' => $data['locale'] ?? 'en',
            ])->save();
            $user->syncRoles([RoleName::Donor->value]);

            $profile = $this->ensureDonorProfile->handle($user);
            $profile->forceFill([
                'preferred_center_id' => $bloodCenter->id,
                'privacy_notice_version' => $usesPhaseSixConsent ? ($data['privacy_notice_version'] ?? null) : null,
                'consented_at' => $usesPhaseSixConsent ? now() : null,
                'consent_recorded_by' => $usesPhaseSixConsent ? $actor->id : null,
                'consent_source' => $usesPhaseSixConsent ? 'staff_reception' : null,
                'identity_review_required' => $matches->isNotEmpty(),
                'push_notifications_enabled' => $data['push_notifications_enabled'] ?? true,
                'email_notifications_enabled' => $data['email_notifications_enabled'] ?? true,
                'sms_reminders_enabled' => $data['sms_reminders_enabled'] ?? true,
            ])->save();

            $matches->each(function (array $match) use ($actor, $bloodCenter, $user): void {
                DonorDuplicateCase::query()->create([
                    'primary_donor_id' => $match['donor']->id,
                    'candidate_donor_id' => $user->id,
                    'blood_center_id' => $bloodCenter->id,
                    'match_signals' => $match['signals'],
                    'match_score' => $match['score'],
                    'detected_by' => $actor->id,
                ]);
            });

            $this->auditLogger->record(
                actor: $actor,
                action: 'donor.registered_at_center',
                subject: $user,
                bloodCenter: $bloodCenter,
                metadata: [
                    'donor_id' => $profile->donor_id,
                    'possible_duplicate_count' => $matches->count(),
                    'privacy_notice_version' => $profile->privacy_notice_version,
                    'registration_channel' => 'staff_reception',
                ],
            );

            return $user->load(['roles', 'donorProfile.preferredCenter']);
        }, attempts: 3);
    }

    private function ensureIdentityIsAvailable(string $phone, ?string $email): void
    {
        $conflicts = User::query()
            ->where('phone', $phone)
            ->when($email !== null, fn ($query) => $query->orWhere('email', $email))
            ->first(['phone', 'email']);

        if ($conflicts === null) {
            return;
        }

        $field = $conflicts->phone === $phone ? 'phone' : 'email';

        throw ValidationException::withMessages([
            $field => [__('validation.unique', ['attribute' => __($field)])],
        ]);
    }
}
