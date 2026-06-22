<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'donor_id',
        'blood_group_status',
        'blood_group_verified',
        'blood_group_verified_at',
        'blood_group_verified_by',
        'next_eligible_donation_date',
        'eligibility_status',
        'last_eligibility_checked_at',
        'eligibility_notes',
        'total_donations',
    ];

    protected function casts(): array
    {
        return [
            'blood_group_verified' => 'boolean',
            'blood_group_verified_at' => 'datetime',
            'next_eligible_donation_date' => 'date',
            'last_eligibility_checked_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'blood_group_verified_by');
    }
}
