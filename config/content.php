<?php

return [
    'locales' => ['en', 'sw'],

    'base_locale' => 'en',

    'storage_strategy' => 'polymorphic_translation_records',

    'publish_requires_base_locale' => true,

    'machine_translation_enabled' => false,

    'managed_fields' => [
        'articles' => [
            'title',
            'category',
            'summary',
            'body',
            'meta_description',
            'attachment_name',
        ],
        'campaigns' => [
            'title',
            'description',
            'location',
        ],
        'blood_centers' => [
            'name',
            'address',
            'opening_hours',
            'services',
            'capacity_label',
            'center_type',
        ],
        'badges' => [
            'name',
            'description',
        ],
        'rewards' => [
            'name',
            'description',
        ],
        'static_pages' => [
            'title',
            'summary',
            'body',
            'meta_description',
        ],
    ],

    'notification_strategy' => 'render_in_recipient_locale_on_send',
];
