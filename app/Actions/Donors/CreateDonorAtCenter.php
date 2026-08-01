<?php

namespace App\Actions\Donors;

use App\Actions\Auth\EnsureDonorProfile;
use App\Models\BloodCenter;
use App\Models\DonorProfile;
use App\Models\User;
use App\RoleName;
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
     *     locale?: string
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

        return DB::transaction(function () use ($actor, $bloodCenter, $data, $phone, $email): User {
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
            $profile->forceFill(['preferred_center_id' => $bloodCenter->id])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: 'donor.registered_at_center',
                subject: $user,
                bloodCenter: $bloodCenter,
                metadata: [
                    'donor_id' => $profile->donor_id,
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
