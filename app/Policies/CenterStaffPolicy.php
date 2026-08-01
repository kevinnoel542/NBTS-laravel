<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use App\PermissionName;

class CenterStaffPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewCenters->value)
            || $user->can(PermissionName::ManageCenterStaff->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CenterStaff $centerStaff): bool
    {
        return $user->hasCenterAccess($centerStaff->blood_center_id)
            && ($user->can(PermissionName::ViewCenters->value) || $user->can(PermissionName::ManageCenterStaff->value));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageCenterStaff->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CenterStaff $centerStaff): bool
    {
        return $user->can(PermissionName::ManageCenterStaff->value)
            && $user->hasCenterAccess($centerStaff->blood_center_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CenterStaff $centerStaff): bool
    {
        return $user->can(PermissionName::ManageCenterStaff->value)
            && $user->hasCenterAccess($centerStaff->blood_center_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function assignAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ManageCenterStaff->value)
            && $user->hasCenterAccess($bloodCenter);
    }
}
