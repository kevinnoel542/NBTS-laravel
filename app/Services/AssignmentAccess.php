<?php

namespace App\Services;

use App\Models\StaffAssignment;
use App\Models\User;
use App\PermissionName;
use BackedEnum;
use Spatie\Permission\Models\Permission;

final readonly class AssignmentAccess
{
    public function __construct(private ActiveAssignmentContext $assignmentContext) {}

    public function allows(
        User $user,
        mixed $permission,
        ?StaffAssignment $assignment = null,
    ): bool {
        if (! $user->is_active) {
            return false;
        }

        $permissionValue = match (true) {
            $permission instanceof BackedEnum => (string) $permission->value,
            $permission instanceof Permission => $permission->name,
            default => (string) $permission,
        };

        $assignment ??= $this->assignmentContext->selectedAssignment($user);

        if (! $assignment instanceof StaffAssignment) {
            return $user->hasCompatibilityPermissionTo($permissionValue);
        }

        if ($assignment->user_id !== $user->id || ! $assignment->isEffective()) {
            return false;
        }

        return $assignment->role->hasPermissionTo($permissionValue);
    }

    /** @param list<string|PermissionName> $permissions */
    public function allowsAny(User $user, array $permissions, ?StaffAssignment $assignment = null): bool
    {
        foreach ($permissions as $permission) {
            if ($this->allows($user, $permission, $assignment)) {
                return true;
            }
        }

        return false;
    }
}
