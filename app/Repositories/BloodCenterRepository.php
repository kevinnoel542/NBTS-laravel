<?php

namespace App\Repositories;

use App\Models\BloodCenter;
use Illuminate\Support\Collection;

class BloodCenterRepository
{
    public function all(): Collection
    {
        return BloodCenter::all();
    }

    public function find(int $id): ?BloodCenter
    {
        return BloodCenter::find($id);
    }

    public function create(array $data): BloodCenter
    {
        return BloodCenter::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $center = $this->find($id);
        if (!$center) return false;
        return $center->update($data);
    }

    public function delete(int $id): bool
    {
        $center = $this->find($id);
        if (!$center) return false;
        return $center->delete();
    }

    public function getActive(): Collection
    {
        return BloodCenter::where('is_active', true)->get();
    }
}
