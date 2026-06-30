<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EligibilityRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'checked_by',
        'status',
        'age',
        'weight_kg',
        'answers',
        'next_eligible_donation_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'next_eligible_donation_date' => 'date',
            'weight_kg' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function checker()
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    public function getStatusLabelAttribute(): string
    {
        return str($this->status)->replace('_', ' ')->title()->toString();
    }
}
