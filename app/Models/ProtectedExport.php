<?php

namespace App\Models;

use Database\Factories\ProtectedExportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'requested_by',
    'approved_by',
    'export_reference',
    'purpose',
    'recipient',
    'scope',
    'delivery_channel',
    'encrypted_manifest',
    'status',
    'expires_at',
    'approved_at',
    'downloaded_at',
])]
#[Hidden(['encrypted_manifest'])]
class ProtectedExport extends Model
{
    /** @use HasFactory<ProtectedExportFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'approved_at' => 'immutable_datetime',
            'downloaded_at' => 'immutable_datetime',
            'encrypted_manifest' => 'encrypted:array',
            'expires_at' => 'immutable_datetime',
            'scope' => 'array',
        ];
    }
}
