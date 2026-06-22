<?php

namespace App\Repositories;

use App\Models\Appointment;
use Illuminate\Support\Collection;

class AppointmentRepository
{
    public function all(): Collection
    {
        return Appointment::with(['user', 'bloodCenter'])->get();
    }

    public function find(int $id): ?Appointment
    {
        return Appointment::with(['user', 'bloodCenter'])->find($id);
    }

    public function create(array $data): Appointment
    {
        return Appointment::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $appointment = $this->find($id);
        if (!$appointment) return false;
        return $appointment->update($data);
    }

    public function getByUserId(int $userId): Collection
    {
        return Appointment::where('user_id', $userId)
            ->with('bloodCenter')
            ->latest('scheduled_at')
            ->get();
    }

    public function getUpcomingByUserId(int $userId): Collection
    {
        return Appointment::where('user_id', $userId)
            ->where('scheduled_at', '>=', now())
            ->whereIn('status', ['pending', 'confirmed'])
            ->with('bloodCenter')
            ->orderBy('scheduled_at')
            ->get();
    }
}
