<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\Envato\EnvatoService;
use Illuminate\Support\Facades\Log;

/**
 * Purchase Code Service with enhanced security and validation.
 */
class PurchaseCodeService
{
    public function __construct(
        private EnvatoService $envatoService,
        private PurchaseCodeValidationService $validationService,
        private PurchaseCodeVerificationService $verificationService,
        private PurchaseCodeAccessService $accessService
    ) {
    }

    /**
     * Clean and validate purchase code format.
     */
    public function cleanPurchaseCode(string $purchaseCode): string
    {
        return $this->validationService->cleanPurchaseCode($purchaseCode);
    }

    /**
     * Validate purchase code format.
     */
    public function isValidFormat(string $purchaseCode): bool
    {
        return $this->validationService->isValidFormat($purchaseCode);
    }

    /**
     * Verify purchase code with dual verification system.
     */
    public function verifyPurchaseCode(string $purchaseCode, ?int $productId = null, ?User $user = null): array
    {
        return $this->verificationService->verifyPurchaseCode($purchaseCode, $productId, $user);
    }

    /**
     * Check if user has access to product.
     */
    public function userHasProductAccess(User $user, int $productId): bool
    {
        return $this->accessService->userHasProductAccess($user, $productId);
    }

    /**
     * Check if user has access to knowledge base.
     */
    public function userHasKnowledgeBaseAccess(User $user, int $productId): bool
    {
        return $this->accessService->userHasKnowledgeBaseAccess($user, $productId);
    }

    /**
     * Get user's active licenses.
     */
    public function getUserActiveLicenses(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $this->accessService->getUserActiveLicenses($user);
    }

    /**
     * Get user's licenses for product.
     */
    public function getUserLicensesForProduct(User $user, int $productId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->accessService->getUserLicensesForProduct($user, $productId);
    }

    /**
     * Check if user can download product files.
     */
    public function userCanDownloadFiles(User $user, int $productId): bool
    {
        return $this->accessService->userCanDownloadFiles($user, $productId);
    }

    /**
     * Get user's product access summary.
     */
    public function getUserProductAccessSummary(User $user): array
    {
        return $this->accessService->getUserProductAccessSummary($user);
    }

    /**
     * Check if license is valid for product.
     */
    public function isLicenseValidForProduct(License $license, int $productId): bool
    {
        return $this->accessService->isLicenseValidForProduct($license, $productId);
    }

    /**
     * Get license for user and product.
     */
    public function getLicenseForUserAndProduct(User $user, int $productId): ?License
    {
        return $this->accessService->getLicenseForUserAndProduct($user, $productId);
    }

    /**
     * Create license from purchase code.
     */
    public function createLicenseFromPurchaseCode(User $user, int $productId, string $purchaseCode): ?License
    {
        try {
            $verification = $this->verifyPurchaseCode($purchaseCode, $productId, $user);

            if (!$verification['success']) {
                Log::warning('Failed to create license from purchase code', [
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'error' => $verification['error'] ?? 'Unknown error',
                ]);
                return null;
            }

            $license = $verification['license'] ?? null;

            if ($license) {
                Log::info('License created from purchase code', [
                    'license_id' => $license->id,
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'source' => $verification['source'] ?? 'unknown',
                ]);
            }

            return $license;
        } catch (\Exception $e) {
            Log::error('Error creating license from purchase code', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Validate purchase code for product.
     */
    public function validatePurchaseCodeForProduct(string $purchaseCode, int $productId): array
    {
        try {
            $validation = $this->validationService->validateForVerification($purchaseCode);
            if (!$validation['valid']) {
                return [
                    'valid' => false,
                    'error' => $validation['error']
                ];
            }

            $verification = $this->verifyPurchaseCode($purchaseCode, $productId);

            return [
                'valid' => $verification['success'],
                'error' => $verification['error'] ?? null,
                'source' => $verification['source'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Error validating purchase code for product', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'error' => 'Validation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get purchase code statistics.
     */
    public function getPurchaseCodeStatistics(): array
    {
        try {
            $totalLicenses = License::count();
            $activeLicenses = License::where('status', 'active')->count();
            $expiredLicenses = License::where('license_expires_at', '<', now())->count();
            $uniqueProducts = License::distinct('product_id')->count('product_id');

            return [
                'total_licenses' => $totalLicenses,
                'active_licenses' => $activeLicenses,
                'expired_licenses' => $expiredLicenses,
                'unique_products' => $uniqueProducts,
            ];
        } catch (\Exception $e) {
            Log::error('Error getting purchase code statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_licenses' => 0,
                'active_licenses' => 0,
                'expired_licenses' => 0,
                'unique_products' => 0,
            ];
        }
    }
}
