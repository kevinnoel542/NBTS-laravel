<?php

return [
    'mobile_token_expiration_days' => (int) env('NBTS_MOBILE_TOKEN_EXPIRATION_DAYS', 30),

    'donor_card_qr_ttl_seconds' => (int) env('NBTS_DONOR_CARD_QR_TTL_SECONDS', 300),

    'donor_card_qr_signing_key' => env('NBTS_DONOR_CARD_QR_SIGNING_KEY'),

    'appointment_booking_window_days' => (int) env('NBTS_APPOINTMENT_BOOKING_WINDOW_DAYS', 90),

    'appointment_slot_capacity' => (int) env('NBTS_APPOINTMENT_SLOT_CAPACITY', 1),

    'appointment_slot_times' => [
        '08:00',
        '09:30',
        '11:00',
        '13:00',
        '14:30',
        '16:00',
    ],

    /*
    |--------------------------------------------------------------------------
    | Whole blood donation policy
    |--------------------------------------------------------------------------
    |
    | NBTS Tanzania's donor-selection guidance requires a minimum interval of
    | three months for men and four months for women. The conservative four-
    | month interval applies when a donor is recorded as other or unspecified.
    |
    */
    'whole_blood_intervals_months' => [
        'male' => 3,
        'female' => 4,
        'other' => 4,
        'default' => 4,
    ],

    'whole_blood_shelf_life_days' => (int) env('NBTS_WHOLE_BLOOD_SHELF_LIFE_DAYS', 35),
];
