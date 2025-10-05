<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | License Management System. These settings help protect against
    | common security vulnerabilities.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Input Validation Settings
    |--------------------------------------------------------------------------
    |
    | Configure validation rules and sanitization options
    |
    */
    'validation' => [
        'max_file_size' => env('SECURITY_MAX_FILE_SIZE', 2048), // KB
        'allowed_image_types' => ['jpeg', 'png', 'jpg', 'gif', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'txt'],
        'max_string_length' => env('SECURITY_MAX_STRING_LENGTH', 10000),
        'sanitize_html' => env('SECURITY_SANITIZE_HTML', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limiting for API endpoints and sensitive operations
    |
    */
    'rate_limiting' => [
        'api_requests_per_minute' => env('SECURITY_API_RATE_LIMIT', 60),
        'login_attempts_per_minute' => env('SECURITY_LOGIN_RATE_LIMIT', 5),
        'license_verification_per_minute' => env('SECURITY_LICENSE_RATE_LIMIT', 30),
        'password_reset_per_hour' => env('SECURITY_PASSWORD_RESET_RATE_LIMIT', 3),
    ],

    /*
    |--------------------------------------------------------------------------
    | XSS Protection
    |--------------------------------------------------------------------------
    |
    | Configure Cross-Site Scripting protection settings
    |
    */
    'xss_protection' => env('SECURITY_XSS_PROTECTION', true),

    /*
    |--------------------------------------------------------------------------
    | SQL Injection Protection
    |--------------------------------------------------------------------------
    |
    | Configure SQL injection protection settings
    |
    */
    'sql_injection_protection' => env('SECURITY_SQL_INJECTION_PROTECTION', true),

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    |
    | Configure Cross-Site Request Forgery protection
    |
    */
    'csrf_protection' => env('SECURITY_CSRF_PROTECTION', true),

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configure session security settings
    |
    */
    'session_security' => [
        'regenerate_on_login' => true,
        'regenerate_on_privilege_change' => true,
        'timeout' => env('SECURITY_SESSION_TIMEOUT', 7200), // seconds
        'secure_cookies' => env('SECURITY_SECURE_COOKIES', true),
        'http_only_cookies' => true,
        'same_site_cookies' => 'strict',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    |
    | Configure password security requirements
    |
    */
    'password_security' => [
        'min_length' => env('SECURITY_PASSWORD_MIN_LENGTH', 8),
        'require_uppercase' => env('SECURITY_PASSWORD_REQUIRE_UPPERCASE', true),
        'require_lowercase' => env('SECURITY_PASSWORD_REQUIRE_LOWERCASE', true),
        'require_numbers' => env('SECURITY_PASSWORD_REQUIRE_NUMBERS', true),
        'require_symbols' => env('SECURITY_PASSWORD_REQUIRE_SYMBOLS', false),
        'prevent_common_passwords' => env('SECURITY_PREVENT_COMMON_PASSWORDS', true),
        'password_history_count' => env('SECURITY_PASSWORD_HISTORY_COUNT', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    |
    | Configure API security settings
    |
    */
    'api_security' => [
        'require_authentication' => true,
        'use_api_tokens' => env('SECURITY_USE_API_TOKENS', true),
        'token_expiration' => env('SECURITY_API_TOKEN_EXPIRATION', 86400), // seconds
        'validate_origin' => env('SECURITY_VALIDATE_ORIGIN', true),
        'allowed_origins' => env('SECURITY_ALLOWED_ORIGINS', '*'),
        'log_api_calls' => env('SECURITY_LOG_API_CALLS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    |
    | Configure file upload security settings
    |
    */
    'file_upload_security' => [
        'enabled' => env('FILE_UPLOAD_SECURITY_ENABLED', true),
        'allowed_mimes' => ['jpeg', 'png', 'jpg', 'gif', 'svg', 'pdf', 'doc', 'docx', 'zip'],
        'max_size_kb' => 2048, // 2MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure security logging and monitoring
    |
    */
    'logging' => [
        'log_failed_logins' => env('SECURITY_LOG_FAILED_LOGINS', true),
        'log_privilege_escalations' => env('SECURITY_LOG_PRIVILEGE_ESCALATIONS', true),
        'log_data_access' => env('SECURITY_LOG_DATA_ACCESS', false),
        'log_api_calls' => env('SECURITY_LOG_API_CALLS', true),
        'alert_on_suspicious_activity' => env('SECURITY_ALERT_SUSPICIOUS_ACTIVITY', true),
        'max_log_file_size' => env('SECURITY_MAX_LOG_FILE_SIZE', 10240), // KB
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    |
    | Configure Content Security Policy headers
    |
    */
    'content_security_policy' => [
        'enabled' => env('SECURITY_CSP_ENABLED', true),
        'report_only' => env('SECURITY_CSP_REPORT_ONLY', false),
        'directives' => [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' "
                .'https://cdn.jsdelivr.net https://cdnjs.cloudflare.com',
            'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            'font-src' => "'self' https://fonts.gstatic.com",
            'img-src' => "'self' data: https:",
            'connect-src' => "'self' https://api.envato.com",
            'frame-ancestors' => "'none'",
            'base-uri' => "'self'",
            'form-action' => "'self'",
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Headers Security
    |--------------------------------------------------------------------------
    |
    | Configure security headers
    |
    */
    'headers' => [
        'x_frame_options' => 'DENY',
        'x_content_type_options' => 'nosniff',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'camera=(), microphone=(), geolocation=()',
        'strict_transport_security' => 'max-age=31536000; includeSubDomains',
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist/Blacklist
    |--------------------------------------------------------------------------
    |
    | Configure IP-based access control
    |
    */
    'ip_control' => [
        'enabled' => env('SECURITY_IP_CONTROL_ENABLED', false),
        'whitelist' => env('SECURITY_IP_WHITELIST', ''),
        'blacklist' => env('SECURITY_IP_BLACKLIST', ''),
        'block_tor_exit_nodes' => env('SECURITY_BLOCK_TOR', false),
        'block_vpn_proxies' => env('SECURITY_BLOCK_VPN', false),
    ],
];
