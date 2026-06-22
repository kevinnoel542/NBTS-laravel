<?php

namespace App\Services;

use App\Repositories\BloodCenterRepository;
use Illuminate\Support\Collection;

class BloodCenterService
{
    protected $bloodCenterRepository;

    public function __construct(BloodCenterRepository $bloodCenterRepository)
    {
        $this->bloodCenterRepository = $bloodCenterRepository;
    }

    public function getAllCenters(): Collection
    {
        return $this->bloodCenterRepository->all();
    }

    public function getActiveCenters(): Collection
    {
        return $this->bloodCenterRepository->getActive();
    }

    public function getCenterById(int $id)
    {
        return $this->bloodCenterRepository->find($id);
    }
}
