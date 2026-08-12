<?php

namespace App\Http\Controllers;

use App\Models\OfflineIdentifierBatch;
use App\Services\Code128Barcode;
use App\Services\CollectionIdentifierService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class OfflineDowntimeFormController extends Controller
{
    public function __invoke(
        Request $request,
        OfflineIdentifierBatch $offlineIdentifierBatch,
        CollectionIdentifierService $identifiers,
        Code128Barcode $barcode,
    ): Response {
        $offlineIdentifierBatch->loadMissing(['device', 'bloodCenter', 'issuer']);
        Gate::authorize('view', $offlineIdentifierBatch->device);

        $sequence = $request->integer('sequence', $offlineIdentifierBatch->next_sequence);
        if ($sequence < $offlineIdentifierBatch->start_sequence
            || $sequence > $offlineIdentifierBatch->end_sequence
            || $offlineIdentifierBatch->revoked_at !== null
            || $offlineIdentifierBatch->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'sequence' => ['Select an identifier from an active offline batch.'],
            ]);
        }

        $identifier = $identifiers->format(
            $offlineIdentifierBatch->bloodCenter,
            $offlineIdentifierBatch->year,
            $sequence,
        );

        return response()
            ->view('operations.offline-downtime-form', [
                'batch' => $offlineIdentifierBatch,
                'barcode' => $barcode->svg($identifier, height: 54, module: 1),
                'identifier' => $identifier,
                'sequence' => $sequence,
            ])
            ->header('Cache-Control', 'private, no-store, max-age=0')
            ->header('X-Content-Type-Options', 'nosniff');
    }
}
