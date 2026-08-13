<?php

namespace App\Models;

use Database\Factories\ComponentProcessingEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'donation_id',
    'blood_unit_id',
    'operator_id',
    'event_type',
    'method',
    'device_identifier',
    'started_at',
    'ended_at',
    'yield_summary',
    'modifications',
    'qc_samples',
    'deviations',
    'final_label_verified',
    'notes',
])]
class ComponentProcessingEvent extends Model
{
    /** @use HasFactory<ComponentProcessingEventFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'deviations' => 'array',
            'ended_at' => 'immutable_datetime',
            'final_label_verified' => 'boolean',
            'modifications' => 'array',
            'qc_samples' => 'array',
            'started_at' => 'immutable_datetime',
            'yield_summary' => 'array',
        ];
    }

    /** @return BelongsTo<Donation, $this> */
    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    /** @return BelongsTo<BloodUnit, $this> */
    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /** @return HasMany<BloodComponent, $this> */
    public function components(): HasMany
    {
        return $this->hasMany(BloodComponent::class);
    }
}
