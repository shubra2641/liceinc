<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Sensitive Data Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains sensitive configuration settings that should be
    | moved to environment variables for security.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Keys and Tokens
    |--------------------------------------------------------------------------
    */
    'api' => [
        'license_token' => env('LICENSE_API_TOKEN', ''),
        'envato_personal_token' => env('ENVATO_PERSONAL_TOKEN', ''),
        'envato_client_id' => env('ENVATO_CLIENT_ID', ''),
        'envato_client_secret' => env('ENVATO_CLIENT_SECRET', ''),
        'envato_username' => env('ENVATO_USERNAME', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Settings
    |--------------------------------------------------------------------------
    */
    'payment' => [
        'stripe_public_key' => env('STRIPE_PUBLIC_KEY', ''),
        'stripe_secret_key' => env('STRIPE_SECRET_KEY', ''),
        'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET', ''),
        'paypal_client_id' => env('PAYPAL_CLIENT_ID', ''),
        'paypal_client_secret' => env('PAYPAL_CLIENT_SECRET', ''),
        'paypal_webhook_id' => env('PAYPAL_WEBHOOK_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    'database' => [
        'encryption_key' => env('DB_ENCRYPTION_KEY', ''),
        'backup_password' => env('DB_BACKUP_PASSWORD', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    */
    'email' => [
        'smtp_password' => env('MAIL_PASSWORD', ''),
        'smtp_username' => env('MAIL_USERNAME', ''),
        'encryption_key' => env('MAIL_ENCRYPTION_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'jwt_secret' => env('JWT_SECRET', ''),
        'encryption_key' => env('ENCRYPTION_KEY', ''),
        'session_secret' => env('SESSION_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third-party Services
    |--------------------------------------------------------------------------
    */
    'services' => [
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),
        'facebook_app_id' => env('FACEBOOK_APP_ID', ''),
        'facebook_app_secret' => env('FACEBOOK_APP_SECRET', ''),
        'twitter_api_key' => env('TWITTER_API_KEY', ''),
        'twitter_api_secret' => env('TWITTER_API_SECRET', ''),
    ],
];
