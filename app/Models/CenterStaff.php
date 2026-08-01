<?php

namespace App\Models;

use Database\Factories\CenterStaffFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $blood_center_id
 * @property string $position
 * @property bool $is_active
 */
#[Fillable([
    'user_id',
    'blood_center_id',
    'position',
    'is_active',
])]
class CenterStaff extends Model
{
    /** @use HasFactory<CenterStaffFactory> */
    use HasFactory;

    protected $table = 'center_staff';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<BloodCenter, $this> */
    public function bloodCenter(): BelongsTo
    {
        return $this->belongsTo(BloodCenter::class);
    }
}
