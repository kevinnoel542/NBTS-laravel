<?php

namespace App\Actions\Assignments;

use App\Models\StaffAssignment;
use App\Models\User;
use App\PermissionName;
use App\RoleName;
use App\StaffAssignmentStatus;
use App\Support\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class SetStaffAssignmentStatus
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(
        User $actor,
        StaffAssignment $assignment,
        StaffAssignmentStatus $status,
        string $reason,
    ): StaffAssignment {
        Gate::forUser($actor)->authorize(PermissionName::ManageAssignments->value);

        if ($status === StaffAssignmentStatus::Expired) {
            throw ValidationException::withMessages([
                'status' => [__('system.staff_assignment_expiry_automatic')],
            ]);
        }

        if (mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages([
                'reason' => [__('system.staff_assignment_reason_required')],
            ]);
        }

        if ($actor->id === $assignment->user_id
            && RoleName::tryFrom($assignment->role->name)?->isClinical() === true) {
            throw new AuthorizationException(__('system.staff_assignment_self_clinical_denied'));
        }

        return DB::transaction(function () use ($actor, $assignment, $status, $reason): StaffAssignment {
            $lockedAssignment = StaffAssignment::query()->lockForUpdate()->findOrFail($assignment->id);

            $lockedAssignment->forceFill([
                'status' => $status,
                'reason' => trim($reason),
                'revoked_by' => $status === StaffAssignmentStatus::Revoked ? $actor->id : null,
                'revoked_at' => $status === StaffAssignmentStatus::Revoked ? now() : null,
            ])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: 'staff_assignment.status_changed',
                subject: $lockedAssignment,
                bloodCenter: $lockedAssignment->organizationUnit->bloodCenter,
                metadata: [
                    'status' => $status->value,
                    'staff_user_id' => $lockedAssignment->user_id,
                ],
            );

            return $lockedAssignment->load(['user', 'role', 'organizationUnit', 'department', 'workLocation', 'approver', 'revoker']);
        }, attempts: 3);
    }
}
