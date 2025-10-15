<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

/**
 * License Service with enhanced security and comprehensive license management.
 */
class LicenseService
{
    public function __construct(
        private LicenseValidationService $validationService,
        private LicenseCalculationService $calculationService,
        private LicenseManagementService $managementService
    ) {
    }

    /**
     * Create license for a user and product.
     */
    public function createLicense(User $user, Product $product, ?string $paymentGateway = null): License
    {
        return $this->managementService->createLicense($user, $product, $paymentGateway);
    }

    /**
     * Get user licenses.
     */
    public function getUserLicenses(User $user, bool $activeOnly = true): Collection
    {
        return $this->managementService->getUserLicenses($user, $activeOnly);
    }

    /**
     * Get active user license for product.
     */
    public function getActiveUserLicenseForProduct(User $user, Product $product): ?License
    {
        return $this->managementService->getActiveUserLicenseForProduct($user, $product);
    }

    /**
     * Check if user can purchase product.
     */
    public function canUserPurchaseProduct(User $user, Product $product): array
    {
        return $this->managementService->canUserPurchaseProduct($user, $product);
    }

    /**
     * Check if license is valid.
     */
    public function isLicenseValid(string $licenseKey, ?string $domain = null): array
    {
        try {
            $this->validationService->validateLicenseKey($licenseKey);
            $this->validationService->validateDomain($domain);

            $license = License::where('license_key', $this->validationService->sanitizeInput($licenseKey))
                ->with('product', 'user')
                ->first();

            if (!$license) {
                Log::warning('License validation failed: License key not found', [
                    'license_key' => $this->validationService->hashForLogging($licenseKey),
                    'domain' => $domain ? $this->validationService->hashForLogging($domain) : null,
                ]);

                return [
                    'valid' => false,
                    'message' => 'License key not found',
                ];
            }

            $statusValidation = $this->validationService->validateLicenseStatus($license);
            if (!$statusValidation['valid']) {
                Log::warning('License validation failed: ' . $statusValidation['message'], [
                    'license_id' => $license->id,
                    'status' => $license->status,
                    'domain' => $domain ? $this->validationService->hashForLogging($domain) : null,
                ]);

                return $statusValidation;
            }

            $domainValidation = $this->validationService->validateDomainAuthorization($license, $domain);
            if (!$domainValidation['authorized']) {
                Log::warning('License validation failed: ' . $domainValidation['message'], [
                    'license_id' => $license->id,
                    'domain' => $domain ? $this->validationService->hashForLogging($domain) : null,
                ]);

                return [
                    'valid' => false,
                    'message' => $domainValidation['message'],
                ];
            }

            return [
                'valid' => true,
                'message' => 'License is valid',
                'license' => $license,
            ];
        } catch (\Exception $e) {
            Log::error('License validation failed', [
                'license_key' => $this->validationService->hashForLogging($licenseKey),
                'domain' => $domain ? $this->validationService->hashForLogging($domain) : null,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'License validation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update license status.
     */
    public function updateLicenseStatus(License $license, string $status): bool
    {
        return $this->managementService->updateLicenseStatus($license, $status);
    }

    /**
     * Add authorized domain to license.
     */
    public function addAuthorizedDomain(License $license, string $domain): bool
    {
        return $this->managementService->addAuthorizedDomain($license, $domain);
    }

    /**
     * Remove authorized domain from license.
     */
    public function removeAuthorizedDomain(License $license, string $domain): bool
    {
        return $this->managementService->removeAuthorizedDomain($license, $domain);
    }

    /**
     * Calculate remaining license days.
     */
    public function calculateRemainingLicenseDays(License $license): int
    {
        if (!$license->license_expires_at) {
            return 0;
        }

        return $this->calculationService->calculateRemainingLicenseDays($license->license_expires_at);
    }

    /**
     * Calculate remaining support days.
     */
    public function calculateRemainingSupportDays(License $license): int
    {
        if (!$license->support_expires_at) {
            return 0;
        }

        return $this->calculationService->calculateRemainingSupportDays($license->support_expires_at);
    }

    /**
     * Check if license is expired.
     */
    public function isLicenseExpired(License $license): bool
    {
        if (!$license->license_expires_at) {
            return false;
        }

        return $this->calculationService->isLicenseExpired($license->license_expires_at);
    }

    /**
     * Check if support is expired.
     */
    public function isSupportExpired(License $license): bool
    {
        if (!$license->support_expires_at) {
            return false;
        }

        return $this->calculationService->isSupportExpired($license->support_expires_at);
    }

    /**
     * Get license status.
     */
    public function getLicenseStatus(License $license): string
    {
        if (!$license->license_expires_at) {
            return 'active';
        }

        return $this->calculationService->getLicenseStatus($license->license_expires_at);
    }

    /**
     * Get support status.
     */
    public function getSupportStatus(License $license): string
    {
        if (!$license->support_expires_at) {
            return 'active';
        }

        return $this->calculationService->getSupportStatus($license->support_expires_at);
    }
}
