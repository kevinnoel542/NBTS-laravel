<?php

namespace App\Console\Commands;

use App\Actions\Inventory\ReconcileInventory;
use App\Models\BloodCenter;
use App\Models\BloodInventory;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

#[Signature('inventory:reconcile
    {--actor= : ID of the authorized staff member running the reconciliation}
    {--center= : Limit the check to one blood center ID}
    {--repair : Repair aggregate balances to match traceable blood-unit states}
    {--reason= : Required audit reason when --repair is used}')]
#[Description('Inspect or repair blood inventory balances against traceable blood-unit states')]
class ReconcileInventoryCommand extends Command
{
    public function handle(ReconcileInventory $reconcileInventory): int
    {
        $actorId = filter_var($this->option('actor'), FILTER_VALIDATE_INT);
        $actor = $actorId === false ? null : User::query()->find($actorId);

        if (! $actor instanceof User || ! $actor->is_active) {
            $this->components->error('Provide an active authorized staff member with --actor=.');

            return self::FAILURE;
        }

        if (Gate::forUser($actor)->denies('viewAny', BloodInventory::class)) {
            $this->components->error('The selected actor cannot view inventory.');

            return self::FAILURE;
        }

        $repair = (bool) $this->option('repair');
        $reason = trim((string) $this->option('reason'));

        if ($repair && mb_strlen($reason) < 10) {
            $this->components->error('A repair reason of at least 10 characters is required.');

            return self::FAILURE;
        }

        $centerId = filter_var($this->option('center'), FILTER_VALIDATE_INT);
        $center = $centerId === false ? null : BloodCenter::query()->find($centerId);

        if ($this->option('center') !== null && ! $center instanceof BloodCenter) {
            $this->components->error('The requested blood center does not exist.');

            return self::FAILURE;
        }

        if ($center instanceof BloodCenter && ! $actor->hasCenterAccess($center)) {
            $this->components->error('The selected actor cannot access the requested blood center.');

            return self::FAILURE;
        }

        $query = BloodInventory::query()
            ->visibleTo($actor)
            ->with('bloodCenter')
            ->when($center instanceof BloodCenter, fn (Builder $builder): Builder => $builder->whereBelongsTo($center))
            ->orderBy('blood_center_id')
            ->orderBy('blood_group');

        $rows = [];
        $mismatches = 0;
        $repaired = 0;

        foreach ($query->lazyById(100) as $inventory) {
            $result = $reconcileInventory->execute(
                inventory: $inventory,
                actor: $actor,
                repair: $repair,
                reason: $repair ? $reason : null,
            );

            $mismatches += (int) $result['mismatch'];
            $repaired += (int) $result['repaired'];
            $rows[] = [
                $inventory->bloodCenter->name,
                $inventory->blood_group->value,
                "{$result['current_available']} / {$result['expected_available']}",
                "{$result['current_reserved']} / {$result['expected_reserved']}",
                $result['mismatch'] ? ($result['repaired'] ? 'repaired' : 'mismatch') : 'matched',
            ];
        }

        if ($rows === []) {
            $this->components->info('No inventory records were available in the selected scope.');

            return self::SUCCESS;
        }

        $this->table(
            ['Center', 'Group', 'Available current/expected', 'Reserved current/expected', 'Result'],
            $rows,
        );
        $this->components->info('Checked '.count($rows)." record(s); {$mismatches} mismatch(es); {$repaired} repaired.");

        return self::SUCCESS;
    }
}
