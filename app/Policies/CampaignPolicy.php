<?php

namespace App\Policies;

use App\Models\BloodCenter;
use App\Models\Campaign;
use App\Models\User;
use App\PermissionName;

class CampaignPolicy
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
    public function view(?User $user, Campaign $campaign): bool
    {
        return $campaign->isPubliclyVisible()
            || ($user?->can(PermissionName::ViewCampaigns->value) === true && $user->hasCenterAccess($campaign->blood_center_id));
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageCampaigns->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Campaign $campaign): bool
    {
        return $user->can(PermissionName::ManageCampaigns->value)
            && $user->hasCenterAccess($campaign->blood_center_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Campaign $campaign): bool
    {
        return $user->can(PermissionName::ManageCampaigns->value)
            && $user->hasCenterAccess($campaign->blood_center_id);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function createAt(User $user, BloodCenter $bloodCenter): bool
    {
        return $user->can(PermissionName::ManageCampaigns->value)
            && $user->hasCenterAccess($bloodCenter);
    }
}
