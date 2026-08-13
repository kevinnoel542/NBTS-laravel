<?php

namespace App\Models;

use App\LaboratoryTestRunStatus;
use Database\Factories\LaboratoryTestRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'laboratory_test_order_id',
    'laboratory_test_catalog_id',
    'laboratory_equipment_id',
    'laboratory_reagent_lot_id',
    'operator_id',
    'method_version',
    'status',
    'started_at',
    'ended_at',
    'control_lot',
    'raw_payload',
    'comments',
])]
class LaboratoryTestRun extends Model
{
    /** @use HasFactory<LaboratoryTestRunFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'ended_at' => 'immutable_datetime',
            'raw_payload' => 'array',
            'started_at' => 'immutable_datetime',
            'status' => LaboratoryTestRunStatus::class,
        ];
    }

    /** @return BelongsTo<LaboratoryTestOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestOrder::class, 'laboratory_test_order_id');
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
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /** @return HasMany<LaboratoryTestResult, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(LaboratoryTestResult::class);
    }
}
