<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration settings for the
    | application including validation rules, rate limiting, and security measures.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Input Validation Settings
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'max_string_length' => env('SECURITY_MAX_STRING_LENGTH', 10000),
        'sanitize_html' => env('SECURITY_SANITIZE_HTML', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS Protection Settings
    |--------------------------------------------------------------------------
    */
    'xss_protection' => [
        'allowed_tags' => env('SECURITY_ALLOWED_HTML_TAGS', ''),
        'enabled' => env('SECURITY_XSS_PROTECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Settings
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'api_requests_per_minute' => env('SECURITY_API_RATE_LIMIT', 300),
        'login_attempts_per_minute' => env('SECURITY_LOGIN_RATE_LIMIT', 20),
        'password_reset_per_hour' => env('SECURITY_PASSWORD_RESET_RATE_LIMIT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Control Settings
    |--------------------------------------------------------------------------
    */
    'ip_control' => [
        'blacklist' => env('SECURITY_IP_BLACKLIST', ''),
        'whitelist' => env('SECURITY_IP_WHITELIST', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security Settings
    |--------------------------------------------------------------------------
    */
    'file_upload_security' => [
        'max_upload_size' => env('SECURITY_MAX_UPLOAD_SIZE', 10240), // KB
        'allowed_extensions' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'txt'],
            'archives' => ['zip', 'rar'],
        ],
        'validate_file_content' => env('SECURITY_VALIDATE_FILE_CONTENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers Settings
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('SECURITY_X_XSS_PROTECTION', '1; mode=block'),
        'content_security_policy' => env('SECURITY_CSP', 'default-src \'self\''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security Settings
    |--------------------------------------------------------------------------
    */
    'session' => [
        'secure_cookie' => env('SESSION_SECURE_COOKIE', false),
        'http_only' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'lax'),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security Settings
    |--------------------------------------------------------------------------
    */
    'api' => [
        'token_expiration' => env('API_TOKEN_EXPIRATION', 3600), // seconds
        'rate_limit_per_minute' => env('API_RATE_LIMIT_PER_MINUTE', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'cipher' => env('SECURITY_CIPHER', 'AES-256-CBC'),
        'key_length' => env('SECURITY_KEY_LENGTH', 32),
    ],
];
