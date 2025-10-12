<?php

declare(strict_types=1);

namespace App\Services\Email\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Email Logging Trait.
 *
 * Provides common logging methods for email services.
 *
 * @version 1.0.0
 */
trait EmailLoggingTrait
{
    /**
     * Log email success.
     */
    protected function logEmailSuccess(string $template, string $recipient): void
    {
        Log::info('Email sent successfully', [
            'template' => $template,
            'recipient' => $recipient,
        ]);
    }

    /**
     * Log email error.
     */
    protected function logEmailError(string $template, string $recipient, string $message, ?\Throwable $exception = null): void
    {
        Log::error('Failed to send email: ' . $message, [
            'template' => $template,
            'recipient' => $recipient,
            'exception' => $exception?->getTraceAsString(),
        ]);
    }

    /**
     * Log template not found error.
     */
    protected function logTemplateNotFound(string $templateName): void
    {
        Log::error('Email template not found: ' . $templateName);
    }

    /**
     * Log admin email configuration error.
     */
    protected function logAdminEmailNotConfigured(): void
    {
        Log::error('Admin email not configured for email sending');
    }

    /**
     * Log invalid user error.
     */
    protected function logInvalidUser(string $context = 'email sending'): void
    {
        Log::error('Invalid user provided for ' . $context);
    }

    /**
     * Log bulk email error.
     */
    protected function logBulkEmailError(string $message, ?\Throwable $exception = null): void
    {
        Log::error('Failed to send bulk email: ' . $message, [
            'exception' => $exception?->getTraceAsString(),
        ]);
    }
}
