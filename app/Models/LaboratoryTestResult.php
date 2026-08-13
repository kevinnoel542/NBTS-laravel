<?php

namespace App\Models;

use App\LaboratoryTestInterpretation;
use App\LaboratoryTestResultStatus;
use Database\Factories\LaboratoryTestResultFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'laboratory_test_order_id',
    'laboratory_test_run_id',
    'laboratory_test_catalog_id',
    'laboratory_quality_control_run_id',
    'entered_by',
    'verified_by',
    'result_value',
    'interpretation',
    'status',
    'is_release_blocking',
    'resulted_at',
    'verified_at',
    'comments',
])]
class LaboratoryTestResult extends Model
{
    /** @use HasFactory<LaboratoryTestResultFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'interpretation' => LaboratoryTestInterpretation::class,
            'is_release_blocking' => 'boolean',
            'resulted_at' => 'immutable_datetime',
            'status' => LaboratoryTestResultStatus::class,
            'verified_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<LaboratoryTestOrder, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestOrder::class, 'laboratory_test_order_id');
    }

    /** @return BelongsTo<LaboratoryTestRun, $this> */
    public function run(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestRun::class, 'laboratory_test_run_id');
    }

    /** @return BelongsTo<LaboratoryTestCatalog, $this> */
    public function testCatalog(): BelongsTo
    {
        return $this->belongsTo(LaboratoryTestCatalog::class, 'laboratory_test_catalog_id');
    }

    /** @return BelongsTo<LaboratoryQualityControlRun, $this> */
    public function qualityControlRun(): BelongsTo
    {
        return $this->belongsTo(LaboratoryQualityControlRun::class, 'laboratory_quality_control_run_id');
    }

    /** @return BelongsTo<User, $this> */
    public function enteredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
