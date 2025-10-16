<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Invoice Logging Helper
 * 
 * Handles all logging operations for InvoiceService.
 */
class InvoiceLoggingHelper
{
    /**
     * Log error with context.
     */
    public function logError(string $message, \Exception $exception, array $context = []): void
    {
        Log::error($message, array_merge([
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ], $context));
    }

    /**
     * Log info message.
     */
    public function logInfo(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }
}
