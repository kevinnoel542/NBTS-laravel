<?php

namespace App\Policies;

use App\Models\User;
use App\PermissionName;
use App\RoleName;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewUsers->value)
            || $user->can(PermissionName::ViewDonors->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $subject): bool
    {
        if ($user->id === $subject->id) {
            return $user->is_active;
        }

        if ($subject->hasRole(RoleName::Donor->value)) {
            return $user->can(PermissionName::ViewDonors->value)
                && $user->hasDonorAccess($subject);
        }

        return $user->hasNationalScope()
            && $user->can(PermissionName::ViewUsers->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $subject): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $subject): bool
    {
        return $user->id !== $subject->id
            && $user->hasNationalScope()
            && $user->can(PermissionName::ManageUsers->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function manageRoles(User $user, User $subject): bool
    {
        return $user->id !== $subject->id
            && $user->hasNationalScope()
            && $user->can(PermissionName::ManageRoles->value);
    }
}
