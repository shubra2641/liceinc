<?php

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
    'auto_generate_license' => function () {
        return App\Helpers\ConfigHelper::getSetting('auto_generate_license', true, 'LICENSE_AUTO_GENERATE');
    },
    'default_license_length' => function () {
        return App\Helpers\ConfigHelper::getSetting('default_license_length', 32, 'LICENSE_DEFAULT_LENGTH');
    },

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
        'verify_with_envato' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_verify_envato', true, 'LICENSE_VERIFY_ENVATO');
        },
        'fallback_to_internal' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_fallback_internal', true, 'LICENSE_FALLBACK_INTERNAL');
        },
        'cache_verification_results' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_cache_verification',
                true,
                'LICENSE_CACHE_VERIFICATION',
            );
        },
        'cache_duration_minutes' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_cache_duration', 60, 'LICENSE_CACHE_DURATION');
        },
        'allow_offline_verification' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_allow_offline', false, 'LICENSE_ALLOW_OFFLINE');
        },
        'grace_period_days' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_grace_period', 7, 'LICENSE_GRACE_PERIOD');
        },
        'max_verification_attempts' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_max_attempts', 5, 'LICENSE_MAX_ATTEMPTS');
        },
        'lockout_duration_minutes' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_lockout_minutes', 15, 'LICENSE_LOCKOUT_DURATION');
        },
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
        'allow_localhost' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_allow_localhost', true, 'LICENSE_ALLOW_LOCALHOST');
        },
        'allow_ip_addresses' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_allow_ip_addresses', false, 'LICENSE_ALLOW_IP');
        },
        'allow_wildcards' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_allow_wildcards', true, 'LICENSE_ALLOW_WILDCARDS');
        },
        'validate_ssl_certificates' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_validate_ssl',
                false,
                'LICENSE_VALIDATE_SSL',
            );
        },
        'auto_approve_subdomains' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_auto_approve_subdomains',
                false,
                'LICENSE_AUTO_APPROVE_SUBDOMAINS',
            );
        },
        'max_domains_per_license' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_max_domains', 5, 'LICENSE_MAX_DOMAINS');
        },
        'domain_change_cooldown_hours' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_domain_cooldown', 24, 'LICENSE_DOMAIN_COOLDOWN');
        },
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
        'api_token' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_api_token', '', 'LICENSE_API_TOKEN');
        },
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
        'personal_token' => function () {
            return App\Helpers\ConfigHelper::getSetting('envato_personal_token', '', 'ENVATO_PERSONAL_TOKEN');
        },
        'client_id' => function () {
            return App\Helpers\ConfigHelper::getSetting('envato_client_id', '', 'ENVATO_CLIENT_ID');
        },
        'client_secret' => function () {
            return App\Helpers\ConfigHelper::getSetting('envato_client_secret', '', 'ENVATO_CLIENT_SECRET');
        },
        'redirect_uri' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'envato_redirect_uri',
                config('app.url').'/auth/envato/callback',
                'ENVATO_REDIRECT_URI',
            );
        },
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
        'default_license_duration_days' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_default_duration', 365, 'LICENSE_DEFAULT_DURATION');
        },
        'support_duration_days' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_support_duration', 365, 'LICENSE_SUPPORT_DURATION');
        },
        'renewal_reminder_days' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_renewal_reminder', 30, 'LICENSE_RENEWAL_REMINDER');
        },
        'grace_period_after_expiration' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_expiration_grace', 7, 'LICENSE_EXPIRATION_GRACE');
        },
        'auto_suspend_expired_licenses' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_auto_suspend', true, 'LICENSE_AUTO_SUSPEND');
        },
        'allow_expired_verification' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_allow_expired_verification',
                false,
                'LICENSE_ALLOW_EXPIRED_VERIFICATION',
            );
        },
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
        'encrypt_license_data' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_encrypt_data', true, 'LICENSE_ENCRYPT_DATA');
        },
        'use_secure_tokens' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_secure_tokens', true, 'LICENSE_SECURE_TOKENS');
        },
        'validate_request_signatures' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_validate_signatures',
                false,
                'LICENSE_VALIDATE_SIGNATURES',
            );
        },
        'prevent_license_sharing' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_prevent_sharing', true, 'LICENSE_PREVENT_SHARING');
        },
        'detect_suspicious_activity' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_detect_suspicious', true, 'LICENSE_DETECT_SUSPICIOUS');
        },
        'block_vpn_verification' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_block_vpn', false, 'LICENSE_BLOCK_VPN');
        },
        'require_https' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_require_https',
                true,
                'LICENSE_REQUIRE_HTTPS',
            );
        },
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
        'notify_on_verification' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_notify_verification',
                false,
                'LICENSE_NOTIFY_VERIFICATION',
            );
        },
        'notify_on_expiration' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_notify_expiration',
                true,
                'LICENSE_NOTIFY_EXPIRATION',
            );
        },
        'notify_on_domain_change' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_notify_domain_change',
                true,
                'LICENSE_NOTIFY_DOMAIN_CHANGE',
            );
        },
        'notify_on_suspicious_activity' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_notify_suspicious',
                true,
                'LICENSE_NOTIFY_SUSPICIOUS',
            );
        },
        'notification_email' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_notification_email',
                '',
                'LICENSE_NOTIFICATION_EMAIL',
            );
        },
        'use_slack_notifications' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_use_slack',
                false,
                'LICENSE_USE_SLACK',
            );
        },
        'slack_webhook_url' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_slack_webhook',
                '',
                'LICENSE_SLACK_WEBHOOK',
            );
        },
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
        'enable_caching' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_enable_caching', true, 'LICENSE_ENABLE_CACHING');
        },
        'cache_driver' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_cache_driver', 'redis', 'LICENSE_CACHE_DRIVER');
        },
        'enable_query_optimization' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_optimize_queries',
                true,
                'LICENSE_OPTIMIZE_QUERIES',
            );
        },
        'batch_verification_size' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_batch_size', 100, 'LICENSE_BATCH_SIZE');
        },
        'use_database_indexes' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_use_indexes', true, 'LICENSE_USE_INDEXES');
        },
        'compress_api_responses' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_compress_responses',
                true,
                'LICENSE_COMPRESS_RESPONSES',
            );
        },
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
        'allow_test_licenses' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_allow_test', true, 'LICENSE_ALLOW_TEST');
        },
        'test_license_prefix' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_test_prefix', 'TEST-', 'LICENSE_TEST_PREFIX');
        },
        'bypass_verification_in_testing' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_bypass_testing',
                false,
                'LICENSE_BYPASS_TESTING',
            );
        },
        'mock_envato_responses' => function () {
            return App\Helpers\ConfigHelper::getSetting('license_mock_envato', false, 'LICENSE_MOCK_ENVATO');
        },
        'generate_fake_data' => function () {
            return App\Helpers\ConfigHelper::getSetting(
                'license_generate_fake_data',
                false,
                'LICENSE_GENERATE_FAKE_DATA',
            );
        },
    ],
];
