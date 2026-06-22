<?php

namespace App\Services;

use App\Repositories\AppointmentRepository;

class AppointmentService
{
    protected $appointmentRepository;

    public function __construct(AppointmentRepository $appointmentRepository)
    {
        $this->appointmentRepository = $appointmentRepository;
    }

    public function bookAppointment(array $data)
    {
        return $this->appointmentRepository->create(array_merge($data, [
            'status' => 'pending'
        ]));
    }

    public function getUserAppointments(int $userId)
    {
        return $this->appointmentRepository->getByUserId($userId);
    }

    public function getUserUpcomingAppointments(int $userId)
    {
        return $this->appointmentRepository->getUpcomingByUserId($userId);
    }

    public function getAppointmentDetails(int $id)
    {
        return $this->appointmentRepository->find($id);
    }
}
