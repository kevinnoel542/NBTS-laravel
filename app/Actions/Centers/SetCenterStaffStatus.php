<?php

namespace App\Actions\Centers;

use App\Models\CenterStaff;
use App\Models\User;
use App\RoleName;
use App\Support\AuditLogger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final readonly class SetCenterStaffStatus
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function handle(User $actor, CenterStaff $assignment, bool $isActive): CenterStaff
    {
        Gate::forUser($actor)->authorize('update', $assignment);

        if ($assignment->position === RoleName::CenterManager->value && ! $actor->hasNationalScope()) {
            throw new AuthorizationException;
        }

        return DB::transaction(function () use ($actor, $assignment, $isActive): CenterStaff {
            $lockedAssignment = CenterStaff::query()->lockForUpdate()->findOrFail($assignment->id);
            $staffMember = User::query()->lockForUpdate()->findOrFail($lockedAssignment->user_id);

            $lockedAssignment->forceFill(['is_active' => $isActive])->save();
            $this->synchronizeStaffRole($staffMember);

            $this->auditLogger->record(
                actor: $actor,
                action: $isActive ? 'center_staff.activated' : 'center_staff.deactivated',
                subject: $lockedAssignment,
                bloodCenter: $lockedAssignment->bloodCenter,
                metadata: [
                    'position' => $lockedAssignment->position,
                    'staff_user_id' => $staffMember->id,
                ],
            );

            return $lockedAssignment->load(['user.roles', 'bloodCenter']);
        }, attempts: 3);
    }

    private function synchronizeStaffRole(User $staffMember): void
    {
        $activeAssignments = $staffMember->centerStaffAssignments()
            ->where('is_active', true)
            ->get(['position']);

        if ($activeAssignments->contains('position', RoleName::CenterManager->value)) {
            $staffMember->syncRoles([RoleName::CenterManager->value]);
        } elseif ($activeAssignments->isNotEmpty()) {
            $staffMember->syncRoles([RoleName::CenterStaff->value]);
        } else {
            $staffMember->syncRoles([]);
        }

        $staffMember->forceFill(['role' => 'staff'])->save();
    }
}
