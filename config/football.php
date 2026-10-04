<?php

return [
    'api_key' => env('FOOTBALL_API_KEY'),
    'base_url' => env('FOOTBALL_API_BASE_URL', 'https://api.5dollarfootballapi.com/v1'),
    'timeout' => (int) env('FOOTBALL_API_TIMEOUT', 10),
    'cache' => [
        'enabled' => (bool) env('FOOTBALL_API_CACHE', true),
        'store' => env('FOOTBALL_API_CACHE_STORE'),
        'prefix' => 'football',
        'ttl' => [
            'countries' => 86_400,
            'bookmakers' => 86_400,
            'leagues' => 3_600,
            'league' => 3_600,
            'team' => 3_600,
        ],
    ],
    'retry' => [
        'max_wait' => (int) env('FOOTBALL_API_RETRY_MAX_WAIT', 2),
        'times' => 1,
    ],
];
