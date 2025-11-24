<?php

return [
    'payvibe' => [
        'base_url' => env('PAYVIBE_BASE_URL', 'https://payvibeapi.six3tech.com/api/v1'),
        'public_key' => env('PAYVIBE_API_KEY'),
        'secret_key' => env('PAYVIBE_SECRET_KEY'),
        'product_identifier' => env('PAYVIBE_PRODUCT_IDENTIFIER', 'biggestlogs'),
    ],
];

