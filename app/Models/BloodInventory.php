<?php

namespace App\Models;

use App\BloodGroup;
use Database\Factories\BloodInventoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_center_id',
    'blood_group',
    'available_units',
    'reserved_units',
    'minimum_threshold',
])]
class BloodInventory extends Model
{
    /** @use HasFactory<BloodInventoryFactory> */
    use HasFactory;

    protected $table = 'blood_inventory';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
        ];
    }

    /**
     * @param  Builder<BloodInventory>  $query
     * @return Builder<BloodInventory>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasNationalScope()) {
            return $query;
        }

        return $query->whereIn(
            'blood_center_id',
            $user->centerStaffAssignments()->where('is_active', true)->select('blood_center_id'),
        );
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function totalUnits(): int
    {
        return $this->available_units + $this->reserved_units;
    }

    public function stockGap(): int
    {
        return max(0, $this->minimum_threshold - $this->available_units);
    }

    public function stockStatus(): string
    {
        if ($this->available_units === 0) {
            return 'critical';
        }

        return $this->available_units < $this->minimum_threshold ? 'low' : 'healthy';
    }
}
