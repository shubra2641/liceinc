<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Envato API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Envato Market API integration including OAuth
    | credentials and API endpoints.
    |
    */

    'client_id' => env('ENVATO_CLIENT_ID'),
    'client_secret' => env('ENVATO_CLIENT_SECRET'),
    'redirect_uri' => env('ENVATO_REDIRECT_URI', env('APP_URL').'/auth/envato/callback'),

    /*
    |--------------------------------------------------------------------------
    | Personal Token (for server-side API calls)
    |--------------------------------------------------------------------------
    |
    | This token is used for verifying purchases and accessing author endpoints.
    | You can generate this token from your Envato Account settings.
    |
    */
    'token' => env('ENVATO_PERSONAL_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | API Base URLs
    |--------------------------------------------------------------------------
    */
    'api_base' => 'https://api.envato.com',

    /*
    |--------------------------------------------------------------------------
    | OAuth Scopes
    |--------------------------------------------------------------------------
    |
    | Scopes required for the OAuth flow. These determine what data
    | the application can access from the user's Envato account.
    |
    */
    'scopes' => [
        'user:username',
        'user:email',
        'purchase:verify',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for API responses to reduce API calls and improve performance.
    |
    */
    'cache' => [
        'purchase_verification' => env('ENVATO_CACHE_PURCHASE_MINUTES', 30),
        'user_info' => env('ENVATO_CACHE_USER_HOURS', 1),
        'item_info' => env('ENVATO_CACHE_ITEM_HOURS', 6),
    ],
];
