<?php

return [
    'privacy_notice_version' => env('NBTS_PRIVACY_NOTICE_VERSION', 'construction-2026-08'),

    'identity_confirmation_hours' => (int) env('NBTS_IDENTITY_CONFIRMATION_HOURS', 12),

    'screening' => [
        'construction_protocol_code' => 'NBTS-WB-CONSTRUCTION',
        'construction_protocol_version' => 1,
        'minimum_age' => 18,
        'maximum_age' => 65,
        'minimum_weight_kg' => 50,
    ],

    'identifiers' => [
        'standard' => env('NBTS_COLLECTION_IDENTIFIER_STANDARD', 'NBTS-CONSTRUCTION-1'),
        'country_prefix' => 'TZ',
        'label_template_version' => env('NBTS_COLLECTION_LABEL_TEMPLATE', 'NBTS-CONSTRUCTION-1'),
    ],

    'collection' => [
        'target_volume_ml' => 450,
        'minimum_routine_volume_ml' => 350,
        'maximum_routine_volume_ml' => 550,
        'required_specimens' => [
            ['code' => 'serology', 'volume_ml' => 6],
            ['code' => 'edta', 'volume_ml' => 4],
        ],
    ],

    'offline' => [
        'identifier_batch_size' => 50,
        'identifier_batch_ttl_days' => 7,
    ],
];
