<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\RoleName;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final readonly class AuthenticateMobileDonor
{
    public function __construct(
        private AuditLogger $auditLogger,
        private EnsureDonorProfile $ensureDonorProfile,
    ) {}

    /** @throws ValidationException */
    public function handle(string $identifier, string $password): User
    {
        $normalizedIdentifier = trim($identifier);
        $user = User::query()
            ->where('email', Str::lower($normalizedIdentifier))
            ->orWhere('phone', $normalizedIdentifier)
            ->first();

        if ($user === null
            || ! Hash::check($password, $user->password)
            || ! $user->is_active
            || ! $user->hasRole(RoleName::Donor->value)) {
            throw ValidationException::withMessages([
                'identifier' => [trans('api.mobile_credentials_invalid')],
            ]);
        }

        $this->ensureDonorProfile->handle($user);
        $this->auditLogger->record(
            actor: $user,
            action: 'mobile.password_authenticated',
            subject: $user,
        );

        return $user->load(['roles', 'donorProfile.preferredCenter']);
    }
}
