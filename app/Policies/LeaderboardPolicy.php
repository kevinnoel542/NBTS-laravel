<?php

namespace App\Policies;

use App\Models\Leaderboard;
use App\Models\User;
use App\PermissionName;

class LeaderboardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Leaderboard $leaderboard): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Leaderboard $leaderboard): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Leaderboard $leaderboard): bool
    {
        return $user->hasNationalScope()
            && $user->can(PermissionName::ManageLoyalty->value);
    }
}
