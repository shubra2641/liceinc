<?php

declare(strict_types=1);

namespace App\Services\Email\Traits;

use App\Services\Email\Contracts\EmailValidatorInterface;

/**
 * Email Validation Trait.
 *
 * Provides common validation methods for email services.
 *
 * @version 1.0.0
 */
trait EmailValidationTrait
{
    protected EmailValidatorInterface $validator;

    /**
     * Initialize validator if not set.
     */
    protected function initializeValidator(): void
    {
        if (!isset($this->validator)) {
            $this->validator = app(EmailValidatorInterface::class);
        }
    }

    /**
     * Validate template name using validator.
     */
    protected function validateTemplateName(string $templateName): string
    {
        $this->initializeValidator();
        return $this->validator->validateTemplateName($templateName);
    }

    /**
     * Validate email using validator.
     */
    protected function validateEmail(string $email): string
    {
        $this->initializeValidator();
        return $this->validator->validateEmail($email);
    }

    /**
     * Validate template type using validator.
     */
    protected function validateTemplateType(string $type): string
    {
        $this->initializeValidator();
        return $this->validator->validateTemplateType($type);
    }

    /**
     * Sanitize string using validator.
     */
    protected function sanitizeString(?string $input): ?string
    {
        $this->initializeValidator();
        return $this->validator->sanitizeString($input);
    }

    /**
     * Sanitize data using validator.
     *
     * @param array<mixed, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function sanitizeData(array $data): array
    {
        $this->initializeValidator();
        return $this->validator->sanitizeData($data);
    }
}
