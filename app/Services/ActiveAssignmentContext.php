<?php

namespace App\Services;

use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use Illuminate\Database\Eloquent\Collection;

final class ActiveAssignmentContext
{
    private const SESSION_KEY = 'operations.assignment';

    /** @return Collection<int, StaffAssignment> */
    public function availableAssignments(User $user): Collection
    {
        if (! $user->is_active) {
            return new Collection;
        }

        return StaffAssignment::query()
            ->effective()
            ->whereBelongsTo($user)
            ->with([
                'role:id,name,guard_name',
                'organizationUnit:id,code,name,short_name,type,status',
                'organizationUnit.bloodCenter:id,organization_unit_id,name,is_active',
                'department:id,organization_unit_id,code,name,is_active',
                'workLocation:id,organization_unit_id,department_id,code,name,is_active',
            ])
            ->orderBy('organization_unit_id')
            ->orderBy('role_id')
            ->get();
    }

    public function initialSelection(User $user): string
    {
        $assignments = $this->availableAssignments($user);
        $stored = session(self::SESSION_KEY);

        if (is_numeric($stored) && $assignments->contains('id', (int) $stored)) {
            return (string) $stored;
        }

        $assignment = $assignments->first();

        if (! $assignment instanceof StaffAssignment) {
            session()->forget(self::SESSION_KEY);

            return 'legacy';
        }

        session([self::SESSION_KEY => $assignment->id]);

        return (string) $assignment->id;
    }

    public function setSelection(User $user, string $selection): string
    {
        $assignments = $this->availableAssignments($user);

        if (ctype_digit($selection) && $assignments->contains('id', (int) $selection)) {
            session([self::SESSION_KEY => (int) $selection]);

            return $selection;
        }

        return $this->initialSelection($user);
    }

    public function selectedAssignment(User $user, ?string $selection = null): ?StaffAssignment
    {
        $selection ??= $this->initialSelection($user);

        if (! ctype_digit($selection)) {
            return null;
        }

        return $this->availableAssignments($user)->firstWhere('id', (int) $selection);
    }

    public function role(User $user, ?string $selection = null): ?RoleName
    {
        $assignment = $this->selectedAssignment($user, $selection);

        return $assignment instanceof StaffAssignment
            ? RoleName::tryFrom($assignment->role->name)
            : null;
    }

    public function dashboardConfiguration(User $user, ?string $selection = null): string
    {
        $role = $this->role($user, $selection);

        if ($role instanceof RoleName) {
            return $role->dashboardConfiguration();
        }

        $compatibilityRole = RoleName::cases();

        foreach ($compatibilityRole as $roleName) {
            if ($user->hasRole($roleName->value)) {
                return $roleName->dashboardConfiguration();
            }
        }

        return 'reception';
    }

    public function label(User $user, ?string $selection = null): string
    {
        $assignment = $this->selectedAssignment($user, $selection);

        if (! $assignment instanceof StaffAssignment) {
            return __('console.context.compatibility');
        }

        $role = RoleName::tryFrom($assignment->role->name);
        $roleLabel = $role instanceof RoleName
            ? __('console.roles.'.$role->value)
            : str($assignment->role->name)->replace('_', ' ')->title()->toString();

        $organizationLabel = $assignment->organizationUnit->short_name
            ?: $assignment->organizationUnit->name;

        return $roleLabel.' · '.$organizationLabel;
    }

    public function setSelectionForCenter(User $user, int $bloodCenterId): ?StaffAssignment
    {
        $assignment = $this->availableAssignments($user)
            ->first(fn (StaffAssignment $candidate): bool => $candidate->organizationUnit->bloodCenter?->id === $bloodCenterId);

        if ($assignment instanceof StaffAssignment) {
            $this->setSelection($user, (string) $assignment->id);
        }

        return $assignment;
    }
}
