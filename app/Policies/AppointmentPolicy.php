<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use App\PermissionName;
use App\RoleName;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionName::ViewAppointments->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole(RoleName::Donor->value)) {
            return $user->id === $appointment->user_id;
        }

        return $user->can(PermissionName::ViewAppointments->value)
            && $user->hasCenterAccess($appointment->blood_center_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->is_active && $user->hasRole(RoleName::Donor->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Appointment $appointment): bool
    {
        return $this->transition($user, $appointment);
    }

    public function transition(User $user, Appointment $appointment): bool
    {
        return $user->can(PermissionName::ManageAppointments->value)
            && $user->hasCenterAccess($appointment->blood_center_id);
    }
}
