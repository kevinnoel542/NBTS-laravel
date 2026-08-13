<?php

namespace App\Models;

use App\ReleaseAuthorizationDecision;
use Carbon\CarbonImmutable;
use Database\Factories\ReleaseAuthorizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $blood_unit_id
 * @property string $criteria_version
 * @property ReleaseAuthorizationDecision $decision
 * @property array<int, array<string, mixed>> $evaluated_tests
 * @property array<int, string>|null $exceptions
 * @property int $approved_by
 * @property int|null $independent_approved_by
 * @property int|null $released_by
 * @property CarbonImmutable $authorized_at
 * @property bool $electronic_signature
 */
#[Fillable([
    'blood_unit_id',
    'criteria_version',
    'decision',
    'evaluated_tests',
    'exceptions',
    'approved_by',
    'independent_approved_by',
    'released_by',
    'authorized_at',
    'reason',
    'electronic_signature',
])]
class ReleaseAuthorization extends Model
{
    /** @use HasFactory<ReleaseAuthorizationFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'authorized_at' => 'immutable_datetime',
            'decision' => ReleaseAuthorizationDecision::class,
            'electronic_signature' => 'boolean',
            'evaluated_tests' => 'array',
            'exceptions' => 'array',
        ];
    }

    /** @return BelongsTo<BloodUnit, $this> */
    public function bloodUnit(): BelongsTo
    {
        return $this->belongsTo(BloodUnit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function independentApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'independent_approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }
}
