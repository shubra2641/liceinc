<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Registration Processing Service - Handles license registration processing.
 */
class RegistrationProcessingService
{
    public function __construct(
        private PurchaseCodeService $purchaseCodeService,
        private InvoiceService $invoiceService
    ) {
    }

    /**
     * Process license registration.
     */
    public function processLicenseRegistration(
        string $purchaseCode,
        int $productId,
        int $userId,
        array $verificationResult
    ): License {
        try {
            DB::beginTransaction();

            // Create the license
            $license = $this->createLicense($purchaseCode, $productId, $userId, $verificationResult);

            // Create initial invoice
            $this->createInitialInvoice($license);

            // Decrease product stock
            $this->decreaseProductStock($productId);

            DB::commit();

            return $license;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License registration processing failed', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Create license.
     */
    private function createLicense(
        string $purchaseCode,
        int $productId,
        int $userId,
        array $verificationResult
    ): License {
        try {
            $licenseData = [
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'user_id' => $userId,
                'status' => 'active',
                'expires_at' => $this->calculateExpirationDate($verificationResult),
                'support_expires_at' => $this->calculateSupportExpirationDate($verificationResult),
                'verification_data' => $verificationResult,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            return License::create($licenseData);
        } catch (\Exception $e) {
            Log::error('License creation failed', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Create initial invoice.
     */
    private function createInitialInvoice(License $license): void
    {
        try {
            $this->invoiceService->createInitialInvoice($license, 'paid');
        } catch (\Exception $e) {
            Log::error('Initial invoice creation failed', [
                'error' => $e->getMessage(),
                'license_id' => $license->id,
            ]);
            throw $e;
        }
    }

    /**
     * Decrease product stock.
     */
    private function decreaseProductStock(int $productId): void
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                throw new \Exception('Product not found');
            }

            if ($product->stock <= 0) {
                throw new \Exception('Product is out of stock');
            }

            $product->decrement('stock');
        } catch (\Exception $e) {
            Log::error('Product stock decrease failed', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);
            throw $e;
        }
    }

    /**
     * Calculate expiration date.
     */
    private function calculateExpirationDate(array $verificationResult): ?\DateTimeInterface
    {
        try {
            $licenseType = $verificationResult['license_type'] ?? 'standard';
            $duration = $this->getLicenseDuration($licenseType);

            if ($duration === null) {
                return null; // Lifetime license
            }

            return now()->addDays($duration);
        } catch (\Exception $e) {
            Log::error('Expiration date calculation failed', [
                'error' => $e->getMessage(),
                'verification_result' => $verificationResult,
            ]);
            return null;
        }
    }

    /**
     * Calculate support expiration date.
     */
    private function calculateSupportExpirationDate(array $verificationResult): ?\DateTimeInterface
    {
        try {
            $supportDuration = $verificationResult['support_duration'] ?? 365; // Default 1 year
            return now()->addDays($supportDuration);
        } catch (\Exception $e) {
            Log::error('Support expiration date calculation failed', [
                'error' => $e->getMessage(),
                'verification_result' => $verificationResult,
            ]);
            return now()->addDays(365); // Default 1 year
        }
    }

    /**
     * Get license duration.
     */
    private function getLicenseDuration(string $licenseType): ?int
    {
        $durations = [
            'trial' => 30,
            'standard' => 365,
            'premium' => 365,
            'lifetime' => null,
        ];

        return $durations[$licenseType] ?? 365;
    }

    /**
     * Verify purchase code.
     */
    public function verifyPurchaseCode(string $purchaseCode, ?int $productId): array
    {
        try {
            return $this->purchaseCodeService->verifyRawCode($purchaseCode, $productId);
        } catch (\Exception $e) {
            Log::error('Purchase code verification failed', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
            ]);
            throw $e;
        }
    }

    /**
     * Determine product ID.
     */
    public function determineProductId(?int $productId, array $verificationResult): ?int
    {
        if ($productId) {
            return $productId;
        }

        return $verificationResult['product_id'] ?? null;
    }

    /**
     * Find product.
     */
    public function findProduct(int $productId): ?Product
    {
        try {
            return Product::find($productId);
        } catch (\Exception $e) {
            Log::error('Product lookup failed', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);
            throw $e;
        }
    }

    /**
     * Find existing license.
     */
    public function findExistingLicense(string $purchaseCode, int $userId): ?License
    {
        try {
            return License::where('purchase_code', $purchaseCode)
                ->where('user_id', $userId)
                ->first();
        } catch (\Exception $e) {
            Log::error('Existing license lookup failed', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Update license status.
     */
    public function updateLicenseStatus(int $licenseId, string $status): bool
    {
        try {
            $license = License::find($licenseId);
            if (!$license) {
                throw new \Exception('License not found');
            }

            $license->status = $status;
            $license->save();

            return true;
        } catch (\Exception $e) {
            Log::error('License status update failed', [
                'error' => $e->getMessage(),
                'license_id' => $licenseId,
                'status' => $status,
            ]);
            throw $e;
        }
    }

    /**
     * Extend license.
     */
    public function extendLicense(int $licenseId, int $days): bool
    {
        try {
            $license = License::find($licenseId);
            if (!$license) {
                throw new \Exception('License not found');
            }

            $currentExpiry = $license->expires_at;
            $newExpiry = $currentExpiry ? $currentExpiry->addDays($days) : now()->addDays($days);

            $license->expires_at = $newExpiry;
            $license->save();

            return true;
        } catch (\Exception $e) {
            Log::error('License extension failed', [
                'error' => $e->getMessage(),
                'license_id' => $licenseId,
                'days' => $days,
            ]);
            throw $e;
        }
    }
}
