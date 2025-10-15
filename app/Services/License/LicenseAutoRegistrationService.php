<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * License Auto Registration Service - Provides automated license registration functionality.
 */
class LicenseAutoRegistrationService
{
    public function __construct(
        private RegistrationValidationService $validationService,
        private RegistrationProcessingService $processingService,
        private RegistrationResultService $resultService
    ) {
    }

    /**
     * Automatically register a license for the authenticated user.
     */
    public function autoRegisterLicense(string $purchaseCode, ?int $productId = null): array
    {
        try {
            // Validate input parameters
            $this->validationService->validatePurchaseCode($purchaseCode);
            $this->validationService->validateProductId($productId);

            $user = $this->validationService->validateUserAuthentication();

            return DB::transaction(function () use ($purchaseCode, $productId, $user) {
                // Check if user already has this license
                $existingLicense = $this->processingService->findExistingLicense($purchaseCode, $user->id);
                if ($existingLicense) {
                    return $this->resultService->createExistingLicenseResult($existingLicense);
                }

                // Verify the purchase code
                $verificationResult = $this->processingService->verifyPurchaseCode($purchaseCode, $productId);
                if (!$verificationResult['success']) {
                    return $this->resultService->createInvalidPurchaseCodeResult(
                        $verificationResult['message'] ?? 'Invalid purchase code'
                    );
                }

                // Determine product ID from verification result or provided parameter
                $licenseProductId = $this->processingService->determineProductId($productId, $verificationResult);
                if (!$licenseProductId) {
                    return $this->resultService->createFailureResult(
                        'Could not determine product for this purchase code'
                    );
                }

                // Verify product exists
                $product = $this->processingService->findProduct($licenseProductId);
                if (!$product) {
                    return $this->resultService->createProductNotFoundResult();
                }

                // Check if product is available
                $availability = $this->validationService->isProductAvailable($licenseProductId);
                if (!$availability['available']) {
                    return $this->resultService->createFailureResult($availability['reason']);
                }

                // Check if user can register license
                $canRegister = $this->validationService->canUserRegisterLicense($user->id, $purchaseCode);
                if (!$canRegister['can_register']) {
                    return $this->resultService->createFailureResult($canRegister['reason']);
                }

                // Process license registration
                $license = $this->processingService->processLicenseRegistration(
                    $purchaseCode,
                    $licenseProductId,
                    $user->id,
                    $verificationResult
                );

                $result = $this->resultService->createSuccessResult($license);
                $this->resultService->logRegistrationResult($result, $purchaseCode, $productId);

                return $result;
            });
        } catch (\Exception $e) {
            Log::error('Failed to auto-register license', [
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return $this->resultService->createSystemErrorResult('License registration failed');
        }
    }

    /**
     * Check if purchase code is valid and can be registered.
     */
    public function checkPurchaseCode(string $purchaseCode, ?int $productId = null): array
    {
        try {
            // Validate input parameters
            $this->validationService->validatePurchaseCode($purchaseCode);
            $this->validationService->validateProductId($productId);

            $user = $this->validationService->validateUserAuthentication();

            // Check if user already has this license
            $existingLicense = $this->processingService->findExistingLicense($purchaseCode, $user->id);
            if ($existingLicense) {
                return [
                    'valid' => true,
                    'message' => 'License already exists for this user',
                    'existing_license' => $existingLicense,
                ];
            }

            // Verify the purchase code
            $verificationResult = $this->processingService->verifyPurchaseCode($purchaseCode, $productId);
            if (!$verificationResult['success']) {
                return [
                    'valid' => false,
                    'message' => $verificationResult['message'] ?? 'Invalid purchase code',
                    'existing_license' => null,
                ];
            }

            return [
                'valid' => true,
                'message' => 'Purchase code is valid',
                'existing_license' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check purchase code', [
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return [
                'valid' => false,
                'message' => 'Error checking purchase code',
                'existing_license' => null,
            ];
        }
    }

    /**
     * Get registration statistics.
     */
    public function getRegistrationStatistics(): array
    {
        try {
            $stats = [
                'total_registrations' => License::count(),
                'successful_registrations' => License::where('status', 'active')->count(),
                'failed_registrations' => License::where('status', 'inactive')->count(),
                'success_rate' => 0,
                'average_registration_time' => 0,
            ];

            if ($stats['total_registrations'] > 0) {
                $stats['success_rate'] = round(
                    ($stats['successful_registrations'] / $stats['total_registrations']) * 100,
                    2
                );
            }

            return $this->resultService->formatRegistrationStatistics($stats);
        } catch (\Exception $e) {
            Log::error('Failed to get registration statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'total_registrations' => 0,
                'successful_registrations' => 0,
                'failed_registrations' => 0,
                'success_rate' => 0,
                'average_registration_time' => 0,
            ];
        }
    }

    /**
     * Bulk register licenses.
     */
    public function bulkRegisterLicenses(array $purchaseCodes, ?int $productId = null): array
    {
        try {
            $results = [];

            foreach ($purchaseCodes as $purchaseCode) {
                $result = $this->autoRegisterLicense($purchaseCode, $productId);
                $results[] = $result;
            }

            return $this->resultService->createBulkRegistrationResult($results);
        } catch (\Exception $e) {
            Log::error('Bulk license registration failed', [
                'error' => $e->getMessage(),
                'purchase_codes' => $purchaseCodes,
                'product_id' => $productId,
            ]);

            return $this->resultService->createSystemErrorResult('Bulk registration failed');
        }
    }

    /**
     * Validate registration request.
     */
    public function validateRegistrationRequest(string $purchaseCode, ?int $productId): array
    {
        try {
            $errors = $this->validationService->validateRegistrationRequest($purchaseCode, $productId);

            if (!empty($errors)) {
                return $this->resultService->createValidationErrorResult($errors);
            }

            return $this->resultService->createSuccessResult(null, 'Registration request is valid');
        } catch (\Exception $e) {
            Log::error('Registration request validation failed', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
            ]);

            return $this->resultService->createSystemErrorResult('Validation failed');
        }
    }

    /**
     * Get user license count.
     */
    public function getUserLicenseCount(int $userId): int
    {
        try {
            return License::where('user_id', $userId)->count();
        } catch (\Exception $e) {
            Log::error('Failed to get user license count', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return 0;
        }
    }

    /**
     * Check if user can register more licenses.
     */
    public function canUserRegisterMoreLicenses(int $userId, int $maxLicenses = 10): bool
    {
        try {
            $currentCount = $this->getUserLicenseCount($userId);
            return $currentCount < $maxLicenses;
        } catch (\Exception $e) {
            Log::error('Failed to check user license limit', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return false;
        }
    }
}
