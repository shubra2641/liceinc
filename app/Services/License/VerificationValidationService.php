<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\LicenseVerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Verification Validation Service - Handles verification validation operations.
 */
class VerificationValidationService
{
    /**
     * Validate purchase code format.
     */
    public function validatePurchaseCode(string $purchaseCode): bool
    {
        if (empty($purchaseCode)) {
            return false;
        }

        if (strlen($purchaseCode) < 10) {
            return false;
        }

        if (strlen($purchaseCode) > 100) {
            return false;
        }

        // Check for valid characters (alphanumeric and hyphens)
        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $purchaseCode)) {
            return false;
        }

        return true;
    }

    /**
     * Validate domain format.
     */
    public function validateDomain(string $domain): bool
    {
        if (empty($domain)) {
            return false;
        }

        if (strlen($domain) > 255) {
            return false;
        }

        return filter_var($domain, FILTER_VALIDATE_DOMAIN) !== false;
    }

    /**
     * Validate verification source.
     */
    public function validateSource(string $source): bool
    {
        $allowedSources = ['install', 'api', 'admin'];
        return in_array($source, $allowedSources);
    }

    /**
     * Validate IP address.
     */
    public function validateIpAddress(string $ip): bool
    {
        if (empty($ip)) {
            return false;
        }

        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validate user agent.
     */
    public function validateUserAgent(?string $userAgent): bool
    {
        if (!$userAgent) {
            return true; // User agent is optional
        }

        if (strlen($userAgent) > 500) {
            return false;
        }

        return true;
    }

    /**
     * Validate verification message.
     */
    public function validateMessage(string $message): bool
    {
        if (empty($message)) {
            return false;
        }

        if (strlen($message) > 1000) {
            return false;
        }

        return true;
    }

    /**
     * Validate error details.
     */
    public function validateErrorDetails(?string $errorDetails): bool
    {
        if (!$errorDetails) {
            return true; // Error details are optional
        }

        if (strlen($errorDetails) > 2000) {
            return false;
        }

        return true;
    }

    /**
     * Validate response data.
     */
    public function validateResponseData(?array $responseData): bool
    {
        if (!$responseData) {
            return true; // Response data is optional
        }

        if (count($responseData) > 100) {
            return false;
        }

        // Check for nested arrays (limit depth)
        $depth = $this->getArrayDepth($responseData);
        if ($depth > 5) {
            return false;
        }

        return true;
    }

    /**
     * Validate verification log data.
     */
    public function validateVerificationLogData(array $data): array
    {
        $errors = [];

        if (!isset($data['purchase_code']) || !$this->validatePurchaseCode($data['purchase_code'])) {
            $errors['purchase_code'] = 'Invalid purchase code';
        }

        if (!isset($data['domain']) || !$this->validateDomain($data['domain'])) {
            $errors['domain'] = 'Invalid domain';
        }

        if (!isset($data['is_valid']) || !is_bool($data['is_valid'])) {
            $errors['is_valid'] = 'Invalid verification status';
        }

        if (!isset($data['message']) || !$this->validateMessage($data['message'])) {
            $errors['message'] = 'Invalid message';
        }

        if (isset($data['source']) && !$this->validateSource($data['source'])) {
            $errors['source'] = 'Invalid verification source';
        }

        if (isset($data['ip_address']) && !$this->validateIpAddress($data['ip_address'])) {
            $errors['ip_address'] = 'Invalid IP address';
        }

        if (isset($data['user_agent']) && !$this->validateUserAgent($data['user_agent'])) {
            $errors['user_agent'] = 'Invalid user agent';
        }

        if (isset($data['error_details']) && !$this->validateErrorDetails($data['error_details'])) {
            $errors['error_details'] = 'Invalid error details';
        }

        if (isset($data['response_data']) && !$this->validateResponseData($data['response_data'])) {
            $errors['response_data'] = 'Invalid response data';
        }

        return $errors;
    }

    /**
     * Check if verification is suspicious.
     */
    public function isSuspiciousVerification(
        string $purchaseCode,
        string $domain,
        string $ipAddress,
        ?string $userAgent = null
    ): bool {
        try {
            // Check for multiple failed attempts from same IP
            $failedAttempts = LicenseVerificationLog::where('ip_address', $ipAddress)
                ->where('is_valid', false)
                ->where('created_at', '>=', now()->subHours(1))
                ->count();

            if ($failedAttempts > 5) {
                return true;
            }

            // Check for multiple domains from same IP
            $domainCount = LicenseVerificationLog::where('ip_address', $ipAddress)
                ->where('created_at', '>=', now()->subHours(24))
                ->distinct('domain')
                ->count();

            if ($domainCount > 10) {
                return true;
            }

            // Check for unusual user agent
            if ($userAgent && $this->isUnusualUserAgent($userAgent)) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check suspicious verification', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'domain' => $domain,
                'ip_address' => $ipAddress,
            ]);
            return false;
        }
    }

    /**
     * Check if user agent is unusual.
     */
    private function isUnusualUserAgent(string $userAgent): bool
    {
        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scanner/i',
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/php/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get array depth.
     */
    private function getArrayDepth(array $array): int
    {
        $maxDepth = 0;
        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = $this->getArrayDepth($value) + 1;
                if ($depth > $maxDepth) {
                    $maxDepth = $depth;
                }
            }
        }
        return $maxDepth;
    }

    /**
     * Sanitize input data.
     */
    public function sanitizeInputData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInputData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize string.
     */
    private function sanitizeString(string $input): string
    {
        // Remove null bytes
        $input = str_replace("\0", '', $input);

        // Remove control characters
        $input = preg_replace('/[\x00-\x1F\x7F]/', '', $input);

        // Trim whitespace
        $input = trim($input);

        // Remove excessive whitespace
        $input = preg_replace('/\s+/', ' ', $input);

        return $input;
    }
}
