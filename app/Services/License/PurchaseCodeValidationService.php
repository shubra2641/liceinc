<?php

declare(strict_types=1);

namespace App\Services\License;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Purchase Code Validation Service - Handles purchase code validation.
 */
class PurchaseCodeValidationService
{
    /**
     * Clean and validate purchase code format.
     */
    public function cleanPurchaseCode(string $purchaseCode): string
    {
        try {
            if (empty($purchaseCode)) {
                throw new InvalidArgumentException('Purchase code cannot be empty');
            }

            $sanitized = $this->sanitizeInput($purchaseCode);
            $cleaned = strtoupper(str_replace([' ', '-', '_'], '', trim($sanitized)));

            if (strlen($cleaned) < 8 || strlen($cleaned) > 50) {
                throw new InvalidArgumentException('Purchase code length is invalid');
            }

            return $cleaned;
        } catch (\Exception $e) {
            Log::error('Error cleaning purchase code', [
                'purchase_code_length' => strlen($purchaseCode),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate purchase code format.
     */
    public function isValidFormat(string $purchaseCode): bool
    {
        try {
            if (empty($purchaseCode)) {
                return false;
            }

            $cleaned = $this->cleanPurchaseCode($purchaseCode);
            return (bool) preg_match('/^[A-Z0-9]{8,50}$/', $cleaned);
        } catch (\Exception $e) {
            Log::error('Error validating purchase code format', [
                'purchase_code_length' => strlen($purchaseCode),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Validate purchase code for verification.
     */
    public function validateForVerification(string $purchaseCode): array
    {
        try {
            if (empty($purchaseCode)) {
                return [
                    'valid' => false,
                    'error' => 'Purchase code cannot be empty'
                ];
            }

            $cleanedCode = $this->cleanPurchaseCode($purchaseCode);

            if (!$this->isValidFormat($cleanedCode)) {
                return [
                    'valid' => false,
                    'error' => 'Invalid purchase code format'
                ];
            }

            return [
                'valid' => true,
                'cleaned_code' => $cleanedCode
            ];
        } catch (\Exception $e) {
            Log::error('Error validating purchase code for verification', [
                'purchase_code_length' => strlen($purchaseCode),
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => 'Validation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sanitize input data.
     */
    public function sanitizeInput(?string $input): string
    {
        if ($input === null) {
            return '';
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate purchase code length.
     */
    public function validateLength(string $purchaseCode): bool
    {
        $length = strlen($purchaseCode);
        return $length >= 8 && $length <= 50;
    }

    /**
     * Validate purchase code characters.
     */
    public function validateCharacters(string $purchaseCode): bool
    {
        return (bool) preg_match('/^[A-Z0-9]+$/', $purchaseCode);
    }

    /**
     * Check for suspicious patterns.
     */
    public function checkSuspiciousPatterns(string $purchaseCode): array
    {
        $suspiciousPatterns = [
            '/^[0-9]+$/' => 'Only numbers',
            '/^[A-Z]+$/' => 'Only letters',
            '/^(.)\1+$/' => 'Repeated characters',
            '/^[A-Z]{1,3}$/' => 'Too short',
        ];

        foreach ($suspiciousPatterns as $pattern => $description) {
            if (preg_match($pattern, $purchaseCode)) {
                return [
                    'suspicious' => true,
                    'pattern' => $description
                ];
            }
        }

        return [
            'suspicious' => false,
            'pattern' => null
        ];
    }
}
