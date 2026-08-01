<?php

namespace App\Policies;

use App\Models\EligibilityRecord;
use App\Models\User;
use App\PermissionName;
use App\RoleName;

class EligibilityRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active && (
            $user->hasRole(RoleName::Donor->value)
            || $user->can(PermissionName::CheckEligibility->value)
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EligibilityRecord $eligibilityRecord): bool
    {
        if ($user->id === $eligibilityRecord->user_id) {
            return $user->is_active;
        }

        return $user->can(PermissionName::CheckEligibility->value)
            && ($user->id === $eligibilityRecord->checked_by || $user->hasDonorAccess($eligibilityRecord->user_id));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::CheckEligibility->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EligibilityRecord $eligibilityRecord): bool
    {
        return $user->can(PermissionName::CheckEligibility->value)
            && ($user->id === $eligibilityRecord->checked_by || $user->hasDonorAccess($eligibilityRecord->user_id));
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EligibilityRecord $eligibilityRecord): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::CheckEligibility->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function check(User $user, User $donor): bool
    {
        return $user->can(PermissionName::CheckEligibility->value)
            && $user->hasDonorAccess($donor);
    }
}
