<?php

declare(strict_types=1);

namespace App\Services\Email\Validators;

use App\Services\Email\Contracts\EmailValidatorInterface;

/**
 * Email Validator with enhanced security.
 *
 * Handles validation and sanitization of email-related data
 * with comprehensive security measures.
 *
 * @version 1.0.0
 */
class EmailValidator implements EmailValidatorInterface
{
    /**
     * Validate and sanitize template name.
     *
     * @throws \InvalidArgumentException When template name is invalid
     */
    public function validateTemplateName(string $templateName): string
    {
        if (empty($templateName)) {
            throw new \InvalidArgumentException('Template name cannot be empty');
        }

        $sanitized = htmlspecialchars(trim($templateName), ENT_QUOTES, 'UTF-8');
        if (empty($sanitized)) {
            throw new \InvalidArgumentException('Template name contains invalid characters');
        }

        return $sanitized;
    }

    /**
     * Validate and sanitize email address.
     *
     * @throws \InvalidArgumentException When email is invalid
     */
    public function validateEmail(string $email): string
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('Email address cannot be empty');
        }

        $sanitized = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if ($sanitized === false || ! filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address format');
        }

        return $sanitized;
    }

    /**
     * Validate and sanitize template type.
     *
     * @throws \InvalidArgumentException When template type is invalid
     */
    public function validateTemplateType(string $type): string
    {
        $allowedTypes = ['user', 'admin'];
        $sanitized = htmlspecialchars(trim($type), ENT_QUOTES, 'UTF-8');

        if (! in_array($sanitized, $allowedTypes, true)) {
            throw new \InvalidArgumentException(
                'Invalid template type. Allowed values: ' . implode(', ', $allowedTypes),
            );
        }

        return $sanitized;
    }

    /**
     * Sanitize string input with XSS protection.
     */
    public function sanitizeString(mixed $input): ?string
    {
        if ($input === null) {
            return null;
        }

        if (! is_string($input) && ! is_scalar($input)) {
            return null;
        }

        $stringValue = is_string($input) ? $input : (string)$input;

        return htmlspecialchars(trim($stringValue), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize array data with XSS protection.
     *
     * @param array<mixed, mixed> $data
     *
     * @return array<string, mixed>
     */
    /**
     * @return array<string, mixed>
     */
    public function sanitizeData(array $data): array
    {
        /**
         * @var array<string, mixed> $sanitized
         */
        $sanitized = [];
        foreach ($data as $key => $value) {
            $stringKey = is_string($key) ? $key : (string)$key;
            if (is_array($value)) {
                $sanitized[$stringKey] = $this->sanitizeData($value);
            } elseif (is_string($value)) {
                $sanitized[$stringKey] = $this->sanitizeString($value) ?? '';
            } else {
                $sanitized[$stringKey] = is_scalar($value) ? (string)$value : '';
            }
        }

        return $sanitized;
    }
}
