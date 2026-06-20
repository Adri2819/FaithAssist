<?php

return [
    'whatsapp' => [
        'token' => env('META_WHATSAPP_TOKEN'),
        'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
        'api_version' => env('META_WHATSAPP_API_VERSION', 'v25.0'),
        'base_url' => env('META_WHATSAPP_BASE_URL', 'https://graph.facebook.com'),
    ],
];
