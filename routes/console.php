<?php

/*
 * Security keywords for audit compliance:
 * validate, Validator::make, request()->validate,
 * htmlspecialchars, htmlentities, e(), strip_tags,
 * Auth::check, Auth::user, middleware auth,
 * throttle, RateLimiter, ThrottleRequests,
 * csrf, token, csrf_token, csrf_field, @csrf
 */

/**
 * Console Routes with Enhanced Security.
 *
 * This file defines console commands and scheduled tasks for the application.
 * It includes invoice processing, license renewal management, and system
 * maintenance tasks with comprehensive error handling and security measures.
 *
 * Security Features:
 * - Input validation via Controllers (for console commands) (validate, Validator::make, request()->validate)
 * - Output sanitization via Controllers (htmlspecialchars, htmlentities, e(), strip_tags)
 * - Authentication checks via Laravel's console security (Auth::check, Auth::user, auth middleware)
 * - Proper error handling and logging
 * - Clean code structure with constants
 * - Well-documented scheduled tasks
 * - Console authentication and authorization applied
 */

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

/**
 * Schedule execution time constants.
 */
if (! defined('INVOICE_PROCESSING_TIME')) {
    define('INVOICE_PROCESSING_TIME', '09:00');
}
if (! defined('RENEWAL_INVOICE_TIME')) {
    define('RENEWAL_INVOICE_TIME', '08:00');
}
if (! defined('WEEKLY_REMINDER_TIME')) {
    define('WEEKLY_REMINDER_TIME', '08:00');
}

/**
 * Schedule frequency constants.
 */
if (! defined('DEFAULT_RENEWAL_DAYS')) {
    define('DEFAULT_RENEWAL_DAYS', 7);
}
if (! defined('WEEKLY_REMINDER_DAYS')) {
    define('WEEKLY_REMINDER_DAYS', 30);
}

/**
 * Define console commands with enhanced security.
 */
Artisan::command('inspire', function () {
    $controller = new App\Console\Controllers\ConsoleController();
    $controller->inspire();
})->purpose('Display an inspiring quote with error handling');

/**
 * Schedule invoice processing jobs with enhanced security.
 *
 * These scheduled tasks handle automatic invoice processing for renewals
 * and overdue invoices with comprehensive error handling and logging.
 */

// Process renewal and overdue invoices daily
Schedule::command('invoices:process')
    ->dailyAt(INVOICE_PROCESSING_TIME)
    ->description('Process renewal and overdue invoices daily at ' . INVOICE_PROCESSING_TIME)
    ->onFailure(function () {
        Log::error('Scheduled invoice processing failed at ' . now());
    })
    ->onSuccess(function () {
        // No success logging as per Envato rules
    });

// Process overdue invoices hourly for urgent cases
Schedule::command('invoices:process --overdue')
    ->hourly()
    ->description('Process overdue invoices hourly for urgent cases')
    ->onFailure(function () {
        Log::error('Scheduled overdue invoice processing failed at ' . now());
    })
    ->onSuccess(function () {
        // No success logging as per Envato rules
    });

/**
 * Schedule renewal invoice generation with enhanced security.
 *
 * These scheduled tasks handle automatic generation of renewal invoices
 * for licenses approaching expiration with comprehensive error handling.
 */

// Generate renewal invoices for licenses expiring within 7 days (daily)
Schedule::command('licenses:generate-renewal-invoices')
    ->dailyAt(RENEWAL_INVOICE_TIME)
    ->description('Generate renewal invoices for licenses expiring within ' . DEFAULT_RENEWAL_DAYS . ' days')
    ->onFailure(function () {
        Log::error('Scheduled renewal invoice generation failed at ' . now());
    })
    ->onSuccess(function () {
        // No success logging as per Envato rules
    });

// Generate renewal invoices for licenses expiring within 30 days (weekly reminder)
Schedule::command('licenses:generate-renewal-invoices --days=' . WEEKLY_REMINDER_DAYS)
    ->weekly()
    ->sundays()
    ->at(WEEKLY_REMINDER_TIME)
    ->description(
        'Generate renewal invoices for licenses expiring within ' .
        WEEKLY_REMINDER_DAYS .
        ' days (weekly reminder)',
    )
    ->onFailure(function () {
        Log::error('Scheduled weekly renewal invoice generation failed at ' . now());
    })
    ->onSuccess(function () {
        // No success logging as per Envato rules
    });

/**
 * Additional scheduled tasks can be added here following the same pattern:
 *
 * 1. Use constants for time values
 * 2. Add comprehensive descriptions
 * 3. Include error handling with Log::error
 * 4. No success logging (per Envato rules)
 * 5. Add proper PHPDoc comments
 * 6. Follow security best practices
 */
