<?php

namespace App\Models;

use App\LaboratoryEquipmentStatus;
use App\LaboratoryInterfaceMode;
use Database\Factories\LaboratoryEquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_center_id',
    'code',
    'name',
    'equipment_type',
    'interface_mode',
    'status',
    'calibration_due_on',
    'maintenance_due_on',
    'last_validated_at',
    'downtime_started_at',
])]
class LaboratoryEquipment extends Model
{
    /** @use HasFactory<LaboratoryEquipmentFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'calibration_due_on' => 'date',
            'downtime_started_at' => 'immutable_datetime',
            'interface_mode' => LaboratoryInterfaceMode::class,
            'last_validated_at' => 'immutable_datetime',
            'maintenance_due_on' => 'date',
            'status' => LaboratoryEquipmentStatus::class,
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    public function permitsTestingUse(): bool
    {
        return $this->status === LaboratoryEquipmentStatus::Active
            && ($this->calibration_due_on === null || $this->calibration_due_on->isFuture() || $this->calibration_due_on->isToday())
            && ($this->maintenance_due_on === null || $this->maintenance_due_on->isFuture() || $this->maintenance_due_on->isToday());
    }
}
