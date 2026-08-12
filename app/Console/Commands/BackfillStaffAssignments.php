<?php

namespace App\Console\Commands;

use App\Models\CenterStaff;
use App\Models\OrganizationUnit;
use App\Models\StaffAssignment;
use App\Models\User;
use App\OrganizationUnitType;
use App\RoleName;
use App\StaffAssignmentStatus;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use LogicException;
use Spatie\Permission\Models\Role;

#[Signature('nbts:backfill-staff-assignments {--dry-run : Report the assignments without writing them}')]
#[Description('Backfill scoped staff assignments from transitional roles and center_staff records')]
class BackfillStaffAssignments extends Command
{
    public function handle(): int
    {
        $nationalUnit = OrganizationUnit::query()
            ->where('type', OrganizationUnitType::National)
            ->first();

        if (! $nationalUnit instanceof OrganizationUnit) {
            $this->components->error('No national organization unit exists. Run OrganizationStructureSeeder first.');

            return self::FAILURE;
        }

        $candidates = $this->candidates($nationalUnit);
        $this->components->info(sprintf('%d scoped assignment candidate(s) found.', count($candidates)));

        if ((bool) $this->option('dry-run')) {
            return self::SUCCESS;
        }

        $created = DB::transaction(function () use ($candidates): int {
            $created = 0;

            foreach ($candidates as $candidate) {
                $assignment = StaffAssignment::query()->firstOrCreate(
                    [
                        'user_id' => $candidate['user_id'],
                        'role_id' => $candidate['role_id'],
                        'organization_unit_id' => $candidate['organization_unit_id'],
                        'department_id' => null,
                        'starts_at' => null,
                    ],
                    [
                        'work_location_id' => null,
                        'shift' => null,
                        'ends_at' => null,
                        'status' => $candidate['status'],
                        'approved_by' => null,
                        'reason' => 'Compatibility migration from the deployed role and center scope.',
                    ],
                );

                $created += $assignment->wasRecentlyCreated ? 1 : 0;
            }

            return $created;
        }, attempts: 3);

        $this->components->info(sprintf('%d assignment(s) created; existing records were preserved.', $created));

        return self::SUCCESS;
    }

    /** @return list<array{user_id: int, role_id: int, organization_unit_id: int, status: StaffAssignmentStatus}> */
    private function candidates(OrganizationUnit $nationalUnit): array
    {
        $candidates = [];

        foreach (RoleName::nationalValues() as $roleName) {
            $roleId = $this->roleId($roleName);

            User::query()
                ->active()
                ->whereHas('roles', fn ($query) => $query->where('name', $roleName))
                ->each(function (User $user) use (&$candidates, $nationalUnit, $roleId): void {
                    $candidates[] = [
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'organization_unit_id' => $nationalUnit->id,
                        'status' => StaffAssignmentStatus::Active,
                    ];
                });
        }

        CenterStaff::query()
            ->with(['user:id,is_active', 'bloodCenter:id,organization_unit_id'])
            ->each(function (CenterStaff $legacyAssignment) use (&$candidates): void {
                $organizationUnitId = $legacyAssignment->bloodCenter?->organization_unit_id;

                if ($organizationUnitId === null || ! $legacyAssignment->user instanceof User) {
                    return;
                }

                $roleName = RoleName::tryFrom($legacyAssignment->position);

                if (! $roleName instanceof RoleName || ! in_array($roleName->value, RoleName::centerValues(), true)) {
                    $roleName = RoleName::CenterStaff;
                }

                $candidates[] = [
                    'user_id' => $legacyAssignment->user_id,
                    'role_id' => $this->roleId($roleName->value),
                    'organization_unit_id' => $organizationUnitId,
                    'status' => $legacyAssignment->is_active && $legacyAssignment->user->is_active
                        ? StaffAssignmentStatus::Active
                        : StaffAssignmentStatus::Suspended,
                ];
            });

        return $candidates;
    }

    private function roleId(string $roleName): int
    {
        $roleId = Role::query()
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->value('id');

        if (! is_int($roleId)) {
            throw new LogicException('The required role does not exist: '.$roleName);
        }

        return $roleId;
    }
}
