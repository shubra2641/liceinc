<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\LicenseDomain;
use App\Models\Product;
use App\Services\LicenseService;
use Illuminate\Support\Facades\Log;

/**
 * License Verification Service
 * 
 * Handles license verification logic
 */
class LicenseVerificationService
{
    public function __construct(
        private LicenseService $licenseService
    ) {
    }

    /**
     * Verify license key
     */
    public function verifyLicenseKey(string $licenseKey, string $domain = null): array
    {
        try {
            $license = $this->findLicenseByKey($licenseKey);
            
            if (!$license) {
                return $this->createErrorResponse('INVALID_LICENSE', 'License key not found');
            }

            if (!$this->isLicenseActive($license)) {
                return $this->createErrorResponse('INACTIVE_LICENSE', 'License is not active');
            }

            if ($domain && !$this->isDomainAllowed($license, $domain)) {
                return $this->createErrorResponse('DOMAIN_NOT_ALLOWED', 'Domain not allowed for this license');
            }

            return $this->createSuccessResponse($license, $domain);
            
        } catch (\Exception $e) {
            Log::error('License verification failed', [
                'license_key' => $licenseKey,
                'domain' => $domain,
                'error' => $e->getMessage()
            ]);
            
            return $this->createErrorResponse('VERIFICATION_FAILED', 'License verification failed');
        }
    }

    /**
     * Find license by key
     */
    private function findLicenseByKey(string $licenseKey): ?License
    {
        return License::where('license_key', $licenseKey)
            ->with('product', 'user')
            ->first();
    }

    /**
     * Check if license is active
     */
    private function isLicenseActive(License $license): bool
    {
        return $license->status === 'active' && 
               ($license->license_expires_at === null || $license->license_expires_at > now());
    }

    /**
     * Check if domain is allowed
     */
    private function isDomainAllowed(License $license, string $domain): bool
    {
        if (!$license->product->domain_verification_required) {
            return true;
        }

        return LicenseDomain::where('license_id', $license->id)
            ->where('domain', $domain)
            ->where('is_active', true)
            ->exists();
    }

    /**
     * Create success response
     */
    private function createSuccessResponse(License $license, string $domain = null): array
    {
        $response = [
            'status' => 'success',
            'license_key' => $license->license_key,
            'product_id' => $license->product_id,
            'product_name' => $license->product->name,
            'license_type' => $license->license_type,
            'expires_at' => $license->license_expires_at?->toISOString(),
            'user_id' => $license->user_id,
        ];

        if ($domain) {
            $response['domain'] = $domain;
            $response['domain_verified'] = true;
        }

        return $response;
    }

    /**
     * Create error response
     */
    private function createErrorResponse(string $code, string $message): array
    {
        return [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ];
    }
}
