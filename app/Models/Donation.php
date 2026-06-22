<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'blood_center_id',
        'recorded_by',
        'appointment_id',
        'donation_type',
        'blood_group',
        'blood_group_verified',
        'volume_ml',
        'donation_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'blood_group_verified' => 'boolean',
            'donation_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function bloodUnit()
    {
        return $this->hasOne(BloodUnit::class);
    }
}
