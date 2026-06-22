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
        'opening_hours',
        'services',
        'capacity_label',
        'estimated_wait_minutes',
        'center_type',
        'image_path',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
            'is_active' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

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
