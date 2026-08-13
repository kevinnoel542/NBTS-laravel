<?php

namespace App\Models;

use App\BloodUnitQuarantineStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $blood_unit_id
 * @property BloodUnitQuarantineStatus $status
 * @property array<int, string> $reasons
 * @property CarbonImmutable $held_at
 * @property CarbonImmutable|null $release_criteria_completed_at
 */
#[Fillable([
    'blood_unit_id',
    'status',
    'reasons',
    'held_at',
    'held_by',
    'release_criteria_completed_at',
    'released_by',
    'notes',
])]
class BloodUnitQuarantine extends Model
{
    /** @return array<string, mixed> */
    protected function casts(): array
    {
        return [
            'held_at' => 'immutable_datetime',
            'reasons' => 'array',
            'release_criteria_completed_at' => 'immutable_datetime',
            'status' => BloodUnitQuarantineStatus::class,
        ];
    }

    /** @return BelongsTo<BloodUnit, $this> */
    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function holder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'held_by');
    }

    /** @return BelongsTo<User, $this> */
    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function hasCompletedReleaseCriteria(): bool
    {
        return $this->status === BloodUnitQuarantineStatus::Released
            && $this->release_criteria_completed_at !== null;
    }
}
