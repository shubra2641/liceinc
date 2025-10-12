<?php

declare(strict_types=1);

namespace App\Services\Installation;

use LicenseProtection\LicenseVerifier;
use Illuminate\Support\Facades\Log;

/**
 * License Verification Service.
 *
 * Handles license verification with proper security validation
 * and comprehensive error handling.
 *
 * @version 1.0.0
 */
class LicenseVerificationService
{
    /**
     * Verify license with comprehensive validation.
     *
     * @param string $purchaseCode The purchase code to verify
     * @param string $domain The domain to verify against
     *
     * @return array<string, mixed> Verification result
     */
    public function verifyLicense(string $purchaseCode, string $domain): array
    {
        try {
            // Validate inputs
            $this->validateInputs($purchaseCode, $domain);
            
            // Create license verifier instance
            $licenseVerifier = new LicenseVerifier();
            
            // Perform actual license verification
            $result = $licenseVerifier->verifyLicense($purchaseCode, $domain);
            
            // Log successful verification
            Log::info('License verification successful', [
                'domain' => $domain,
                'purchase_code_length' => strlen($purchaseCode),
            ]);
            
            return [
                'valid' => true,
                'message' => 'License verified successfully',
                'verified_at' => now()->toDateTimeString(),
                'product' => $result['product'] ?? 'License Management System',
                'expires_at' => $result['expires_at'] ?? null,
            ];
            
        } catch (\Exception $e) {
            Log::error('License verification failed', [
                'domain' => $domain,
                'purchase_code_length' => strlen($purchaseCode),
                'error' => $e->getMessage(),
            ]);
            
            return [
                'valid' => false,
                'message' => $this->getErrorMessage($e),
                'error_code' => $this->getErrorCode($e),
            ];
        }
    }

    /**
     * Validate input parameters.
     *
     * @throws \InvalidArgumentException When inputs are invalid
     */
    private function validateInputs(string $purchaseCode, string $domain): void
    {
        if (empty($purchaseCode) || strlen($purchaseCode) < 5) {
            throw new \InvalidArgumentException('Invalid purchase code');
        }
        
        if (empty($domain) || !filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Invalid domain');
        }
    }

    /**
     * Get user-friendly error message.
     */
    private function getErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();
        
        // Map technical errors to user-friendly messages
        if (str_contains($message, 'network') || str_contains($message, 'timeout')) {
            return 'Unable to verify license. Please check your internet connection and try again.';
        }
        
        if (str_contains($message, 'invalid') || str_contains($message, 'expired')) {
            return 'Invalid or expired license. Please check your purchase code.';
        }
        
        if (str_contains($message, 'domain')) {
            return 'License is not valid for this domain. Please contact support.';
        }
        
        return 'License verification failed. Please try again or contact support.';
    }

    /**
     * Get error code for debugging.
     */
    private function getErrorCode(\Exception $e): string
    {
        $message = $e->getMessage();
        
        if (str_contains($message, 'network')) {
            return 'NETWORK_ERROR';
        }
        
        if (str_contains($message, 'invalid')) {
            return 'INVALID_LICENSE';
        }
        
        if (str_contains($message, 'expired')) {
            return 'EXPIRED_LICENSE';
        }
        
        if (str_contains($message, 'domain')) {
            return 'DOMAIN_MISMATCH';
        }
        
        return 'VERIFICATION_FAILED';
    }
}
