<?php

return [
    /* |-------------------------------------------------------------------------- | License Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the main license server that manages updates
    | and license verification for all products.
    |
    */

    'url' => env('LICENSE_SERVER_URL', env('APP_URL') . '/api'),

    'domain' => env('LICENSE_SERVER_DOMAIN', \App\Helpers\SecureFileHelper::parseUrl(env('APP_URL'), PHP_URL_HOST) ?: 'my-logos.com'),

    'timeout' => env('LICENSE_SERVER_TIMEOUT', 30),

    'cache_ttl' => [
        'updates' => env('LICENSE_CACHE_UPDATES_TTL', 300), // 5 minutes
        'history' => env('LICENSE_CACHE_HISTORY_TTL', 600), // 10 minutes
        'latest' => env('LICENSE_CACHE_LATEST_TTL', 300), // 5 minutes
        'products' => env('LICENSE_CACHE_PRODUCTS_TTL', 1800), // 30 minutes
    ],

    'retry_attempts' => env('LICENSE_RETRY_ATTEMPTS', 3),

    'retry_delay' => env('LICENSE_RETRY_DELAY', 1000), // milliseconds

    /* |-------------------------------------------------------------------------- | Default Product Configuration
    |--------------------------------------------------------------------------
    |
    | Default product settings when no specific product is specified
    |
    */

    'default_product' => [
        'slug' => env('DEFAULT_PRODUCT_SLUG', 'the-ultimate-license-management-system'),
        'name' => env('DEFAULT_PRODUCT_NAME', 'The Ultimate License Management System'),
    ],

    /* |-------------------------------------------------------------------------- | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Security settings for license verification and update downloads
    |
    */

    'security' => [
        'verify_ssl' => env('LICENSE_VERIFY_SSL', true),
        'allowed_domains' => env('LICENSE_ALLOWED_DOMAINS', ''),
        'rate_limit' => [
            'enabled' => env('LICENSE_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('LICENSE_RATE_LIMIT_MAX_ATTEMPTS', 10),
            'decay_minutes' => env('LICENSE_RATE_LIMIT_DECAY_MINUTES', 60),
        ],
    ],

    /* |-------------------------------------------------------------------------- | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Logging settings for license server interactions
    |
    */

    'logging' => [
        'enabled' => env('LICENSE_LOGGING_ENABLED', true),
        'level' => env('LICENSE_LOGGING_LEVEL', 'info'),
        'log_failures' => env('LICENSE_LOG_FAILURES', true),
        'log_success' => env('LICENSE_LOG_SUCCESS', false),
    ],
];
