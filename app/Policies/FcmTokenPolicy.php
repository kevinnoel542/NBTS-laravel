<?php

namespace App\Policies;

use App\Models\FcmToken;
use App\Models\User;

class FcmTokenPolicy
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
    public function view(User $user, FcmToken $fcmToken): bool
    {
        return $user->is_active && $user->id === $fcmToken->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FcmToken $fcmToken): bool
    {
        return $user->is_active && $user->id === $fcmToken->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FcmToken $fcmToken): bool
    {
        return $user->is_active && $user->id === $fcmToken->user_id;
    }
}
