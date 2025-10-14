<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

/**
 * Product Update Latest Version Request with enhanced security.
 *
 * This request class handles validation for latest version operations
 * with comprehensive security measures and input sanitization.
 */
class ProductUpdateLatestVersionRequest extends BaseProductUpdateRequest
{
    // All validation logic is inherited from BaseProductUpdateRequest

    /**
     * Sanitize input to prevent XSS attacks.
     */
    private function sanitizeInput(mixed $input): ?string
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
