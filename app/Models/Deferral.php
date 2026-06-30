<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deferral extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'type',
        'reason',
        'notes',
        'starts_at',
        'ends_at',
        'is_active',
        'lifted_at',
        'lifted_by',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'lifted_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lifter()
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }

    public function getTypeLabelAttribute(): string
    {
        return str($this->type)->title()->toString();
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) {
            return 'Lifted';
        }

        if ($this->type === 'temporary' && $this->ends_at && $this->ends_at->isPast()) {
            return 'Expired';
        }

        return 'Active';
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->type !== 'temporary' || !$this->ends_at || !$this->is_active) {
            return null;
        }

        return max(0, now()->startOfDay()->diffInDays($this->ends_at, false));
    }
}
