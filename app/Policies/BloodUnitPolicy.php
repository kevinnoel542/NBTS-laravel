<?php

namespace App\Policies;

use App\Models\BloodUnit;
use App\Models\User;
use App\PermissionName;

class BloodUnitPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewInventory->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BloodUnit $bloodUnit): bool
    {
        return $user->can(PermissionName::ViewInventory->value)
            && $user->hasCenterAccess($bloodUnit->blood_center_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function transition(User $user, BloodUnit $bloodUnit): bool
    {
        return $user->can(PermissionName::ManageInventory->value)
            && $user->hasCenterAccess($bloodUnit->blood_center_id);
    }
}
