<?php

declare(strict_types=1);

namespace App\Services\Email\Contracts;

/**
 * Email Validator Interface.
 *
 * Defines the contract for email validation services.
 *
 * @version 1.0.0
 */
interface EmailValidatorInterface
{
    /**
     * Validate and sanitize template name.
     *
     * @throws \InvalidArgumentException When template name is invalid
     */
    public function validateTemplateName(string $templateName): string;

    /**
     * Validate and sanitize email address.
     *
     * @throws \InvalidArgumentException When email is invalid
     */
    public function validateEmail(string $email): string;

    /**
     * Validate and sanitize template type.
     *
     * @throws \InvalidArgumentException When template type is invalid
     */
    public function validateTemplateType(string $type): string;

    /**
     * Sanitize string input with XSS protection.
     */
    public function sanitizeString(?string $input): ?string;

    /**
     * Sanitize array data with XSS protection.
     *
     * @param array<mixed, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function sanitizeData(array $data): array;
}
