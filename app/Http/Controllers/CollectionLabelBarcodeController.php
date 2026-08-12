<?php

namespace App\Http\Controllers;

use App\Models\CollectionLabel;
use App\Services\Code128Barcode;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class CollectionLabelBarcodeController extends Controller
{
    public function __invoke(CollectionLabel $collectionLabel, Code128Barcode $barcode): Response
    {
        $collectionLabel->loadMissing('collectionEpisode');
        Gate::authorize('view', $collectionLabel);

        return response($barcode->svg($collectionLabel->label_identifier), 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
