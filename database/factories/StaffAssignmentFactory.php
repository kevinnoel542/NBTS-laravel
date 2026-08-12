<?php

namespace Database\Factories;

use App\Models\OrganizationUnit;
use App\Models\StaffAssignment;
use App\Models\User;
use App\RoleName;
use App\StaffAssignmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<StaffAssignment>
 */
class StaffAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->staff(),
            'role_id' => fn (): int => $this->roleId(RoleName::ReceptionOfficer),
            'organization_unit_id' => OrganizationUnit::factory(),
            'department_id' => null,
            'work_location_id' => null,
            'shift' => null,
            'starts_at' => now()->subMonth(),
            'ends_at' => null,
            'status' => StaffAssignmentStatus::Active,
            'approved_by' => null,
            'reason' => 'Factory assignment for an isolated automated test.',
            'revoked_by' => null,
            'revoked_at' => null,
        ];
    }

    public function forRole(RoleName $role): static
    {
        return $this->state(fn (): array => [
            'role_id' => $this->roleId($role),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => ['status' => StaffAssignmentStatus::Suspended]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => StaffAssignmentStatus::Active,
            'ends_at' => now()->subMinute(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'status' => StaffAssignmentStatus::Revoked,
            'revoked_at' => now(),
        ]);
    }

    private function roleId(RoleName $roleName): int
    {
        Role::findOrCreate($roleName->value, 'web');
        $roleId = Role::query()
            ->where('name', $roleName->value)
            ->where('guard_name', 'web')
            ->value('id');

        if (! is_int($roleId)) {
            throw new LogicException('The required role does not exist: '.$roleName->value);
        }

        return $roleId;
    }
}
