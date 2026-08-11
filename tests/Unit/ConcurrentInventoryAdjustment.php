<?php

namespace Tests\Unit;

use App\Actions\Inventory\AdjustInventory;
use App\Data\AdjustInventoryData;
use App\Models\BloodInventory;
use App\Models\User;
use Closure;
use Illuminate\Validation\ValidationException;

final class ConcurrentInventoryAdjustment
{
    public static function task(int $inventoryId, int $managerId): Closure
    {
        return static function () use ($inventoryId, $managerId): string {
            try {
                app(AdjustInventory::class)->execute(
                    inventory: BloodInventory::query()->findOrFail($inventoryId),
                    data: new AdjustInventoryData(
                        availableDelta: -1,
                        reservedDelta: 0,
                        reason: 'Parallel controlled stock decrement contention test.',
                    ),
                    actor: User::query()->findOrFail($managerId),
                );

                return 'adjusted';
            } catch (ValidationException) {
                return 'blocked';
            }
        };
    }
}
