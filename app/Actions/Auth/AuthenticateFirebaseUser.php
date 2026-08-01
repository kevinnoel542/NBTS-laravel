<?php

namespace App\Actions\Auth;

use App\Firebase\VerifiedFirebaseIdentity;
use App\Models\User;
use App\RoleName;
use App\Support\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateFirebaseUser
{
    public function __construct(
        private AuditLogger $auditLogger,
        private EnsureDonorProfile $ensureDonorProfile,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function handle(VerifiedFirebaseIdentity $identity): User
    {
        return DB::transaction(function () use ($identity): User {
            $userByUid = User::query()
                ->where('firebase_uid', $identity->uid)
                ->lockForUpdate()
                ->first();

            $normalizedEmail = $this->verifiedEmail($identity, required: $userByUid === null);
            $userByEmail = $normalizedEmail === null
                ? null
                : User::query()->where('email', $normalizedEmail)->lockForUpdate()->first();

            if ($userByUid !== null && $userByEmail !== null && ! $userByUid->is($userByEmail)) {
                throw ValidationException::withMessages([
                    'firebase_id_token' => [trans('api.firebase_identity_conflict')],
                ]);
            }

            $user = $userByUid ?? $userByEmail;
            $wasCreated = $user === null;

            if ($user === null) {
                $user = $this->createDonor($identity, $normalizedEmail);
            } else {
                $this->assertCanUseDonorMobileAuthentication($user, $identity);
                $this->linkFirebaseIdentity($user, $identity);
            }

            $user->assignRole(RoleName::Donor->value);
            $this->ensureDonorProfile->handle($user);

            $this->auditLogger->record(
                actor: $user,
                action: $wasCreated ? 'mobile.firebase_account_created' : 'mobile.firebase_authenticated',
                subject: $user,
                metadata: [
                    'provider' => $identity->provider,
                ],
            );

            return $user->load(['roles', 'donorProfile.preferredCenter']);
        }, attempts: 3);
    }

    private function verifiedEmail(VerifiedFirebaseIdentity $identity, bool $required): ?string
    {
        if ($identity->email === null || ! $identity->emailVerified) {
            if ($required) {
                throw ValidationException::withMessages([
                    'firebase_id_token' => [trans('api.firebase_verified_email_required')],
                ]);
            }

            return null;
        }

        return Str::lower(trim($identity->email));
    }

    private function createDonor(VerifiedFirebaseIdentity $identity, ?string $email): User
    {
        if ($email === null) {
            throw ValidationException::withMessages([
                'firebase_id_token' => [trans('api.firebase_verified_email_required')],
            ]);
        }

        return User::unguarded(fn (): User => User::query()->create([
            'name' => $identity->name ?? Str::before($email, '@'),
            'email' => $email,
            'email_verified_at' => now(),
            'password' => Str::random(64),
            'profile_photo_path' => $identity->photoUrl,
            'role' => RoleName::Donor->legacyValue(),
            'is_active' => true,
            'locale' => (string) config('app.locale', 'en'),
            'firebase_uid' => $identity->uid,
            'firebase_provider' => $identity->provider,
        ]));
    }

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    private function assertCanUseDonorMobileAuthentication(User $user, VerifiedFirebaseIdentity $identity): void
    {
        if (! $user->is_active) {
            throw new AuthorizationException(trans('api.mobile_account_inactive'));
        }

        if (! $user->hasRole(RoleName::Donor->value)) {
            throw new AuthorizationException(trans('api.mobile_staff_link_denied'));
        }

        if ($user->firebase_uid !== null && $user->firebase_uid !== $identity->uid) {
            throw ValidationException::withMessages([
                'firebase_id_token' => [trans('api.firebase_identity_conflict')],
            ]);
        }
    }

    private function linkFirebaseIdentity(User $user, VerifiedFirebaseIdentity $identity): void
    {
        $attributes = [
            'firebase_uid' => $identity->uid,
            'firebase_provider' => $identity->provider,
        ];

        if ($identity->emailVerified && $user->email_verified_at === null) {
            $attributes['email_verified_at'] = now();
        }

        if ($user->profile_photo_path === null && $identity->photoUrl !== null) {
            $attributes['profile_photo_path'] = $identity->photoUrl;
        }

        $user->forceFill($attributes)->save();
    }
}
