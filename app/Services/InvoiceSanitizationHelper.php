<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Invoice Sanitization Helper
 * 
 * Handles all sanitization logic for InvoiceService.
 */
class InvoiceSanitizationHelper
{
    /**
     * Sanitize amount for security.
     */
    public function sanitizeAmount(mixed $amount): float
    {
        if (!is_numeric($amount)) {
            return 0.0;
        }
        return max(0, round((float)$amount, 2));
    }

    /**
     * Sanitize status for security.
     */
    public function sanitizeStatus(string $status): string
    {
        $validStatuses = ['paid', 'pending', 'overdue', 'cancelled'];
        return in_array($status, $validStatuses) ? $status : 'pending';
    }

    /**
     * Sanitize currency for security.
     */
    public function sanitizeCurrency(string $currency): string
    {
        return strtoupper(trim($currency));
    }

    /**
     * Sanitize input to prevent XSS attacks.
     */
    public function sanitizeInput(mixed $input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (!is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
