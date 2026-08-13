<?php

namespace App\Models;

use App\LaboratoryQualityEventStatus;
use App\LaboratoryQualityEventType;
use App\LaboratoryQualitySeverity;
use Database\Factories\LaboratoryQualityEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'blood_center_id',
    'laboratory_test_catalog_id',
    'laboratory_equipment_id',
    'laboratory_reagent_lot_id',
    'opened_by',
    'closed_by',
    'type',
    'severity',
    'status',
    'title',
    'description',
    'affected_identifiers',
    'corrective_action',
    'opened_at',
    'closed_at',
])]
class LaboratoryQualityEvent extends Model
{
    /** @use HasFactory<LaboratoryQualityEventFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'affected_identifiers' => 'array',
            'closed_at' => 'immutable_datetime',
            'opened_at' => 'immutable_datetime',
            'severity' => LaboratoryQualitySeverity::class,
            'status' => LaboratoryQualityEventStatus::class,
            'type' => LaboratoryQualityEventType::class,
        ];
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return BelongsTo<LaboratoryTestCatalog, $this> */
    public function testCatalog(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestCatalog::class, 'laboratory_test_catalog_id');
    }

    /** @return BelongsTo<LaboratoryEquipment, $this> */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(LaboratoryEquipment::class, 'laboratory_equipment_id');
    }

    /** @return BelongsTo<LaboratoryReagentLot, $this> */
    public function reagentLot(): BelongsTo
    {
        return $this->belongsTo(LaboratoryReagentLot::class, 'laboratory_reagent_lot_id');
    }

    /** @return BelongsTo<User, $this> */
    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /** @return BelongsTo<User, $this> */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }
}
