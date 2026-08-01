<?php

namespace App\Models;

use App\RoleName;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $phone
 * @property string|null $blood_group
 * @property string|null $gender
 * @property Carbon|null $date_of_birth
 * @property string|null $region
 * @property Carbon|null $last_donation
 * @property string|null $address
 * @property string|null $profile_photo_path
 * @property string $role
 * @property bool $is_active
 * @property string $locale
 * @property string|null $firebase_uid
 * @property string|null $firebase_provider
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'blood_group',
    'gender',
    'date_of_birth',
    'region',
    'last_donation',
    'address',
    'profile_photo_path',
    'locale',
])]
#[Hidden([
    'password',
    'remember_token',
    'firebase_uid',
    'two_factor_secret',
    'two_factor_recovery_codes',
])]
class User extends Authenticatable implements HasLocalePreference, MustVerifyEmailContract, PasskeyUser
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles {
        hasPermissionTo as protected hasPermissionToIgnoringAccountStatus;
    }
    use MustVerifyEmailTrait;
    use Notifiable;
    use PasskeyAuthenticatable;
    use TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_donation' => 'date',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function canAccessStaffAccount(): bool
    {
        return $this->is_active && $this->hasAnyRole(RoleName::staffValues());
    }

    /**
     * Prevent inactive accounts from retaining direct or role permissions.
     */
    public function hasPermissionTo(mixed $permission, ?string $guardName = null): bool
    {
        return $this->is_active
            && $this->hasPermissionToIgnoringAccountStatus($permission, $guardName);
    }

    public function hasNationalScope(): bool
    {
        return $this->is_active && $this->hasAnyRole([
            RoleName::SuperAdmin->value,
            RoleName::NbtsAdmin->value,
        ]);
    }

    public function hasCenterAccess(BloodCenter|int $bloodCenter): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->hasNationalScope()) {
            return true;
        }

        if (! $this->hasAnyRole([
            RoleName::CenterManager->value,
            RoleName::CenterStaff->value,
        ])) {
            return false;
        }

        $bloodCenterId = $bloodCenter instanceof BloodCenter ? $bloodCenter->id : $bloodCenter;

        return $this->centerStaffAssignments()
            ->where('blood_center_id', $bloodCenterId)
            ->where('is_active', true)
            ->exists();
    }

    /** @return HasMany<CenterStaff, $this> */
    public function centerStaffAssignments(): HasMany
    {
        return $this->hasMany(CenterStaff::class);
    }

    /** @return BelongsToMany<BloodCenter, $this> */
    public function assignedBloodCenters(): BelongsToMany
    {
        return $this->belongsToMany(BloodCenter::class, 'center_staff')
            ->withPivot(['position', 'is_active'])
            ->withTimestamps();
    }

    /** @return HasOne<DonorProfile, $this> */
    public function donorProfile(): HasOne
    {
        return $this->hasOne(DonorProfile::class);
    }

    /** @return HasMany<Appointment, $this> */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /** @return HasMany<Donation, $this> */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    /** @return HasMany<EligibilityRecord, $this> */
    public function eligibilityRecords(): HasMany
    {
        return $this->hasMany(EligibilityRecord::class);
    }

    /** @return HasMany<Deferral, $this> */
    public function deferrals(): HasMany
    {
        return $this->hasMany(Deferral::class);
    }

    /** @return HasMany<BloodUnit, $this> */
    public function donatedBloodUnits(): HasMany
    {
        return $this->hasMany(BloodUnit::class, 'donor_id');
    }

    public function preferredLocale(): string
    {
        $supportedLocales = config('app.supported_locales', ['en', 'sw']);

        return is_array($supportedLocales) && in_array($this->locale, $supportedLocales, true)
            ? $this->locale
            : (string) config('app.fallback_locale', 'en');
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
