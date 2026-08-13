<?php

namespace App\Models;

use App\LaboratoryTestOrderStatus;
use Database\Factories\LaboratoryTestOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'laboratory_specimen_receipt_id',
    'specimen_id',
    'laboratory_test_catalog_id',
    'ordered_by',
    'status',
    'ordered_at',
    'due_at',
    'cancellation_reason',
])]
class LaboratoryTestOrder extends Model
{
    /** @use HasFactory<LaboratoryTestOrderFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'due_at' => 'immutable_datetime',
            'ordered_at' => 'immutable_datetime',
            'status' => LaboratoryTestOrderStatus::class,
        ];
    }

    /** @return BelongsTo<LaboratorySpecimenReceipt, $this> */
    public function receipt(): BelongsTo
    {
        return $this->belongsTo(LaboratorySpecimenReceipt::class, 'laboratory_specimen_receipt_id');
    }

    /** @return BelongsTo<Specimen, $this> */
    public function specimen(): BelongsTo
    {
        return $this->belongsTo(Specimen::class);
    }

    /** @return BelongsTo<LaboratoryTestCatalog, $this> */
    public function testCatalog(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestCatalog::class, 'laboratory_test_catalog_id');
    }

    /** @return BelongsTo<User, $this> */
    public function orderer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }

    /** @return HasMany<LaboratoryTestRun, $this> */
    public function runs(): HasMany
    {
        return $this->hasMany(LaboratoryTestRun::class);
    }

    /** @return HasMany<LaboratoryTestResult, $this> */
    public function results(): HasMany
    {
        return $this->hasMany(LaboratoryTestResult::class);
    }
}
