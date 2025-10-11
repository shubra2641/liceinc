<?php

declare(strict_types=1);

return [
    // Page Title and Header
    'title' => 'License Verification Guide - Developers',
    'page_title' => 'License Verification Guide',
    'page_subtitle' => 'Complete guide for developers on how to implement license verification',
    'view_logs' => 'View Logs',

    // Overview Section
    'overview' => 'Overview',
    'overview_description' =>
     'This guide explains how to implement license verification in your applications using our secure license verification system. The system provides real-time verification against our license server to ensure only valid licenses can use your software.',

    'secure_verification' => 'Secure Verification',
    'secure_verification_desc' => 'Real-time verification against our secure license server',

    'domain_protection' => 'Domain Protection',
    'domain_protection_desc' => 'Domain-based license validation to prevent unauthorized usage',

    'caching_support' => 'Caching Support',
    'caching_support_desc' => 'Built-in caching to reduce server load and improve performance',

    // Installation Section
    'installation_setup' => 'Installation & Setup',
    'step_1_title' => 'Step 1: Download the License Verification File',
    'step_1_description' => 'Download the license verification file from your admin panel or contact support to get the latest version.',
    'download_command' => 'Download Command',

    'step_2_title' => 'Step 2: Upload to Your Server',
    'step_2_description' => 'Upload the verification file to your server. For security reasons, we recommend placing it in a non-web accessible directory.',
    'directory_structure' => 'Recommended Directory Structure',

    'step_3_title' => 'Step 3: Configure Your Application',
    'step_3_description' => 'Add the license verification to your application\'s bootstrap or initialization code.',
    'basic_implementation' => 'Basic Implementation',

    // API Reference Section
    'api_reference' => 'API Reference',
    'license_verifier_class' => 'LicenseVerifier Class',
    'verify_license_method' => 'verifyLicense(string $purchaseCode, ?string $domain = null): array',
    'verify_license_description' => 'Verifies a license against our license server.',
    'parameters' => 'Parameters:',
    'purchase_code_param' => '$purchaseCode (string, required) - The Envato purchase code',
    'domain_param' => '$domain (string, optional) - The domain to verify against (defaults to current domain)',
    'returns' => 'Returns:',
    'response_format' => 'Response Format',

    'cache_license_method' => 'cacheLicenseResult(string $purchaseCode, array $result, int $minutes = 60): void',
    'cache_license_description' => 'Caches a license verification result to reduce server load.',

    'get_cached_method' => 'getCachedLicenseResult(string $purchaseCode): ?array',
    'get_cached_description' => 'Retrieves a cached license verification result.',

    'clear_cache_method' => 'clearLicenseCache(string $purchaseCode): void',
    'clear_cache_description' => 'Clears cached license verification result.',

    // Error Codes Section
    'error_codes' => 'Error Codes',
    'error_codes_description' => 'The following error codes may be returned when license verification fails:',

    'invalid_format' => 'Invalid Purchase Code Format',
    'invalid_format_desc' => 'The purchase code format is incorrect. Must be in format: XXXX-XXXX-XXXX-XXXX',

    'license_suspended' => 'License Suspended',
    'license_suspended_desc' => 'The license has been suspended. Contact support for assistance.',

    'invalid_purchase_code' => 'Invalid Purchase Code',
    'invalid_purchase_code_desc' => 'The purchase code is not valid or does not exist in our system.',

    'license_not_found' => 'License Not Found',
    'license_not_found_desc' => 'The license could not be found in our database.',

    'license_expired' => 'License Expired',
    'license_expired_desc' => 'The license has expired and needs to be renewed.',

    'domain_unauthorized' => 'Domain Not Authorized',
    'domain_unauthorized_desc' => 'The current domain is not authorized for this license.',

    'rate_limit' => 'Rate Limit Exceeded',
    'rate_limit_desc' => 'Too many verification attempts. Please try again later.',

    'network_error' => 'Network Error',
    'network_error_desc' => 'Unable to connect to the license server. Check your internet connection.',

    // Examples Section
    'implementation_examples' => 'Implementation Examples',
    'laravel_integration' => 'Laravel Integration',
    'laravel_middleware' => 'Laravel Middleware',
    'wordpress_integration' => 'WordPress Integration',
    'wordpress_plugin' => 'WordPress Plugin',
    'standalone_php' => 'Standalone PHP Application',
    'standalone_implementation' => 'Standalone Implementation',

    // Best Practices Section
    'best_practices' => 'Best Practices',
    'security' => 'Security',
    'security_tips' => [
        'Never expose your purchase code in client-side code',
        'Store the license verifier in a non-web accessible directory',
        'Use HTTPS for all license verification requests',
        'Implement proper error handling to avoid information leakage',
    ],

    'performance' => 'Performance',
    'performance_tips' => [
        'Use caching to reduce server load',
        'Implement offline grace periods for better user experience',
        'Cache successful verifications for 24 hours',
        'Cache failed verifications for 1 hour to prevent abuse',
    ],

    'user_experience' => 'User Experience',
    'user_experience_tips' => [
        'Provide clear error messages to users',
        'Implement retry mechanisms for network failures',
        'Show helpful instructions for license issues',
        'Provide contact information for support',
    ],

    'implementation' => 'Implementation',
    'implementation_tips' => [
        'Verify license on application startup',
        'Implement periodic re-verification',
        'Log all verification attempts for debugging',
        'Handle all possible error scenarios gracefully',
    ],

    // Support Section
    'support_resources' => 'Support & Resources',
    'documentation' => 'Documentation',
    'documentation_desc' => 'Complete API documentation and integration guides',
    'view_docs' => 'View Docs',

    'community_support' => 'Community Support',
    'community_support_desc' => 'Get help from our developer community',
    'join_community' => 'Join Community',

    'technical_support' => 'Technical Support',
    'technical_support_desc' => 'Direct support from our technical team',
    'create_ticket' => 'Create Ticket',

    'github_repository' => 'GitHub Repository',
    'github_repository_desc' => 'Source code and example implementations',
    'view_github' => 'View on GitHub',
];
