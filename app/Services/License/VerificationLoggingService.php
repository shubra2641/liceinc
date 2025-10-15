<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\LicenseVerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Verification Logging Service - Handles license verification logging.
 */
class VerificationLoggingService
{
    /**
     * Log license verification attempt.
     */
    public function logVerification(
        string $purchaseCode,
        string $domain,
        bool $isValid,
        string $message,
        ?array $responseData = null,
        string $source = 'install',
        ?Request $request = null,
        ?string $errorDetails = null,
    ): LicenseVerificationLog {
        try {
            $purchaseCode = $this->validatePurchaseCode($purchaseCode);
            $domain = $this->validateDomain($domain);
            $message = $this->sanitizeString($message);
            $source = $this->validateSource($source);
            $errorDetails = $this->sanitizeString($errorDetails);

            $ipAddress = $this->getValidatedIpAddress($request);
            $userAgent = $this->getValidatedUserAgent($request);

            $purchaseCodeHash = hash('sha256', $purchaseCode);
            $status = $this->determineStatus($isValid, $errorDetails);

            $log = LicenseVerificationLog::create([
                'purchase_code_hash' => $purchaseCodeHash,
                'domain' => $domain,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'is_valid' => $isValid,
                'response_message' => $message,
                'response_data' => $responseData,
                'verification_source' => $source,
                'status' => $status,
                'error_details' => $errorDetails,
                'verified_at' => $isValid ? now() : null,
            ]);

            if (!$isValid || $errorDetails) {
                $this->logToFile($purchaseCode, $domain, $isValid, $message, $errorDetails);
            }

            return $log;
        } catch (\Exception $e) {
            Log::error('Failed to log license verification', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'domain' => $domain,
            ]);
            throw $e;
        }
    }

    /**
     * Log successful verification.
     */
    public function logSuccessfulVerification(
        string $purchaseCode,
        string $domain,
        string $message,
        ?array $responseData = null,
        ?Request $request = null,
    ): LicenseVerificationLog {
        return $this->logVerification(
            $purchaseCode,
            $domain,
            true,
            $message,
            $responseData,
            'install',
            $request
        );
    }

    /**
     * Log failed verification.
     */
    public function logFailedVerification(
        string $purchaseCode,
        string $domain,
        string $message,
        ?string $errorDetails = null,
        ?Request $request = null,
    ): LicenseVerificationLog {
        return $this->logVerification(
            $purchaseCode,
            $domain,
            false,
            $message,
            null,
            'install',
            $request,
            $errorDetails
        );
    }

    /**
     * Validate purchase code.
     */
    private function validatePurchaseCode(string $purchaseCode): string
    {
        if (empty($purchaseCode)) {
            throw new \InvalidArgumentException('Purchase code is required');
        }

        if (strlen($purchaseCode) < 10) {
            throw new \InvalidArgumentException('Purchase code is too short');
        }

        return trim($purchaseCode);
    }

    /**
     * Validate domain.
     */
    private function validateDomain(string $domain): string
    {
        if (empty($domain)) {
            throw new \InvalidArgumentException('Domain is required');
        }

        if (!filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Invalid domain format');
        }

        return trim($domain);
    }

    /**
     * Validate source.
     */
    private function validateSource(string $source): string
    {
        $allowedSources = ['install', 'api', 'admin'];
        if (!in_array($source, $allowedSources)) {
            throw new \InvalidArgumentException('Invalid verification source');
        }

        return $source;
    }

    /**
     * Sanitize string.
     */
    private function sanitizeString(?string $input): ?string
    {
        if (!$input) {
            return null;
        }

        return trim(strip_tags($input));
    }

    /**
     * Get validated IP address.
     */
    private function getValidatedIpAddress(?Request $request): string
    {
        if (!$request) {
            return 'unknown';
        }

        $ip = $request->ip();
        if (!$ip || $ip === '127.0.0.1') {
            return 'unknown';
        }

        return $ip;
    }

    /**
     * Get validated user agent.
     */
    private function getValidatedUserAgent(?Request $request): ?string
    {
        if (!$request) {
            return null;
        }

        $userAgent = $request->userAgent();
        if (!$userAgent || strlen($userAgent) > 500) {
            return null;
        }

        return $userAgent;
    }

    /**
     * Determine status.
     */
    private function determineStatus(bool $isValid, ?string $errorDetails): string
    {
        if ($isValid) {
            return 'success';
        }

        if ($errorDetails) {
            return 'error';
        }

        return 'failed';
    }

    /**
     * Log to file.
     */
    private function logToFile(
        string $purchaseCode,
        string $domain,
        bool $isValid,
        string $message,
        ?string $errorDetails
    ): void {
        $logData = [
            'purchase_code' => $this->maskPurchaseCode($purchaseCode),
            'domain' => $domain,
            'is_valid' => $isValid,
            'message' => $message,
            'error_details' => $errorDetails,
            'timestamp' => now()->toISOString(),
        ];

        if (!$isValid) {
            Log::warning('License verification failed', $logData);
        } else {
            Log::info('License verification successful', $logData);
        }
    }

    /**
     * Mask purchase code.
     */
    private function maskPurchaseCode(string $purchaseCode): string
    {
        if (strlen($purchaseCode) <= 8) {
            return str_repeat('*', strlen($purchaseCode));
        }

        return substr($purchaseCode, 0, 4) . str_repeat('*', strlen($purchaseCode) - 8) . substr($purchaseCode, -4);
    }
}
