<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin' || $user->id === $appointment->user_id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'donor';
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin' || ($user->id === $appointment->user_id && $appointment->status === 'pending');
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin';
    }
}
