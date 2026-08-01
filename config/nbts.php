<?php

return [
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
