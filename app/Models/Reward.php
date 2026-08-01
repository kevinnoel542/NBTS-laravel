<?php

namespace App\Models;

use Database\Factories\RewardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $donation_threshold
 * @property bool $is_active
 */
#[Fillable([
    'name',
    'slug',
    'description',
    'donation_threshold',
    'is_active',
])]
class Reward extends Model
{
    /** @use HasFactory<RewardFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<Reward>  $query
     * @return Builder<Reward>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @return HasMany<DonorReward, $this> */
    public function donorRewards(): HasMany
    {
        return $this->hasMany(DonorReward::class);
    }
}
