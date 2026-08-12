<?php

namespace App\Models;

use Database\Factories\DonorIdentityAliasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'canonical_donor_id',
    'source_donor_id',
    'duplicate_case_id',
    'source_donor_identifier',
    'merged_by',
    'reason',
    'merged_at',
])]
class DonorIdentityAlias extends Model
{
    /** @use HasFactory<DonorIdentityAliasFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['merged_at' => 'immutable_datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function canonicalDonor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'canonical_donor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function sourceDonor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'source_donor_id');
    }

    /** @return BelongsTo<DonorDuplicateCase, $this> */
    public function duplicateCase(): BelongsTo
    {
        return $this->belongsTo(DonorDuplicateCase::class);
    }

    /** @return BelongsTo<User, $this> */
    public function merger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }
}
