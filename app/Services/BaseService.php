<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

/**
 * Base Service Class with common functionality.
 *
 * This abstract base class provides common functionality for all services
 * including database transactions, error handling, and logging capabilities.
 *
 * Features:
 * - Database transaction management
 * - Centralized error handling and logging
 * - Input validation helpers
 * - Security validation methods
 * - Clean code structure with no duplicate patterns
 * - Proper type hints and return types
 */
abstract class BaseService
{
    /**
     * Execute a callback within a database transaction.
     *
     * Provides safe database transaction handling with automatic rollback
     * on exceptions and comprehensive error logging.
     *
     * @param callable $callback The callback to execute within transaction
     *
     * @throws \Exception When the callback execution fails
     *
     * @return mixed The result of the callback execution
     *
     * @example
     * $result = $this->executeInTransaction(function() {
     *     return $this->createUser($userData);
     * });
     */
    protected function executeInTransaction(callable $callback): mixed
    {
        DB::beginTransaction();

        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Throwable $e) {
            DB::rollback();
            $this->logError('Transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'service' => static::class,
            ]);
            throw $e;
        }
    }

    /**
     * Log an error with context information.
     *
     * Provides centralized error logging with consistent format
     * and comprehensive context information.
     *
     * @param string $message The error message
     * @param array $context Additional context data
     *
     * @example
     * $this->logError('User creation failed', [
     *     'user_id' => $userId,
     *     'email' => $email
     * ]);
     */
    /**
     * @param array<string, mixed> $context
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, array_merge($context, [
            'service' => static::class,
            'timestamp' => now()->toISOString(),
        ]));
    }

    /**
     * Log an info message with context information.
     *
     * Provides centralized info logging with consistent format
     * and comprehensive context information.
     *
     * @param string $message The info message
     * @param array $context Additional context data
     *
     * @example
     * $this->logInfo('User created successfully', [
     *     'user_id' => $userId,
     *     'email' => $email
     * ]);
     */
    /**
     * @param array<string, mixed> $context
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, array_merge($context, [
            'service' => static::class,
            'timestamp' => now()->toISOString(),
        ]));
    }

    /**
     * Validate that a value is not empty.
     *
     * Provides input validation with clear error messages
     * and consistent validation logic.
     *
     * @param mixed $value The value to validate
     * @param string $fieldName The name of the field for error message
     *
     * @throws InvalidArgumentException When the value is empty
     *
     * @example
     * $this->validateNotEmpty($email, 'Email');
     */
    protected function validateNotEmpty(mixed $value, string $fieldName): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException("{$fieldName} cannot be empty");
        }
    }

    /**
     * Validate that a value is a positive integer.
     *
     * Provides numeric validation with clear error messages
     * and consistent validation logic.
     *
     * @param mixed $value The value to validate
     * @param string $fieldName The name of the field for error message
     *
     * @throws InvalidArgumentException When the value is not a positive integer
     *
     * @example
     * $this->validatePositiveInteger($userId, 'User ID');
     */
    protected function validatePositiveInteger(mixed $value, string $fieldName): void
    {
        if (!is_numeric($value) || (int)$value <= 0) {
            throw new InvalidArgumentException("{$fieldName} must be a positive integer");
        }
    }

    /**
     * Validate that a value is a valid email address.
     *
     * Provides email validation with clear error messages
     * and consistent validation logic.
     *
     * @param mixed $value The value to validate
     * @param string $fieldName The name of the field for error message
     *
     * @throws InvalidArgumentException When the value is not a valid email
     *
     * @example
     * $this->validateEmail($email, 'Email');
     */
    protected function validateEmail(mixed $value, string $fieldName): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("{$fieldName} must be a valid email address");
        }
    }

    /**
     * Sanitize string input to prevent XSS attacks.
     *
     * Provides input sanitization with comprehensive security measures
     * and consistent sanitization logic.
     *
     * @param string|null $input The input string to sanitize
     *
     * @return string The sanitized input string
     *
     * @example
     * $cleanInput = $this->sanitizeInput($userInput);
     */
    protected function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }

        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Trim whitespace
        $input = trim($input);

        // Remove HTML tags
        $input = strip_tags($input);

        // Escape special characters
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

        return $input;
    }

    /**
     * Generate a secure random token.
     *
     * Provides secure token generation with configurable length
     * and consistent token format.
     *
     * @param int $length The length of the token (default: 32)
     *
     * @return string The generated secure token
     *
     * @example
     * $token = $this->generateSecureToken(64);
     */
    protected function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes(max(1, (int) ($length / 2))));
    }

    /**
     * Check if a string contains only alphanumeric characters.
     *
     * Provides alphanumeric validation with clear error messages
     * and consistent validation logic.
     *
     * @param string $value The value to validate
     * @param string $fieldName The name of the field for error message
     *
     * @throws InvalidArgumentException When the value contains non-alphanumeric characters
     *
     * @example
     * $this->validateAlphanumeric($code, 'Purchase Code');
     */
    protected function validateAlphanumeric(string $value, string $fieldName): void
    {
        if (!ctype_alnum($value)) {
            throw new InvalidArgumentException("{$fieldName} must contain only alphanumeric characters");
        }
    }
}
