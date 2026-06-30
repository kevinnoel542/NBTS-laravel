<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LowStockAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_center_id',
        'blood_group',
        'available_units',
        'minimum_threshold',
        'status',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function bloodCenter()
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function campaign()
    {
        return $this->hasOne(Campaign::class);
    }

    public function getStockGapAttribute(): int
    {
        return max(0, $this->minimum_threshold - $this->available_units);
    }

    public function getSeverityAttribute(): string
    {
        if ($this->available_units <= 0) {
            return 'critical';
        }

        if ($this->stock_gap >= 3) {
            return 'high';
        }

        return 'low';
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, ['open', 'notified', 'campaign_created'], true);
    }
}
