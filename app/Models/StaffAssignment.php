<?php

namespace App\Models;

use App\OrganizationUnitStatus;
use App\StaffAssignmentStatus;
use Carbon\CarbonInterface;
use Database\Factories\StaffAssignmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role;

/**
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 * @property int $organization_unit_id
 * @property int|null $department_id
 * @property int|null $work_location_id
 * @property StaffAssignmentStatus $status
 */
#[Fillable([
    'user_id',
    'role_id',
    'organization_unit_id',
    'department_id',
    'work_location_id',
    'shift',
    'starts_at',
    'ends_at',
    'status',
    'approved_by',
    'reason',
    'revoked_by',
    'revoked_at',
])]
class StaffAssignment extends Model
{
    /** @use HasFactory<StaffAssignmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ends_at' => 'datetime',
            'revoked_at' => 'datetime',
            'starts_at' => 'datetime',
            'status' => StaffAssignmentStatus::class,
        ];
    }

    /**
     * @param  Builder<StaffAssignment>  $query
     * @return Builder<StaffAssignment>
     */
    public function scopeEffective(Builder $query, ?CarbonInterface $at = null): Builder
    {
        $at ??= now();

        return $query
            ->where('status', StaffAssignmentStatus::Active)
            ->where(fn (Builder $dateQuery): Builder => $dateQuery
                ->whereNull('starts_at')
                ->orWhere('starts_at', '<=', $at))
            ->where(fn (Builder $dateQuery): Builder => $dateQuery
                ->whereNull('ends_at')
                ->orWhere('ends_at', '>', $at))
            ->whereHas('organizationUnit', fn (Builder $unitQuery): Builder => $unitQuery
                ->where('status', OrganizationUnitStatus::Active)
                ->where(fn (Builder $dateQuery): Builder => $dateQuery
                    ->whereNull('effective_from')
                    ->orWhereDate('effective_from', '<=', $at))
                ->where(fn (Builder $dateQuery): Builder => $dateQuery
                    ->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', $at)))
            ->where(fn (Builder $scopeQuery): Builder => $scopeQuery
                ->whereNull('department_id')
                ->orWhereHas('department', fn (Builder $departmentQuery): Builder => $departmentQuery->where('is_active', true)))
            ->where(fn (Builder $scopeQuery): Builder => $scopeQuery
                ->whereNull('work_location_id')
                ->orWhereHas('workLocation', fn (Builder $locationQuery): Builder => $locationQuery->where('is_active', true)));
    }

    public function isEffective(?CarbonInterface $at = null): bool
    {
        return self::query()->whereKey($this)->effective($at)->exists();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @return BelongsTo<OrganizationUnit, $this> */
    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<WorkLocation, $this> */
    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }
}
