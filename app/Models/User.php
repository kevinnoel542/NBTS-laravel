<?php

namespace App\Models;

use App\BloodGroup;
use App\DonationStatus;
use App\Gender;
use App\RoleName;
use App\Services\ActiveAssignmentContext;
use App\Services\AssignmentAccess;
use Database\Factories\UserFactory;
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
 * @property BloodGroup|null $blood_group
 * @property Gender|null $gender
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
class User extends Authenticatable implements HasLocalePreference, PasskeyUser
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles {
        hasPermissionTo as protected hasPermissionToFromPackage;
    }
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
            'gender' => Gender::class,
            'is_active' => 'boolean',
            'last_donation' => 'date',
            'password' => 'hashed',
            'blood_group' => BloodGroup::class,
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
        return $this->is_active
            && ($this->hasAnyRole(RoleName::staffValues())
                || $this->staffAssignments()->effective()->exists());
    }

    /**
     * Prevent inactive accounts from retaining direct or role permissions.
     */
    public function hasPermissionTo(mixed $permission, ?string $guardName = null): bool
    {
        return app(AssignmentAccess::class)->allows($this, $permission);
    }

    public function hasCompatibilityPermissionTo(mixed $permission, ?string $guardName = null): bool
    {
        return $this->is_active
            && $this->hasPermissionToFromPackage($permission, $guardName);
    }

    public function hasNationalScope(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $assignment = app(ActiveAssignmentContext::class)->selectedAssignment($this);

        if ($assignment instanceof StaffAssignment) {
            return in_array($assignment->role->name, RoleName::nationalValues(), true);
        }

        return $this->hasAnyRole(RoleName::nationalValues());
    }

    public function hasCenterAccess(BloodCenter|int $bloodCenter): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $bloodCenterId = $bloodCenter instanceof BloodCenter ? $bloodCenter->id : $bloodCenter;

        if ($this->hasNationalScope()) {
            return true;
        }

        $activeAssignment = app(ActiveAssignmentContext::class)->selectedAssignment($this);

        if ($activeAssignment instanceof StaffAssignment) {
            return $activeAssignment->organizationUnit->bloodCenter?->id === $bloodCenterId;
        }

        if (! $this->hasAnyRole([
            RoleName::CenterManager->value,
            RoleName::CenterStaff->value,
        ])) {
            return false;
        }

        return $this->centerStaffAssignments()
            ->where('blood_center_id', $bloodCenterId)
            ->where('is_active', true)
            ->exists();
    }

    public function hasDonorAccess(User|int $donor): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $donorId = $donor instanceof User ? $donor->id : $donor;

        if ($this->id === $donorId || $this->hasNationalScope()) {
            return true;
        }

        $activeAssignment = app(ActiveAssignmentContext::class)->selectedAssignment($this);
        $selectedCenterId = $activeAssignment?->organizationUnit->bloodCenter?->id;

        if ($activeAssignment instanceof StaffAssignment && $selectedCenterId === null) {
            return false;
        }

        if (! $activeAssignment instanceof StaffAssignment
            && ! $this->hasAnyRole([
                RoleName::CenterManager->value,
                RoleName::CenterStaff->value,
            ])) {
            return false;
        }

        $centerIds = $selectedCenterId === null
            ? $this->centerStaffAssignments()->where('is_active', true)->select('blood_center_id')
            : BloodCenter::query()->whereKey($selectedCenterId)->select('id');

        return User::query()
            ->whereKey($donorId)
            ->where(function (Builder $query) use ($centerIds): void {
                $query
                    ->whereHas('donorProfile', fn (Builder $profileQuery): Builder => $profileQuery->whereIn('preferred_center_id', clone $centerIds))
                    ->orWhereHas('appointments', fn (Builder $appointmentQuery): Builder => $appointmentQuery->whereIn('blood_center_id', clone $centerIds))
                    ->orWhereHas('donations', fn (Builder $donationQuery): Builder => $donationQuery->whereIn('blood_center_id', clone $centerIds));
            })
            ->exists();
    }

    /** @return HasMany<CenterStaff, $this> */
    public function centerStaffAssignments(): HasMany
    {
        return $this->hasMany(CenterStaff::class);
    }

    /** @return HasMany<StaffAssignment, $this> */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }

    /** @return HasMany<StaffCompetency, $this> */
    public function staffCompetencies(): HasMany
    {
        return $this->hasMany(StaffCompetency::class);
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

    /** @return HasMany<Donation, $this> */
    public function completedDonations(): HasMany
    {
        return $this->donations()->where('status', DonationStatus::Completed);
    }

    /** @return HasMany<EligibilityRecord, $this> */
    public function eligibilityRecords(): HasMany
    {
        return $this->hasMany(EligibilityRecord::class);
    }

    /** @return HasMany<UserNotification, $this> */
    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    /** @return HasMany<FcmToken, $this> */
    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class);
    }

    /** @return HasMany<NotificationDelivery, $this> */
    public function notificationDeliveries(): HasMany
    {
        return $this->hasMany(NotificationDelivery::class);
    }

    /** @return HasMany<DonorBadge, $this> */
    public function donorBadges(): HasMany
    {
        return $this->hasMany(DonorBadge::class);
    }

    /** @return BelongsToMany<Badge, $this> */
    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'donor_badges')
            ->withPivot('awarded_at')
            ->withTimestamps();
    }

    /** @return HasMany<DonorReward, $this> */
    public function donorRewards(): HasMany
    {
        return $this->hasMany(DonorReward::class);
    }

    /** @return BelongsToMany<Reward, $this> */
    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(Reward::class, 'donor_rewards')
            ->withPivot(['status', 'awarded_at', 'redeemed_at'])
            ->withTimestamps();
    }

    /** @return HasMany<Leaderboard, $this> */
    public function leaderboardEntries(): HasMany
    {
        return $this->hasMany(Leaderboard::class);
    }

    /** @return HasMany<Deferral, $this> */
    public function deferrals(): HasMany
    {
        return $this->hasMany(Deferral::class);
    }

    /** @return HasMany<DonorDuplicateCase, $this> */
    public function duplicateCasesAsPrimary(): HasMany
    {
        return $this->hasMany(DonorDuplicateCase::class, 'primary_donor_id');
    }

    /** @return HasMany<DonorDuplicateCase, $this> */
    public function duplicateCasesAsCandidate(): HasMany
    {
        return $this->hasMany(DonorDuplicateCase::class, 'candidate_donor_id');
    }

    /** @return HasMany<DonorIdentityAlias, $this> */
    public function mergedIdentityAliases(): HasMany
    {
        return $this->hasMany(DonorIdentityAlias::class, 'canonical_donor_id');
    }

    /** @return HasOne<DonorIdentityAlias, $this> */
    public function sourceIdentityAlias(): HasOne
    {
        return $this->hasOne(DonorIdentityAlias::class, 'source_donor_id');
    }

    /** @return HasMany<DonorIdentityCheck, $this> */
    public function identityChecks(): HasMany
    {
        return $this->hasMany(DonorIdentityCheck::class, 'donor_id');
    }

    /** @return HasMany<CollectionEpisode, $this> */
    public function collectionEpisodes(): HasMany
    {
        return $this->hasMany(CollectionEpisode::class, 'donor_id');
    }

    /** @return HasMany<DonorReaction, $this> */
    public function donorReactions(): HasMany
    {
        return $this->hasMany(DonorReaction::class, 'donor_id');
    }

    /** @return HasMany<OfflineCollectionDevice, $this> */
    public function assignedOfflineCollectionDevices(): HasMany
    {
        return $this->hasMany(OfflineCollectionDevice::class, 'assigned_to');
    }

    /** @return HasMany<BloodUnit, $this> */
    public function donatedBloodUnits(): HasMany
    {
        return $this->hasMany(BloodUnit::class, 'donor_id');
    }

    /** @return HasMany<AuditLog, $this> */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
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
