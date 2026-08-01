<?php

namespace App\Models;

use App\EligibilityStatus;
use Database\Factories\EligibilityRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'checked_by',
    'status',
    'age',
    'weight_kg',
    'answers',
    'next_eligible_donation_date',
    'notes',
])]
class EligibilityRecord extends Model
{
    /** @use HasFactory<EligibilityRecordFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'next_eligible_donation_date' => 'date',
            'status' => EligibilityStatus::class,
            'weight_kg' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
