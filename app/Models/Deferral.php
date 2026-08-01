<?php

namespace App\Models;

use App\DeferralType;
use Carbon\CarbonInterface;
use Database\Factories\DeferralFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
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
])]
class Deferral extends Model
{
    /** @use HasFactory<DeferralFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ends_at' => 'date',
            'is_active' => 'boolean',
            'lifted_at' => 'datetime',
            'starts_at' => 'date',
            'type' => DeferralType::class,
        ];
    }

    /**
     * @param  Builder<Deferral>  $query
     * @return Builder<Deferral>
     */
    public function scopeEffectiveOn(Builder $query, ?CarbonInterface $date = null): Builder
    {
        $effectiveDate = ($date ?? now())->toDateString();

        return $query
            ->where('is_active', true)
            ->whereDate('starts_at', '<=', $effectiveDate)
            ->where(function (Builder $dateQuery) use ($effectiveDate): void {
                $dateQuery
                    ->whereNull('ends_at')
                    ->orWhereDate('ends_at', '>=', $effectiveDate);
            });
    }

    /** @return BelongsTo<User, $this> */
    public function donor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function lifter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }
}
