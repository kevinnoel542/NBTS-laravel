<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use Illuminate\Validation\ValidationException;

final readonly class HospitalAccessService
{
    public function __construct(private ActiveAssignmentContext $assignmentContext) {}

    public function canAccess(User $user, Hospital $hospital): bool
    {
        if (! $user->is_active) {
            return false;
        }

        if ($user->hasNationalScope()) {
            return true;
        }

        $assignment = $this->assignmentContext->selectedAssignment($user);

        if ($assignment instanceof StaffAssignment) {
            return $hospital->organization_unit_id !== null
                && $assignment->organization_unit_id === $hospital->organization_unit_id;
        }

        return $hospital->organization_unit_id === null
            && $user->hasAnyRole(RoleName::hospitalValues());
    }

    public function ensure(User $user, Hospital $hospital): void
    {
        if (! $this->canAccess($user, $hospital)) {
            throw ValidationException::withMessages(['hospital' => ['This account cannot access the selected hospital scope.']]);
        }
    }
}
