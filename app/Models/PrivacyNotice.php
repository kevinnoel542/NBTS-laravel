<?php

namespace App\Models;

use Database\Factories\PrivacyNoticeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'approved_by',
    'notice_code',
    'version',
    'title',
    'channels',
    'consent_scope',
    'communication_preferences',
    'status',
    'effective_from',
    'retired_at',
    'approved_at',
])]
class PrivacyNotice extends Model
{
    /** @use HasFactory<PrivacyNoticeFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'channels' => 'array',
            'communication_preferences' => 'array',
            'consent_scope' => 'array',
            'effective_from' => 'date',
            'retired_at' => 'date',
        ];
    }
}
