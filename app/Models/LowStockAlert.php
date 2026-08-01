<?php

namespace App\Models;

use App\BloodGroup;
use App\LowStockAlertStatus;
use Database\Factories\LowStockAlertFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_center_id',
    'blood_group',
    'available_units',
    'minimum_threshold',
    'status',
    'resolved_at',
])]
class LowStockAlert extends Model
{
    /** @use HasFactory<LowStockAlertFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blood_group' => BloodGroup::class,
            'resolved_at' => 'datetime',
            'status' => LowStockAlertStatus::class,
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function stockGap(): int
    {
        return max(0, $this->minimum_threshold - $this->available_units);
    }

    public function severity(): string
    {
        if ($this->available_units === 0) {
            return 'critical';
        }

        return $this->stockGap() >= 3 ? 'high' : 'low';
    }
}
