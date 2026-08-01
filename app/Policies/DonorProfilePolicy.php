<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\DonorProfile;
use App\Models\User;
use App\PermissionName;
use App\RoleName;

class DonorProfilePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && (
            $user->hasRole(RoleName::Donor->value)
            || $user->can(PermissionName::ViewDonors->value)
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DonorProfile $donorProfile): bool
    {
        return $user->id === $donorProfile->user_id
            || ($user->can(PermissionName::ViewDonors->value) && $user->hasDonorAccess($donorProfile->user_id));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && (
            $user->hasRole(RoleName::Donor->value)
            || $user->can(PermissionName::RegisterDonors->value)
            || $user->can(PermissionName::ManageDonors->value)
        );
    }

    public function registerAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $bloodCenter->is_active
            && $user->can(PermissionName::RegisterDonors->value)
            && $user->hasCenterAccess($bloodCenter);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DonorProfile $donorProfile): bool
    {
        return ($user->id === $donorProfile->user_id && $user->is_active)
            || ($user->can(PermissionName::ManageDonors->value) && $user->hasDonorAccess($donorProfile->user_id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DonorProfile $donorProfile): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageDonors->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function verifyBloodGroup(User $user, DonorProfile $donorProfile): bool
    {
        return $user->can(PermissionName::CheckEligibility->value)
            && $user->hasDonorAccess($donorProfile->user_id);
    }
}
