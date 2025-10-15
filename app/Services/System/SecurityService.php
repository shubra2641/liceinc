<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Security Service - Provides comprehensive security functionality.
 */
class SecurityService
{
    public function __construct(
        private InputSanitizationService $sanitizationService,
        private AttackDetectionService $attackDetectionService,
        private FileSecurityService $fileSecurityService,
        private SecurityValidationService $validationService
    ) {
    }

    /**
     * Validate and sanitize input data.
     */
    public function validateAndSanitizeInput(array $input): array
    {
        try {
            $sanitized = $this->sanitizationService->sanitizeInput($input);
            $validation = $this->validationService->validateInput($input);

            return [
                'sanitized' => $sanitized,
                'validation' => $validation,
            ];
        } catch (\Exception $e) {
            Log::error('Input validation and sanitization failed', [
                'error' => $e->getMessage(),
                'input' => $input,
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize string input.
     */
    public function sanitizeString(string $input): string
    {
        return $this->sanitizationService->sanitizeString($input);
    }

    /**
     * Sanitize HTML content.
     */
    public function sanitizeHtml(string $html): string
    {
        return $this->sanitizationService->sanitizeHtml($html);
    }

    /**
     * Sanitize email address.
     */
    public function sanitizeEmail(string $email): string
    {
        return $this->sanitizationService->sanitizeEmail($email);
    }

    /**
     * Sanitize URL.
     */
    public function sanitizeUrl(string $url): string
    {
        return $this->sanitizationService->sanitizeUrl($url);
    }

    /**
     * Sanitize phone number.
     */
    public function sanitizePhone(string $phone): string
    {
        return $this->sanitizationService->sanitizePhone($phone);
    }

    /**
     * Sanitize numeric input.
     */
    public function sanitizeNumeric(string $input): float
    {
        return $this->sanitizationService->sanitizeNumeric($input);
    }

    /**
     * Sanitize file name.
     */
    public function sanitizeFileName(string $fileName): string
    {
        return $this->sanitizationService->sanitizeFileName($fileName);
    }

    /**
     * Sanitize JSON input.
     */
    public function sanitizeJson(string $json): array
    {
        return $this->sanitizationService->sanitizeJson($json);
    }

    /**
     * Check if input contains XSS attempts.
     */
    public function containsXss(string $input): bool
    {
        return $this->sanitizationService->containsXss($input);
    }

    /**
     * Check if input contains SQL injection attempts.
     */
    public function containsSqlInjection(string $input): bool
    {
        return $this->sanitizationService->containsSqlInjection($input);
    }

    /**
     * Detect attacks in request.
     */
    public function detectAttacks(Request $request): array
    {
        return $this->attackDetectionService->detectAttacks($request);
    }

    /**
     * Check for brute force attacks.
     */
    public function detectBruteForce(string $identifier, string $type = 'login'): bool
    {
        return $this->attackDetectionService->detectBruteForce($identifier, $type);
    }

    /**
     * Check for DDoS attacks.
     */
    public function detectDdos(string $ip): bool
    {
        return $this->attackDetectionService->detectDdos($ip);
    }

    /**
     * Check for suspicious user agents.
     */
    public function detectSuspiciousUserAgent(string $userAgent): bool
    {
        return $this->attackDetectionService->detectSuspiciousUserAgent($userAgent);
    }

    /**
     * Check for suspicious IP addresses.
     */
    public function detectSuspiciousIp(string $ip): bool
    {
        return $this->attackDetectionService->detectSuspiciousIp($ip);
    }

    /**
     * Get attack statistics.
     */
    public function getAttackStatistics(): array
    {
        return $this->attackDetectionService->getAttackStatistics();
    }

    /**
     * Validate file upload.
     */
    public function validateFileUpload($file, string $type = 'image', int $maxSize = null): array
    {
        return $this->fileSecurityService->validateFileUpload($file, $type, $maxSize);
    }

    /**
     * Scan file for malware.
     */
    public function scanFileForMalware($file): array
    {
        return $this->fileSecurityService->scanFileForMalware($file);
    }

    /**
     * Sanitize file name.
     */
    public function sanitizeFileName($fileName): string
    {
        return $this->fileSecurityService->sanitizeFileName($fileName);
    }

    /**
     * Generate secure file name.
     */
    public function generateSecureFileName(string $originalName, string $type = 'file'): string
    {
        return $this->fileSecurityService->generateSecureFileName($originalName, $type);
    }

    /**
     * Store file securely.
     */
    public function storeFileSecurely($file, string $path = 'uploads', string $disk = 'local'): array
    {
        return $this->fileSecurityService->storeFileSecurely($file, $path, $disk);
    }

    /**
     * Validate input for security threats.
     */
    public function validateInput(array $input): array
    {
        return $this->validationService->validateInput($input);
    }

    /**
     * Validate file upload.
     */
    public function validateFileUploadSecurity($file, array $allowedTypes = [], int $maxSize = 5242880): array
    {
        return $this->validationService->validateFileUpload($file, $allowedTypes, $maxSize);
    }

    /**
     * Validate API request.
     */
    public function validateApiRequest(Request $request): array
    {
        return $this->validationService->validateApiRequest($request);
    }

    /**
     * Validate user input.
     */
    public function validateUserInput(array $input, array $rules = []): array
    {
        return $this->validationService->validateUserInput($input, $rules);
    }

    /**
     * Comprehensive security check for request.
     */
    public function performSecurityCheck(Request $request): array
    {
        try {
            $results = [
                'attack_detection' => $this->detectAttacks($request),
                'brute_force' => $this->detectBruteForce($request->ip(), 'api'),
                'ddos' => $this->detectDdos($request->ip()),
                'suspicious_user_agent' => $this->detectSuspiciousUserAgent($request->userAgent() ?? ''),
                'suspicious_ip' => $this->detectSuspiciousIp($request->ip()),
                'api_validation' => $this->validateApiRequest($request),
            ];

            $isSecure = !$results['attack_detection']['is_attack'] &&
                       !$results['brute_force'] &&
                       !$results['ddos'] &&
                       !$results['suspicious_user_agent'] &&
                       !$results['suspicious_ip'] &&
                       $results['api_validation']['valid'];

            if (!$isSecure) {
                Log::warning('Security check failed', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'results' => $results,
                ]);
            }

            return [
                'secure' => $isSecure,
                'results' => $results,
                'recommendations' => $this->getSecurityRecommendations($results),
            ];
        } catch (\Exception $e) {
            Log::error('Security check failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return [
                'secure' => false,
                'results' => [],
                'recommendations' => ['Investigate security check failure'],
            ];
        }
    }

    /**
     * Get security recommendations.
     */
    private function getSecurityRecommendations(array $results): array
    {
        $recommendations = [];

        if ($results['attack_detection']['is_attack']) {
            $recommendations[] = 'Attack detected - review and block suspicious activity';
        }

        if ($results['brute_force']) {
            $recommendations[] = 'Brute force attack detected - implement rate limiting';
        }

        if ($results['ddos']) {
            $recommendations[] = 'DDoS attack detected - implement traffic filtering';
        }

        if ($results['suspicious_user_agent']) {
            $recommendations[] = 'Suspicious user agent detected - investigate further';
        }

        if ($results['suspicious_ip']) {
            $recommendations[] = 'Suspicious IP address detected - consider blocking';
        }

        if (!$results['api_validation']['valid']) {
            $recommendations[] = 'API validation failed - review request format';
        }

        return $recommendations;
    }
}
