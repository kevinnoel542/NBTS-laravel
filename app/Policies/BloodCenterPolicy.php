<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\User;
use App\PermissionName;

class BloodCenterPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, BloodCenter $bloodCenter): bool
    {
        return $bloodCenter->is_active || ($user?->hasNationalScope() ?? false) || ($user?->hasCenterAccess($bloodCenter) ?? false);
    }

    public function viewOperations(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ViewCenters->value)
            && $user->hasCenterAccess($bloodCenter);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageCenters->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageCenters->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageCenters->value);
    }

    public function manageStaff(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ManageCenterStaff->value)
            && $user->hasCenterAccess($bloodCenter);
    }
}
