<?php

namespace App\Models;

use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LogicException;

#[Guarded(['*'])]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new LogicException('Audit records cannot be updated.');
        });

        static::deleting(function (): never {
            throw new LogicException('Audit records cannot be deleted.');
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }

    /** @return MorphTo<Model, $this> */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
