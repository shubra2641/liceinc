<?php

declare(strict_types=1);

namespace App\Services\License\Strategies;

use App\Models\License;
use App\Models\LicenseDomain;

/**
 * Standard License Verification Strategy
 * 
 * Handles verification for standard licenses
 */
class StandardLicenseVerificationStrategy implements LicenseVerificationStrategy
{
    /**
     * Verify license
     */
    public function verify(License $license, array $data): array
    {
        if (!$this->isLicenseActive($license)) {
            return $this->createErrorResponse('INACTIVE_LICENSE', 'License is not active');
        }

        if (!$this->isLicenseValid($license)) {
            return $this->createErrorResponse('INVALID_LICENSE', 'License is invalid');
        }

        if (isset($data['domain']) && !$this->isDomainAllowed($license, $data['domain'])) {
            return $this->createErrorResponse('DOMAIN_NOT_ALLOWED', 'Domain not allowed for this license');
        }

        return $this->createSuccessResponse($license, $data);
    }

    /**
     * Check if strategy can handle the license type
     */
    public function canHandle(string $licenseType): bool
    {
        return $licenseType === 'standard';
    }

    /**
     * Check if license is active
     */
    private function isLicenseActive(License $license): bool
    {
        return $license->status === 'active';
    }

    /**
     * Check if license is valid
     */
    private function isLicenseValid(License $license): bool
    {
        return $license->license_expires_at === null || $license->license_expires_at > now();
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
    private function createSuccessResponse(License $license, array $data): array
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

        if (isset($data['domain'])) {
            $response['domain'] = $data['domain'];
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
