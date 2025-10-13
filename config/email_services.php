<?php

/**
 * Email Services Configuration.
 *
 * Configuration for the email services system.
 *
 * @version 1.0.0
 */

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Email Service
    |--------------------------------------------------------------------------
    |
    | This option controls the default email service that will be used
    | when no specific service is requested.
    |
    */
    'default' => env('EMAIL_SERVICE_DEFAULT', 'core'),

    /*
    |--------------------------------------------------------------------------
    | Email Services
    |--------------------------------------------------------------------------
    |
    | Here you may configure the email services available in your application.
    | You may add additional services as needed.
    |
    */
    'services' => [
        'core' => [
            'class' => \App\Services\Email\CoreEmailService::class,
            'validator' => \App\Services\Email\Validators\EmailValidator::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Handlers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the specialized email handlers.
    |
    */
    'handlers' => [
        'user' => \App\Services\Email\Handlers\UserEmailHandler::class,
        'license' => \App\Services\Email\Handlers\LicenseEmailHandler::class,
        'invoice' => \App\Services\Email\Handlers\InvoiceEmailHandler::class,
        'ticket' => \App\Services\Email\Handlers\TicketEmailHandler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Validation
    |--------------------------------------------------------------------------
    |
    | Here you may configure email validation settings.
    |
    */
    'validation' => [
        'allowed_template_types' => ['user', 'admin'],
        'max_template_name_length' => 255,
        'max_email_length' => 255,
        'sanitize_inputs' => true,
        'validate_emails' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Logging
    |--------------------------------------------------------------------------
    |
    | Here you may configure email logging settings.
    |
    */
    'logging' => [
        'log_success' => env('EMAIL_LOG_SUCCESS', false),
        'log_errors' => env('EMAIL_LOG_ERRORS', true),
        'log_template_not_found' => env('EMAIL_LOG_TEMPLATE_NOT_FOUND', true),
        'log_admin_errors' => env('EMAIL_LOG_ADMIN_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Security
    |--------------------------------------------------------------------------
    |
    | Here you may configure email security settings.
    |
    */
    'security' => [
        'sanitize_outputs' => true,
        'prevent_xss' => true,
        'validate_inputs' => true,
        'rate_limit' => [
            'enabled' => env('EMAIL_RATE_LIMIT_ENABLED', true),
            'max_emails_per_minute' => env('EMAIL_RATE_LIMIT_MAX', 100),
        ],
    ],
];
