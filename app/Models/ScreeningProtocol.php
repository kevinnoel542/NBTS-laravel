<?php

namespace App\Models;

use App\ScreeningProtocolStatus;
use Carbon\CarbonImmutable;
use Database\Factories\ScreeningProtocolFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property ScreeningProtocolStatus $status
 * @property list<array{key: string, label: string, required: bool, type: string}> $questionnaire
 * @property array{minimum_age?: int, maximum_age?: int, minimum_weight_kg?: int|float, disqualifying_answers?: array<string, bool|float|int|string|null>} $rules
 * @property CarbonImmutable|null $effective_from
 * @property CarbonImmutable|null $effective_until
 * @property bool $is_construction_only
 */
#[Fillable([
    'code',
    'version',
    'title',
    'status',
    'questionnaire',
    'rules',
    'effective_from',
    'effective_until',
    'is_construction_only',
    'approved_by',
    'approved_at',
])]
class ScreeningProtocol extends Model
{
    /** @use HasFactory<ScreeningProtocolFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'effective_from' => 'immutable_date',
            'effective_until' => 'immutable_date',
            'is_construction_only' => 'boolean',
            'questionnaire' => 'array',
            'rules' => 'array',
            'status' => ScreeningProtocolStatus::class,
        ];
    }

    /**
     * @param  Builder<ScreeningProtocol>  $query
     * @return Builder<ScreeningProtocol>
     */
    public function scopeEffective(Builder $query): Builder
    {
        return $query
            ->where('status', ScreeningProtocolStatus::Active)
            ->where(function (Builder $dateQuery): void {
                $dateQuery->whereNull('effective_from')->orWhereDate('effective_from', '<=', today());
            })
            ->where(function (Builder $dateQuery): void {
                $dateQuery->whereNull('effective_until')->orWhereDate('effective_until', '>=', today());
            });
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return HasMany<EligibilityRecord, $this> */
    public function eligibilityRecords(): HasMany
    {
        return $this->hasMany(EligibilityRecord::class);
    }
}
