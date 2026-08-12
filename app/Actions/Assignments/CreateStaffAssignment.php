<?php

namespace App\Actions\Assignments;

use App\Models\Department;
use App\Models\OrganizationUnit;
use App\Models\StaffAssignment;
use App\Models\User;
use App\Models\WorkLocation;
use App\OrganizationUnitStatus;
use App\PermissionName;
use App\RoleName;
use App\StaffAssignmentStatus;
use App\Support\AuditLogger;
use Carbon\CarbonInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final readonly class CreateStaffAssignment
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(
        User $actor,
        User $staffMember,
        RoleName $roleName,
        OrganizationUnit $organizationUnit,
        string $reason,
        ?Department $department = null,
        ?WorkLocation $workLocation = null,
        ?CarbonInterface $startsAt = null,
        ?CarbonInterface $endsAt = null,
        ?string $shift = null,
    ): StaffAssignment {
        Gate::forUser($actor)->authorize(PermissionName::ManageAssignments->value);

        $this->validateAssignment(
            actor: $actor,
            staffMember: $staffMember,
            roleName: $roleName,
            organizationUnit: $organizationUnit,
            reason: $reason,
            department: $department,
            workLocation: $workLocation,
            startsAt: $startsAt,
            endsAt: $endsAt,
        );

        return DB::transaction(function () use ($actor, $staffMember, $roleName, $organizationUnit, $reason, $department, $workLocation, $startsAt, $endsAt, $shift): StaffAssignment {
            $lockedStaffMember = User::query()->lockForUpdate()->findOrFail($staffMember->id);
            $lockedUnit = OrganizationUnit::query()->lockForUpdate()->findOrFail($organizationUnit->id);
            $role = Role::findByName($roleName->value, 'web');

            $duplicateQuery = StaffAssignment::query()
                ->whereBelongsTo($lockedStaffMember)
                ->where('role_id', $role->getKey())
                ->whereBelongsTo($lockedUnit)
                ->effective();

            $department instanceof Department
                ? $duplicateQuery->whereBelongsTo($department)
                : $duplicateQuery->whereNull('department_id');

            if ($duplicateQuery->exists()) {
                throw ValidationException::withMessages([
                    'role' => [__('system.staff_assignment_duplicate')],
                ]);
            }

            $assignment = StaffAssignment::query()->create([
                'user_id' => $lockedStaffMember->id,
                'role_id' => $role->getKey(),
                'organization_unit_id' => $lockedUnit->id,
                'department_id' => $department?->id,
                'work_location_id' => $workLocation?->id,
                'shift' => filled($shift) ? trim((string) $shift) : null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => StaffAssignmentStatus::Active,
                'approved_by' => $actor->id,
                'reason' => trim($reason),
            ]);

            $lockedStaffMember->forceFill(['role' => $roleName->legacyValue()])->save();

            $this->auditLogger->record(
                actor: $actor,
                action: 'staff_assignment.created',
                subject: $assignment,
                bloodCenter: $lockedUnit->bloodCenter,
                metadata: [
                    'department_id' => $department?->id,
                    'organization_unit_id' => $lockedUnit->id,
                    'role' => $roleName->value,
                    'staff_user_id' => $lockedStaffMember->id,
                    'work_location_id' => $workLocation?->id,
                ],
            );

            return $assignment->load(['user', 'role', 'organizationUnit', 'department', 'workLocation', 'approver']);
        }, attempts: 3);
    }

    private function validateAssignment(
        User $actor,
        User $staffMember,
        RoleName $roleName,
        OrganizationUnit $organizationUnit,
        string $reason,
        ?Department $department,
        ?WorkLocation $workLocation,
        ?CarbonInterface $startsAt,
        ?CarbonInterface $endsAt,
    ): void {
        if ($roleName === RoleName::Donor || ! $staffMember->is_active) {
            throw ValidationException::withMessages([
                'user_id' => [__('system.staff_assignment_account_invalid')],
            ]);
        }

        if ($actor->is($staffMember) && $roleName->isClinical()) {
            throw new AuthorizationException(__('system.staff_assignment_self_clinical_denied'));
        }

        if ($organizationUnit->status !== OrganizationUnitStatus::Active
            || ! OrganizationUnit::query()->active()->whereKey($organizationUnit)->exists()
            || ! in_array($organizationUnit->type, $roleName->organizationUnitTypes(), true)) {
            throw ValidationException::withMessages([
                'organization_unit_id' => [__('system.staff_assignment_scope_invalid')],
            ]);
        }

        if ($department instanceof Department
            && ($department->organization_unit_id !== $organizationUnit->id || ! $department->is_active)) {
            throw ValidationException::withMessages([
                'department_id' => [__('system.staff_assignment_department_invalid')],
            ]);
        }

        if ($workLocation instanceof WorkLocation
            && ($workLocation->organization_unit_id !== $organizationUnit->id
                || ! $workLocation->is_active
                || ($workLocation->department_id !== null && $workLocation->department_id !== $department?->id))) {
            throw ValidationException::withMessages([
                'work_location_id' => [__('system.staff_assignment_location_invalid')],
            ]);
        }

        if (mb_strlen(trim($reason)) < 10) {
            throw ValidationException::withMessages([
                'reason' => [__('system.staff_assignment_reason_required')],
            ]);
        }

        if ($startsAt !== null && $endsAt !== null && $endsAt->lessThanOrEqualTo($startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => [__('system.staff_assignment_dates_invalid')],
            ]);
        }
    }
}
