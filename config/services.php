<?php

return [
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),
    ],

    'tawk' => [
        'enabled' => env('TAWK_ENABLED', true),
        'property_id' => env('TAWK_PROPERTY_ID'),
        'widget_id' => env('TAWK_WIDGET_ID'),
    ],
];


