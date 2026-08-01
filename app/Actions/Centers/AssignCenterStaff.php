<?php

namespace App\Actions\Centers;

use App\Models\BloodCenter;
use App\Models\CenterStaff;
use App\Models\User;
use App\RoleName;
use App\Support\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final readonly class AssignCenterStaff
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(
        User $actor,
        BloodCenter $bloodCenter,
        User $staffMember,
        RoleName $position,
    ): CenterStaff {
        Gate::forUser($actor)->authorize('assignAt', [CenterStaff::class, $bloodCenter]);

        if (! in_array($position, [RoleName::CenterManager, RoleName::CenterStaff], true)) {
            throw ValidationException::withMessages([
                'position' => [__('validation.in', ['attribute' => __('position')])],
            ]);
        }

        if ($position === RoleName::CenterManager && ! $actor->hasNationalScope()) {
            throw new AuthorizationException;
        }

        if (! $staffMember->is_active || $staffMember->hasNationalScope()) {
            throw ValidationException::withMessages([
                'user_id' => [__('system.staff_assignment_account_invalid')],
            ]);
        }

        return DB::transaction(function () use ($actor, $bloodCenter, $staffMember, $position): CenterStaff {
            $lockedStaffMember = User::query()->lockForUpdate()->findOrFail($staffMember->id);

            $assignment = CenterStaff::query()->updateOrCreate(
                [
                    'user_id' => $lockedStaffMember->id,
                    'blood_center_id' => $bloodCenter->id,
                ],
                [
                    'position' => $position->value,
                    'is_active' => true,
                ],
            );

            $this->synchronizeStaffRole($lockedStaffMember);

            $this->auditLogger->record(
                actor: $actor,
                action: 'center_staff.assigned',
                subject: $assignment,
                bloodCenter: $bloodCenter,
                metadata: [
                    'position' => $position->value,
                    'staff_user_id' => $lockedStaffMember->id,
                ],
            );

            return $assignment->load(['user.roles', 'bloodCenter']);
        }, attempts: 3);
    }

    private function synchronizeStaffRole(User $staffMember): void
    {
        $hasManagerAssignment = $staffMember->centerStaffAssignments()
            ->where('is_active', true)
            ->where('position', RoleName::CenterManager->value)
            ->exists();

        $staffMember->syncRoles([
            $hasManagerAssignment
                ? RoleName::CenterManager->value
                : RoleName::CenterStaff->value,
        ]);
        $staffMember->forceFill(['role' => 'staff'])->save();
    }
}
