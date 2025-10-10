<?php

declare(strict_types=1);

namespace App\Services\Email;

use InvalidArgumentException;

/**
 * Email validation and sanitization service.
 */
class EmailValidator
{
    /**
     * Validate and sanitize template name.
     */
    public function validateTemplateName(string $templateName): string
    {
        if (empty($templateName)) {
            throw new InvalidArgumentException('Template name cannot be empty');
        }

        // Remove any potentially dangerous characters
        $templateName = preg_replace('/[^a-zA-Z0-9_-]/', '', $templateName);

        if (empty($templateName)) {
            throw new InvalidArgumentException('Invalid template name');
        }

        return $templateName;
    }

    /**
     * Validate email address.
     */
    public function validateEmail(string $email): string
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }

        return $email;
    }

    /**
     * Sanitize string input.
     */
    public function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize data array.
     */
    public function sanitizeData(array<string, mixed> $data): array<string, mixed>
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
