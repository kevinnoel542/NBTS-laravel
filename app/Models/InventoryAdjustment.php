<?php

namespace App\Models;

use App\BloodGroup;
use Database\Factories\InventoryAdjustmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_center_id',
    'blood_unit_id',
    'adjusted_by',
    'blood_group',
    'quantity_delta',
    'reserved_quantity_delta',
    'reason',
    'notes',
])]
class InventoryAdjustment extends Model
{
    /** @use HasFactory<InventoryAdjustmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<BloodUnit, $this> */
    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function direction(): string
    {
        return match (true) {
            $this->quantity_delta > 0 => 'increase',
            $this->quantity_delta < 0 => 'decrease',
            default => 'no_change',
        };
    }
}
