<?php

namespace App\Models;

use App\StaffCompetencyStatus;
use Database\Factories\StaffCompetencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'organization_unit_id',
    'code',
    'name',
    'status',
    'valid_from',
    'expires_at',
    'verified_by',
    'notes',
])]
class StaffCompetency extends Model
{
    /** @use HasFactory<StaffCompetencyFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
            'status' => StaffCompetencyStatus::class,
            'valid_from' => 'date',
        ];
    }

    /**
     * @param  Builder<StaffCompetency>  $query
     * @return Builder<StaffCompetency>
     */
    public function scopeEffective(Builder $query): Builder
    {
        return $query
            ->where('status', StaffCompetencyStatus::Active)
            ->where(fn (Builder $dateQuery): Builder => $dateQuery
                ->whereNull('valid_from')
                ->orWhereDate('valid_from', '<=', today()))
            ->where(fn (Builder $dateQuery): Builder => $dateQuery
                ->whereNull('expires_at')
                ->orWhereDate('expires_at', '>=', today()));
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<OrganizationUnit, $this> */
    public function organizationUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationUnit::class);
    }

    /** @return BelongsTo<User, $this> */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
