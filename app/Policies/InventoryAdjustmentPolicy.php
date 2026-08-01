<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\InventoryAdjustment;
use App\Models\User;
use App\PermissionName;

class InventoryAdjustmentPolicy
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
    public function view(User $user, InventoryAdjustment $inventoryAdjustment): bool
    {
        return $user->can(PermissionName::ViewInventory->value)
            && $user->hasCenterAccess($inventoryAdjustment->blood_center_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageInventory->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InventoryAdjustment $inventoryAdjustment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InventoryAdjustment $inventoryAdjustment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function recordAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ManageInventory->value)
            && $user->hasCenterAccess($bloodCenter);
    }
}
