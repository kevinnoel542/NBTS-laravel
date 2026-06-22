<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class BloodCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'phone',
        'email',
        'latitude',
        'longitude',
        'is_active',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    public function staffAssignments()
    {
        return $this->hasMany(CenterStaff::class);
    }

    public function bloodUnits()
    {
        return $this->hasMany(BloodUnit::class);
    }

    public function inventory()
    {
        return $this->hasMany(BloodInventory::class);
    }

    public function lowStockAlerts()
    {
        return $this->hasMany(LowStockAlert::class);
    }
}
