<?php

namespace App\Actions\Auth;

use App\Models\User;
use App\RoleName;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateMobileDonor
{
    public function __construct(
        private EnsureDonorProfile $ensureDonorProfile,
        private AuditLogger $auditLogger,
    ) {}

    /**
     * @param  array{
     *     name: string,
     *     email: string|null,
     *     phone: string,
     *     password: string,
     *     blood_group: string,
     *     gender: string,
     *     region: string,
     *     date_of_birth: string
     * }  $data
     */
    public function handle(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $email = is_string($data['email']) && $data['email'] !== ''
                ? Str::lower(trim($data['email']))
                : null;

            $user = User::unguarded(fn (): User => User::query()->create([
                'name' => trim($data['name']),
                'email' => $email,
                'phone' => trim($data['phone']),
                'password' => $data['password'],
                'blood_group' => $data['blood_group'],
                'gender' => $data['gender'],
                'region' => trim($data['region']),
                'date_of_birth' => $data['date_of_birth'],
                'role' => RoleName::Donor->legacyValue(),
                'is_active' => true,
                'locale' => (string) config('app.locale', 'en'),
            ]));

            $user->assignRole(RoleName::Donor->value);
            $this->ensureDonorProfile->handle($user);

            $this->auditLogger->record(
                actor: $user,
                action: 'mobile.password_account_created',
                subject: $user,
            );

            return $user->load(['roles', 'donorProfile.preferredCenter']);
        }, attempts: 3);
    }
}
