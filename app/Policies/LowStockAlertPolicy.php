<?php

namespace App\Policies;

use App\Models\LowStockAlert;
use App\Models\User;
use App\PermissionName;

class LowStockAlertPolicy
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
    public function view(User $user, LowStockAlert $lowStockAlert): bool
    {
        return $user->can(PermissionName::ViewInventory->value)
            && $user->hasCenterAccess($lowStockAlert->blood_center_id);
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
    public function update(User $user, LowStockAlert $lowStockAlert): bool
    {
        return $user->can(PermissionName::ManageInventory->value)
            && $user->hasCenterAccess($lowStockAlert->blood_center_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LowStockAlert $lowStockAlert): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageInventory->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function resolve(User $user, LowStockAlert $lowStockAlert): bool
    {
        return $this->update($user, $lowStockAlert);
    }
}
