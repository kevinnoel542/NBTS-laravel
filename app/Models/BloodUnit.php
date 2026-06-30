<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_number',
        'donation_id',
        'donor_id',
        'blood_center_id',
        'blood_group',
        'collection_date',
        'expiry_date',
        'status',
        'current_location',
        'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function donation()
    {
        return $this->belongsTo(Donation::class);
    }

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id');
    }

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function getDaysToExpiryAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_to_expiry;

        if ($days === null) {
            return 'unknown';
        }

        if ($days < 0) {
            return 'expired';
        }

        if ($days <= 7) {
            return 'expiring_soon';
        }

        return 'valid';
    }
}
