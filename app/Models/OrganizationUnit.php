<?php

namespace App\Models;

use App\OrganizationUnitStatus;
use App\OrganizationUnitType;
use Database\Factories\OrganizationUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int|null $parent_id
 * @property string $code
 * @property string $name
 * @property string|null $short_name
 * @property OrganizationUnitType $type
 * @property OrganizationUnitStatus $status
 */
#[Fillable([
    'parent_id',
    'code',
    'name',
    'short_name',
    'type',
    'status',
    'effective_from',
    'effective_until',
])]
class OrganizationUnit extends Model
{
    /** @use HasFactory<OrganizationUnitFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_until' => 'date',
            'status' => OrganizationUnitStatus::class,
            'type' => OrganizationUnitType::class,
        ];
    }

    /**
     * @param  Builder<OrganizationUnit>  $query
     * @return Builder<OrganizationUnit>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('status', OrganizationUnitStatus::Active)
            ->where(fn (Builder $dateQuery): Builder => $dateQuery
                ->whereNull('effective_from')
                ->orWhereDate('effective_from', '<=', today()))
            ->where(fn (Builder $dateQuery): Builder => $dateQuery
                ->whereNull('effective_until')
                ->orWhereDate('effective_until', '>=', today()));
    }

    /** @return BelongsTo<OrganizationUnit, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<OrganizationUnit, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return HasOne<BloodCenter, $this> */
    public function bloodCenter(): HasOne
    {
        return $this->hasOne(BloodCenter::class);
    }

    /** @return HasMany<Department, $this> */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /** @return HasMany<WorkLocation, $this> */
    public function workLocations(): HasMany
    {
        return $this->hasMany(WorkLocation::class);
    }

    /** @return HasMany<StaffAssignment, $this> */
    public function staffAssignments(): HasMany
    {
        return $this->hasMany(StaffAssignment::class);
    }
}
