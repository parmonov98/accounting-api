<?php

return [
    'default_driver' => env('CURRENCY_DRIVER', 'average'),
    'cache_ttl' => env('CURRENCY_CACHE_TTL', 300), // 5 minutes
    
    'services' => [
        'xml' => [
            'url' => env('CURRENCY_XML_SERVICE_URL', 'https://api.example1.com/rates.xml'),
            'key' => env('CURRENCY_XML_SERVICE_KEY'),
        ],
        'json' => [
            'url' => env('CURRENCY_JSON_SERVICE_URL', 'https://api.example2.com/rates.json'),
            'key' => env('CURRENCY_JSON_SERVICE_KEY'),
        ],
        'csv' => [
            'url' => env('CURRENCY_CSV_SERVICE_URL', 'https://api.example3.com/rates.csv'),
            'key' => env('CURRENCY_CSV_SERVICE_KEY'),
        ],
    ],
];
