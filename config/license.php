<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | License Management Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the License Management
    | System, including license generation, validation, and verification.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | License Generation
    |--------------------------------------------------------------------------
    |
    | Configure license key generation settings
    | Note: Settings are now read from database with fallback to env
    |
    */
    'auto_generate_license' => true,
    'default_license_length' => 32,

    /*
    |--------------------------------------------------------------------------
    | License Types
    |--------------------------------------------------------------------------
    |
    | Define different license types and their characteristics
    |
    */
    'types' => [
        'regular' => [
            'name' => 'Regular License',
            'description' => 'Standard license for single end product',
            'max_domains' => 1,
            'support_period_days' => 365,
            'can_resell' => false,
            'commercial_use' => true,
        ],
        'extended' => [
            'name' => 'Extended License',
            'description' => 'Extended license for multiple end products',
            'max_domains' => 5,
            'support_period_days' => 365,
            'can_resell' => true,
            'commercial_use' => true,
        ],
        'developer' => [
            'name' => 'Developer License',
            'description' => 'License for developers and agencies',
            'max_domains' => -1, // Unlimited
            'support_period_days' => 730, // 2 years
            'can_resell' => true,
            'commercial_use' => true,
        ],
        'trial' => [
            'name' => 'Trial License',
            'description' => 'Limited trial license',
            'max_domains' => 1,
            'support_period_days' => 30,
            'can_resell' => false,
            'commercial_use' => false,
            'expires_after_days' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | License Validation
    |--------------------------------------------------------------------------
    |
    | Configure license validation and verification settings
    | Note: Some settings are now read from database
    |
    */
    'validation' => [
        'verify_with_envato' => true,
        'fallback_to_internal' => true,
        'cache_verification_results' => true,
        'cache_duration_minutes' => 60,
        'allow_offline_verification' => false,
        'grace_period_days' => 7,
        'max_verification_attempts' => 5,
        'lockout_duration_minutes' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Domain Management
    |--------------------------------------------------------------------------
    |
    | Configure domain authorization and management
    |
    */
    'domains' => [
        'allow_localhost' => true,
        'allow_ip_addresses' => false,
        'allow_wildcards' => true,
        'validate_ssl_certificates' => false,
        'auto_approve_subdomains' => false,
        'max_domains_per_license' => 5,
        'domain_change_cooldown_hours' => 24,
    ],

    /*
    |--------------------------------------------------------------------------
    | License Templates
    |--------------------------------------------------------------------------
    |
    | Configure license file templates and generation
    |
    */
    'templates' => [
        'template_directory' => resource_path('templates/licenses'),
        'auto_create_templates' => env('LICENSE_AUTO_CREATE_TEMPLATES', true),
        'template_cache_enabled' => env('LICENSE_TEMPLATE_CACHE', true),
        'template_cache_duration' => env('LICENSE_TEMPLATE_CACHE_DURATION', 3600),
        'include_verification_code' => env('LICENSE_INCLUDE_VERIFICATION', true),
        'obfuscate_generated_code' => env('LICENSE_OBFUSCATE_CODE', false),
        'minify_generated_code' => env('LICENSE_MINIFY_CODE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Integration
    |--------------------------------------------------------------------------
    |
    | Configure API endpoints and integration settings
    | Note: API token is now read from database settings table
    |
    */
    'api' => [
        'verification_endpoint' => '/api/license/verify',
        'status_endpoint' => '/api/license/status',
        'domain_endpoint' => '/api/license/domains',
        'api_token' => '',
        'require_api_key' => env('LICENSE_API_REQUIRE_KEY', false),
        'api_key_header' => 'X-License-API-Key',
        'rate_limit_per_minute' => env('LICENSE_API_RATE_LIMIT', 60),
        'log_api_requests' => env('LICENSE_LOG_API_REQUESTS', true),
        'return_detailed_errors' => env('LICENSE_DETAILED_ERRORS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Envato Integration
    |--------------------------------------------------------------------------
    |
    | Configure Envato API integration settings
    | Note: Sensitive data is now read from database settings table
    |
    */
    'envato' => [
        'api_base_url' => env('ENVATO_API_BASE', 'https://api.envato.com'),
        'personal_token' => '',
        'client_id' => '',
        'client_secret' => '',
        'redirect_uri' => '/auth/envato/callback',
        'timeout_seconds' => env('ENVATO_TIMEOUT', 30),
        'retry_attempts' => env('ENVATO_RETRY_ATTEMPTS', 3),
        'cache_user_data' => env('ENVATO_CACHE_USER_DATA', true),
        'cache_duration_minutes' => env('ENVATO_CACHE_DURATION', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | License Expiration
    |--------------------------------------------------------------------------
    |
    | Configure license expiration and renewal settings
    |
    */
    'expiration' => [
        'default_license_duration_days' => 365,
        'support_duration_days' => 365,
        'renewal_reminder_days' => 30,
        'grace_period_after_expiration' => 7,
        'auto_suspend_expired_licenses' => true,
        'allow_expired_verification' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging and Monitoring
    |--------------------------------------------------------------------------
    |
    | Configure license logging and monitoring
    |
    */
    'logging' => [
        'log_verifications' => env('LICENSE_LOG_VERIFICATIONS', true),
        'log_failed_verifications' => env('LICENSE_LOG_FAILED_VERIFICATIONS', true),
        'log_domain_changes' => env('LICENSE_LOG_DOMAIN_CHANGES', true),
        'log_license_generation' => env('LICENSE_LOG_GENERATION', true),
        'log_api_calls' => env('LICENSE_LOG_API_CALLS', true),
        'detailed_logging' => env('LICENSE_DETAILED_LOGGING', false),
        'log_retention_days' => env('LICENSE_LOG_RETENTION', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure license security settings
    |
    */
    'security' => [
        'encrypt_license_data' => true,
        'use_secure_tokens' => true,
        'validate_request_signatures' => false,
        'prevent_license_sharing' => true,
        'detect_suspicious_activity' => true,
        'block_vpn_verification' => false,
        'require_https' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure license-related notifications
    |
    */
    'notifications' => [
        'notify_on_verification' => false,
        'notify_on_expiration' => true,
        'notify_on_domain_change' => true,
        'notify_on_suspicious_activity' => true,
        'notification_email' => '',
        'use_slack_notifications' => false,
        'slack_webhook_url' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure performance optimization settings
    |
    */
    'performance' => [
        'enable_caching' => true,
        'cache_driver' => 'redis',
        'enable_query_optimization' => true,
        'batch_verification_size' => 100,
        'use_database_indexes' => true,
        'compress_api_responses' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing and Development
    |--------------------------------------------------------------------------
    |
    | Configure testing and development settings
    |
    */
    'testing' => [
        'allow_test_licenses' => true,
        'test_license_prefix' => 'TEST-',
        'bypass_verification_in_testing' => false,
        'mock_envato_responses' => false,
        'generate_fake_data' => false,
    ],
];
