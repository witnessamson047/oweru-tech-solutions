<?php

return [
    'scanner' => [
        'url' => env('SCANNER_SERVICE_URL', 'http://localhost:5000'),
        'api_key' => env('SCANNER_API_KEY', ''),
    ],
    'pesapal' => [
        'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
        'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
        'base_url' => env('PESAPAL_BASE_URL', 'https://cybqa.pesapal.com/pesapalv3'),
        'ipn_url' => env('PESAPAL_IPN_URL'),
    ],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
];
