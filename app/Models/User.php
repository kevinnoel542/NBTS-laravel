<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
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
        'role',
        'is_active',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function donorProfile()
    {
        return $this->hasOne(DonorProfile::class);
    }

    public function centerStaffAssignments()
    {
        return $this->hasMany(CenterStaff::class);
    }

    public function eligibilityRecords()
    {
        return $this->hasMany(EligibilityRecord::class);
    }

    public function deferrals()
    {
        return $this->hasMany(Deferral::class);
    }

    public function donorBadges()
    {
        return $this->hasMany(DonorBadge::class);
    }

    public function donorRewards()
    {
        return $this->hasMany(DonorReward::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function fcmTokens()
    {
        return $this->hasMany(FCMToken::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return (bool) $this->is_active && $this->hasAnyRole([
            'super_admin',
            'nbts_admin',
            'center_manager',
            'center_staff',
        ]);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'last_donation' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
