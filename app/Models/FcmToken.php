<?php

namespace App\Models;

use App\DevicePlatform;
use Database\Factories\FcmTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $token
 * @property DevicePlatform $device_type
 */
#[Fillable([
    'user_id',
    'token',
    'device_type',
])]
class FcmToken extends Model
{
    /** @use HasFactory<FcmTokenFactory> */
    use HasFactory;

    protected $table = 'f_c_m_tokens';

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'device_type' => DevicePlatform::class,
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
