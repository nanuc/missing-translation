<?php

return [
    'base-locale' => env('MISSING_TRANSLATION_BASE_LOCALE', 'en'),
    'enable-realtime-check' => env('MISSING_TRANSLATION_ENABLE_REALTIME_CHECK', false),
    'deep-l' => [
        'endpoint' => env('DEEP_L_ENDPOINT', 'https://api-free.deepl.com/v2/translate'),
        'auth-key' => env('DEEP_L_AUTH_KEY'),
    ],
];
