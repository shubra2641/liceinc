<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\Envato\EnvatoService;
use Illuminate\Support\Facades\Log;

/**
 * Purchase Code Verification Service - Handles purchase code verification.
 */
class PurchaseCodeVerificationService
{
    public function __construct(
        private EnvatoService $envatoService,
        private PurchaseCodeValidationService $validationService
    ) {
    }

    /**
     * Verify purchase code with dual verification system.
     */
    public function verifyPurchaseCode(string $purchaseCode, ?int $productId = null, ?User $user = null): array
    {
        try {
            $validation = $this->validationService->validateForVerification($purchaseCode);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error'],
                    'source' => null,
                ];
            }

            $cleanedCode = $validation['cleaned_code'];

            // 1. Check our database first
            $dbResult = $this->verifyAgainstDatabase($cleanedCode, $productId);
            if ($dbResult['success']) {
                return $dbResult;
            }

            // 2. Check Envato if not found in our database
            $envatoResult = $this->verifyAgainstEnvato($cleanedCode, $productId, $user);
            if ($envatoResult['success']) {
                return $envatoResult;
            }

            return [
                'success' => false,
                'error' => 'Invalid purchase code',
                'source' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Error verifying purchase code', [
                'purchase_code_length' => strlen($purchaseCode),
                'product_id' => $productId ?? 'null',
                'user_id' => $user->id ?? 'null',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify against database.
     */
    private function verifyAgainstDatabase(string $purchaseCode, ?int $productId = null): array
    {
        try {
            $query = License::where('purchase_code', $purchaseCode);

            if ($productId) {
                $query->where('product_id', $productId);
            }

            $license = $query->first();

            if (!$license) {
                return [
                    'success' => false,
                    'error' => 'License not found in database',
                    'source' => 'database',
                ];
            }

            if ($license->status !== 'active') {
                return [
                    'success' => false,
                    'error' => 'License is not active',
                    'source' => 'database',
                ];
            }

            return [
                'success' => true,
                'license' => $license,
                'source' => 'database',
            ];
        } catch (\Exception $e) {
            Log::error('Error verifying against database', [
                'purchase_code' => $this->validationService->sanitizeInput($purchaseCode),
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Database verification failed',
                'source' => 'database',
            ];
        }
    }

    /**
     * Verify against Envato API.
     */
    private function verifyAgainstEnvato(string $purchaseCode, ?int $productId = null, ?User $user = null): array
    {
        try {
            $envatoResult = $this->envatoService->verifyPurchaseCode($purchaseCode);

            if (!$envatoResult['success']) {
                return [
                    'success' => false,
                    'error' => 'Envato verification failed',
                    'source' => 'envato',
                ];
            }

            $envatoData = $envatoResult['data'] ?? [];

            if (empty($envatoData)) {
                return [
                    'success' => false,
                    'error' => 'No data from Envato',
                    'source' => 'envato',
                ];
            }

            // Create license from Envato data
            if ($user && $productId) {
                $license = $this->createLicenseFromEnvatoData($user, $productId, $envatoData, $purchaseCode);

                return [
                    'success' => true,
                    'license' => $license,
                    'source' => 'envato',
                ];
            }

            return [
                'success' => true,
                'envato_data' => $envatoData,
                'source' => 'envato',
            ];
        } catch (\Exception $e) {
            Log::error('Error verifying against Envato', [
                'purchase_code' => $this->validationService->sanitizeInput($purchaseCode),
                'product_id' => $productId,
                'user_id' => $user->id ?? 'null',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Envato verification failed',
                'source' => 'envato',
            ];
        }
    }

    /**
     * Create license from Envato data.
     */
    private function createLicenseFromEnvatoData(User $user, int $productId, array $envatoData, string $purchaseCode): License
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                throw new \Exception('Product not found');
            }

            $license = License::create([
                'user_id' => $user->id,
                'product_id' => $productId,
                'purchase_code' => $purchaseCode,
                'license_type' => $product->license_type ?? 'single',
                'status' => 'active',
                'max_domains' => $product->max_domains ?? 1,
                'license_expires_at' => now()->addYear(),
                'support_expires_at' => now()->addYear(),
                'notes' => 'Created from Envato verification',
                'envato_data' => $envatoData,
            ]);

            Log::info('License created from Envato data', [
                'license_id' => $license->id,
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);

            return $license;
        } catch (\Exception $e) {
            Log::error('Error creating license from Envato data', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
