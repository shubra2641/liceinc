<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\LicenseVerificationLog;
use Illuminate\Http\Request;

/**
 * License Verification Logger - Provides comprehensive license verification logging.
 */
class LicenseVerificationLogger
{
    public function __construct(
        private VerificationLoggingService $loggingService,
        private VerificationAnalyticsService $analyticsService,
        private VerificationValidationService $validationService
    ) {
    }

    /**
     * Log license verification attempt.
     */
    public static function log(
        string $purchaseCode,
        string $domain,
        bool $isValid,
        string $message,
        ?array $responseData = null,
        string $source = 'install',
        ?Request $request = null,
        ?string $errorDetails = null,
    ): LicenseVerificationLog {
        $logger = app(self::class);
        return $logger->loggingService->logVerification(
            $purchaseCode,
            $domain,
            $isValid,
            $message,
            $responseData,
            $source,
            $request,
            $errorDetails
        );
    }

    /**
     * Log successful verification.
     */
    public static function logSuccessful(
        string $purchaseCode,
        string $domain,
        string $message,
        ?array $responseData = null,
        ?Request $request = null,
    ): LicenseVerificationLog {
        $logger = app(self::class);
        return $logger->loggingService->logSuccessfulVerification(
            $purchaseCode,
            $domain,
            $message,
            $responseData,
            $request
        );
    }

    /**
     * Log failed verification.
     */
    public static function logFailed(
        string $purchaseCode,
        string $domain,
        string $message,
        ?string $errorDetails = null,
        ?Request $request = null,
    ): LicenseVerificationLog {
        $logger = app(self::class);
        return $logger->loggingService->logFailedVerification(
            $purchaseCode,
            $domain,
            $message,
            $errorDetails,
            $request
        );
    }

    /**
     * Get verification statistics.
     */
    public function getVerificationStats(): array
    {
        return $this->analyticsService->getVerificationStats();
    }

    /**
     * Get verification trends.
     */
    public function getVerificationTrends(int $days = 30): array
    {
        return $this->analyticsService->getVerificationTrends($days);
    }

    /**
     * Get top domains.
     */
    public function getTopDomains(int $limit = 10): array
    {
        return $this->analyticsService->getTopDomains($limit);
    }

    /**
     * Get top sources.
     */
    public function getTopSources(int $limit = 10): array
    {
        return $this->analyticsService->getTopSources($limit);
    }

    /**
     * Get suspicious activity.
     */
    public function getSuspiciousActivity(): array
    {
        return $this->analyticsService->getSuspiciousActivity();
    }

    /**
     * Get verifications by domain.
     */
    public function getVerificationsByDomain(string $domain, int $limit = 50): array
    {
        return $this->analyticsService->getVerificationsByDomain($domain, $limit);
    }

    /**
     * Get verifications by IP.
     */
    public function getVerificationsByIp(string $ip, int $limit = 50): array
    {
        return $this->analyticsService->getVerificationsByIp($ip, $limit);
    }

    /**
     * Validate purchase code.
     */
    public function validatePurchaseCode(string $purchaseCode): bool
    {
        return $this->validationService->validatePurchaseCode($purchaseCode);
    }

    /**
     * Validate domain.
     */
    public function validateDomain(string $domain): bool
    {
        return $this->validationService->validateDomain($domain);
    }

    /**
     * Validate source.
     */
    public function validateSource(string $source): bool
    {
        return $this->validationService->validateSource($source);
    }

    /**
     * Validate IP address.
     */
    public function validateIpAddress(string $ip): bool
    {
        return $this->validationService->validateIpAddress($ip);
    }

    /**
     * Validate user agent.
     */
    public function validateUserAgent(?string $userAgent): bool
    {
        return $this->validationService->validateUserAgent($userAgent);
    }

    /**
     * Validate message.
     */
    public function validateMessage(string $message): bool
    {
        return $this->validationService->validateMessage($message);
    }

    /**
     * Validate error details.
     */
    public function validateErrorDetails(?string $errorDetails): bool
    {
        return $this->validationService->validateErrorDetails($errorDetails);
    }

    /**
     * Validate response data.
     */
    public function validateResponseData(?array $responseData): bool
    {
        return $this->validationService->validateResponseData($responseData);
    }

    /**
     * Validate verification log data.
     */
    public function validateVerificationLogData(array $data): array
    {
        return $this->validationService->validateVerificationLogData($data);
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
        return $this->validationService->isSuspiciousVerification(
            $purchaseCode,
            $domain,
            $ipAddress,
            $userAgent
        );
    }

    /**
     * Sanitize input data.
     */
    public function sanitizeInputData(array $data): array
    {
        return $this->validationService->sanitizeInputData($data);
    }
}
