<?php

namespace App\Policies;

use App\Models\BloodCenter;
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

    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageInventory->value);
    }

    public function createAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ManageInventory->value)
            && $user->hasCenterAccess($bloodCenter);
    }

    /**
     * Determine whether the user can create models.
     */
    public function transition(User $user, BloodUnit $bloodUnit): bool
    {
        return $user->can(PermissionName::ManageInventory->value)
            && $user->hasCenterAccess($bloodUnit->blood_center_id);
    }

    public function authorizeRelease(User $user, BloodUnit $bloodUnit): bool
    {
        return $user->can(PermissionName::ApproveLaboratoryRelease->value)
            && $user->hasCenterAccess($bloodUnit->blood_center_id);
    }

    public function update(User $user, BloodUnit $bloodUnit): bool
    {
        return $this->transition($user, $bloodUnit);
    }

    public function delete(User $user, BloodUnit $bloodUnit): bool
    {
        return $user->hasNationalScope()
            && $this->transition($user, $bloodUnit);
    }
}
