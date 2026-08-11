<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserNotification;
use App\PermissionName;

class UserNotificationPolicy
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
    public function view(User $user, UserNotification $userNotification): bool
    {
        return $user->is_active && (
            $user->id === $userNotification->user_id
            || $user->can(PermissionName::ManageNotifications->value)
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionName::ManageNotifications->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, UserNotification $userNotification): bool
    {
        return $user->is_active && $user->id === $userNotification->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, UserNotification $userNotification): bool
    {
        return $user->is_active && $user->id === $userNotification->user_id;
    }
}
