<?php

declare(strict_types=1);

namespace App\Services\Envato;

use InvalidArgumentException;

/**
 * Envato Validation Service - Handles validation for Envato operations.
 */
class EnvatoValidationService
{
    /**
     * Validate and sanitize purchase code.
     */
    public function validatePurchaseCode(string $purchaseCode): string
    {
        if (empty($purchaseCode)) {
            throw new InvalidArgumentException('Purchase code cannot be empty');
        }

        $sanitized = htmlspecialchars(trim($purchaseCode), ENT_QUOTES, 'UTF-8');

        if (empty($sanitized) || strlen($sanitized) < 10) {
            throw new InvalidArgumentException('Purchase code must be at least 10 characters long');
        }

        return $sanitized;
    }

    /**
     * Validate and sanitize username.
     */
    public function validateUsername(string $username): string
    {
        if (empty($username)) {
            throw new InvalidArgumentException('Username cannot be empty');
        }

        $sanitized = htmlspecialchars(trim($username), ENT_QUOTES, 'UTF-8');

        if (empty($sanitized) || strlen($sanitized) < 2) {
            throw new InvalidArgumentException('Username must be at least 2 characters long');
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $sanitized)) {
            throw new InvalidArgumentException('Username contains invalid characters');
        }

        return $sanitized;
    }

    /**
     * Validate access token.
     */
    public function validateAccessToken(string $token): string
    {
        if (empty($token)) {
            throw new InvalidArgumentException('Access token cannot be empty');
        }

        $sanitized = trim($token);

        if (empty($sanitized) || strlen($sanitized) < 20) {
            throw new InvalidArgumentException('Access token must be at least 20 characters long');
        }

        return $sanitized;
    }

    /**
     * Validate item ID.
     */
    public function validateItemId(int $itemId): int
    {
        if ($itemId <= 0) {
            throw new InvalidArgumentException('Item ID must be a positive integer');
        }

        return $itemId;
    }

    /**
     * Sanitize string input.
     */
    public function sanitizeString(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate settings array.
     */
    public function validateSettings(array $settings): array
    {
        $requiredKeys = ['token'];
        $missingKeys = [];

        foreach ($requiredKeys as $key) {
            if (!isset($settings[$key]) || empty($settings[$key])) {
                $missingKeys[] = $key;
            }
        }

        if (!empty($missingKeys)) {
            throw new InvalidArgumentException('Missing required settings: ' . implode(', ', $missingKeys));
        }

        return $settings;
    }
}
