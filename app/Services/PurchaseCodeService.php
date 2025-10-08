<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Purchase Code Service with enhanced security and validation.
 *
 * This service handles purchase code verification, validation, and license management
 * with comprehensive security measures, dual verification systems, and access control.
 *
 * Features:
 * - Purchase code format validation and sanitization
 * - Dual verification system (database + Envato API)
 * - Comprehensive license management and creation
 * - Knowledge base access control based on licenses
 * - Product access validation and authorization
 * - Secure license creation from Envato data
 * - Advanced error handling and logging
 *
 *
 * @example
 * // Verify a purchase code
 * $service = new PurchaseCodeService($envatoService);
 * $result = $service->verifyPurchaseCode('ABC123DEF456', $productId, $user);
 *
 * // Check user access to product
 * $hasAccess = $service->userHasProductAccess($user, $productId);
 */
class PurchaseCodeService extends BaseService
{
    /**
     * The Envato service instance for API operations.
     *
     * @var EnvatoService
     */
    protected $envatoService;

    /**
     * Create a new PurchaseCodeService instance.
     *
     * @param EnvatoService $envatoService The Envato service for API operations
     */
    public function __construct(EnvatoService $envatoService)
    {
        $this->envatoService = $envatoService;
    }

    /**
     * Clean and validate purchase code format with security validation.
     *
     * Sanitizes and normalizes purchase codes by removing whitespace and dashes,
     * converting to uppercase for consistency, and applying security measures
     * to prevent injection attacks.
     *
     * @param string $purchaseCode The raw purchase code to clean
     *
     * @throws \InvalidArgumentException When purchase code is invalid
     *
     * @return string The cleaned and sanitized purchase code
     *
     * @example
     * $cleaned = $service->cleanPurchaseCode('abc-123 def');
     * // Returns: 'ABC123DEF'
     */
    public function cleanPurchaseCode(string $purchaseCode): string
    {
        try {
            // Validate input
            if (empty($purchaseCode)) {
                throw new \InvalidArgumentException('Purchase code cannot be empty');
            }
            // Sanitize input to prevent injection attacks
            $sanitized = $this->sanitizeInput($purchaseCode);
            // Remove all whitespace and dashes, convert to uppercase for consistency
            $cleaned = strtoupper(str_replace([' ', '-', '_'], '', trim($sanitized)));
            // Additional security validation
            if (strlen($cleaned) < 8 || strlen($cleaned) > 50) {
                throw new \InvalidArgumentException('Purchase code length is invalid');
            }

            return $cleaned;
        } catch (\Exception $e) {
            Log::error('Error cleaning purchase code', [
                'purchase_code_length' => strlen($purchaseCode),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate purchase code format with comprehensive security checks.
     *
     * Performs thorough format validation of purchase codes including
     * alphanumeric character validation, length restrictions, and
     * security pattern detection to ensure code integrity.
     *
     * @param string $purchaseCode The purchase code to validate
     *
     * @return bool True if format is valid, false otherwise
     *
     * @example
     * $isValid = $service->isValidFormat('ABC123DEF456');
     * if ($isValid) {
     *     echo "Purchase code format is valid";
     * }
     */
    public function isValidFormat(string $purchaseCode): bool
    {
        try {
            if (empty($purchaseCode)) {
                return false;
            }
            $cleaned = $this->cleanPurchaseCode($purchaseCode);

            // Basic format validation - should be alphanumeric and reasonable length
            return (bool)preg_match('/^[A-Z0-9]{8, 50}$/', $cleaned);
        } catch (\Exception $e) {
            Log::error('Error validating purchase code format', [
                'purchase_code_length' => strlen($purchaseCode),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Verify purchase code with dual verification system and comprehensive security.
     *
     * Implements a dual verification system that first checks the local database
     * and then falls back to Envato API verification. Includes comprehensive
     * security validation, error handling, and detailed logging.
     *
     * @param string $purchaseCode The purchase code to verify
     * @param int|null $productId Optional product ID for product-specific verification
     * @param User|null $user Optional user for license creation
     *
     * @throws \Exception When verification fails or security validation errors occur
     *
     * @return array Verification result with success status and details
     *
     * @example
     * $result = $service->verifyPurchaseCode('ABC123DEF456', $productId, $user);
     * if ($result['success']) {
     *     echo "Purchase code verified from: " . $result['source'];
     * }
     */
    /**
     * @return array<string, mixed>
     */
    public function verifyPurchaseCode(string $purchaseCode, ?int $productId = null, ?User $user = null): array
    {
        try {
            // Validate inputs
            if (empty($purchaseCode)) {
                throw new \InvalidArgumentException('Purchase code cannot be empty');
            }
            $cleanedCode = $this->cleanPurchaseCode($purchaseCode);
            // Validate format first
            if (! $this->isValidFormat($cleanedCode)) {
                return [
                    'success' => false,
                    'error' => 'Invalid purchase code format',
                    'source' => null,
                ];
            }
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
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize input data to prevent XSS and injection attacks.
     *
     * Provides comprehensive input sanitization for purchase codes and other
     * user inputs to ensure security and prevent various types of injection attacks.
     *
     *
     * @return string The sanitized input string
     */

    /**
     * Verify against our database with comprehensive security validation.
     *
     * Performs database verification of purchase codes with proper security
     * measures, license status validation, and expiration checks.
     *
     * @param string $purchaseCode The cleaned purchase code to verify
     * @param int|null $productId Optional product ID for product-specific verification
     *
     * @throws \Exception When database verification fails
     *
     * @return array Database verification result
     */
    /**
     * @return array<string, mixed>
     */
    protected function verifyAgainstDatabase(string $purchaseCode, ?int $productId = null): array
    {
        try {
            // Validate inputs
            if (empty($purchaseCode)) {
                throw new \InvalidArgumentException('Purchase code cannot be empty for database verification');
            }
            $query = License::where('status', 'active')
                ->where(function ($q) use ($purchaseCode) {
                    $q->where('purchase_code', $purchaseCode)
                        ->orWhere('license_key', $purchaseCode);
                })
                ->where(function ($q) {
                    $q->whereNull('license_expires_at')
                        ->orWhere('license_expires_at', '>', now());
                });
            // If product ID is specified, ensure the license belongs to that product
            if ($productId) {
                if ((int)$productId <= 0) {
                    throw new \InvalidArgumentException('Invalid product ID for database verification');
                }
                $query->where('product_id', $productId);
            }
            $license = $query->first();
            if ($license) {
                return [
                    'success' => true,
                    'license' => $license,
                    'source' => 'database',
                    'product_id' => $license->product_id,
                ];
            }

            return [
                'success' => false,
                'error' => 'Purchase code not found in our database',
                'source' => 'database',
            ];
        } catch (\Exception $e) {
            Log::error('Error verifying against database', [
                'purchase_code_length' => strlen($purchaseCode),
                'product_id' => $productId ?? 'null',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify raw code directly against database with comprehensive validation.
     *
     * Performs direct database verification without cleaning the input code,
     * useful for license key verification where exact format must be preserved.
     * Includes comprehensive status and expiration validation.
     *
     * @param string $rawCode The raw code to verify (no cleaning applied)
     * @param int|null $productId Optional product ID for product-specific verification
     *
     * @throws \Exception When raw code verification fails
     *
     * @return array Raw code verification result with detailed status information
     *
     * @example
     * $result = $service->verifyRawCode('ABC123DEF456', $productId);
     * if ($result['success']) {
     *     echo "Raw code verified successfully";
     * }
     */
    /**
     * @return array<string, mixed>
     */
    public function verifyRawCode(string $rawCode, ?int $productId = null): array
    {
        try {
            // Validate inputs
            if (empty($rawCode)) {
                throw new \InvalidArgumentException('Raw code cannot be empty for verification');
            }
            // Sanitize input to prevent injection attacks
            $sanitizedCode = $this->sanitizeInput($rawCode);
            // First, find the license by code (regardless of status or expiration)
            $license = License::where(function ($q) use ($sanitizedCode) {
                $q->where('license_key', $sanitizedCode)
                    ->orWhere('purchase_code', $sanitizedCode);
            })->first();
            // If license doesn't exist at all
            if (! $license) {
                return [
                    'success' => false,
                    'error' => 'invalid_code',
                    'message' => trans('license_status.license_code_invalid'),
                    'source' => 'database_raw',
                ];
            }
            // Check if license belongs to the correct product
            if ($productId && $license->product_id !== $productId) {
                return [
                    'success' => false,
                    'error' => 'wrong_product',
                    'message' => trans('license_status.license_code_not_for_product'),
                    'source' => 'database_raw',
                    'license_product_id' => $license->product_id,
                ];
            }
            // Check if license is active
            if ($license->status !== 'active') {
                $statusMessages = [
                    'inactive' => trans('license_status.license_inactive'),
                    'suspended' => trans('license_status.license_suspended'),
                    'expired' => trans('license_status.license_expired'),
                ];
                $message = $statusMessages[$license->status] ?? trans('license_status.license_inactive');

                return [
                    'success' => false,
                    'error' => 'license_status',
                    'message' => $message,
                    'license_status' => $license->status,
                    'source' => 'database_raw',
                ];
            }
            // Check if license is expired
            if ($license->license_expires_at && now()->greaterThan($license->license_expires_at)) {
                return [
                    'success' => false,
                    'error' => 'license_expired',
                    'message' => trans('license_status.license_expired'),
                    'expires_at' => $license->license_expires_at,
                    'source' => 'database_raw',
                ];
            }

            // All checks passed
            return [
                'success' => true,
                'license' => $license,
                'source' => 'database_raw',
                'product_id' => $license->product_id,
            ];
        } catch (\Exception $e) {
            Log::error('Error verifying raw code', [
                'raw_code_length' => strlen($rawCode),
                'product_id' => $productId ?? 'null',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function verifyAgainstEnvato(string $purchaseCode, ?int $productId = null, ?User $user = null): array
    {
        try {
            $envatoData = $this->envatoService->verifyPurchase($purchaseCode);
            if (! $envatoData || ! isset($envatoData['item']) || ! is_array($envatoData['item']) || ! isset($envatoData['item']['id'])) {
                return [
                    'success' => false,
                    'error' => 'Invalid Envato purchase code',
                    'source' => 'envato',
                ];
            }
            $itemId = $envatoData['item']['id'];
            $envatoItemId = is_string($itemId) ? $itemId : '';
            // If product ID is specified, verify it matches the Envato item
            if ($productId) {
                $product = Product::find($productId);
                if (! $product || ! $product->envato_item_id || $product->envato_item_id != $envatoItemId) {
                    return [
                        'success' => false,
                        'error' => 'Purchase code does not belong to this product',
                        'source' => 'envato',
                    ];
                }
            }
            // Create license record for authenticated user
            if ($user) {
                $this->createLicenseFromEnvato($user, $purchaseCode, $envatoData, $productId);
            }

            return [
                'success' => true,
                'envato_data' => $envatoData,
                'source' => 'envato',
                'product_id' => $productId,
            ];
        } catch (\Exception $e) {
            // Envato API error during purchase verification
            return [
                'success' => false,
                'error' => 'Failed to verify with Envato',
                'source' => 'envato',
            ];
        }
    }

    /**
     * Create license record from Envato data.
     */
    /**
     * @param array<mixed> $envatoData
     */
    protected function createLicenseFromEnvato(
        User $user,
        string $purchaseCode,
        array $envatoData,
        ?int $productId = null,
    ): ?License {
        // Check if license already exists
        $existingLicense = $user->licenses()
            ->where('purchase_code', $purchaseCode)
            ->first();
        if ($existingLicense) {
            return $existingLicense;
        }
        // Create new license
        $licenseData = [
            'user_id' => $user->id,
            'purchase_code' => $purchaseCode,
            'license_key' => 'envato_' . $purchaseCode,
            'license_type' => 'regular',
            'status' => 'active',
            'purchase_date' => data_get($envatoData, 'sold_at')
                ? date('Y-m-d H:i:s', strtotime(is_string(data_get($envatoData, 'sold_at')) ? data_get($envatoData, 'sold_at') : '') ?: time())
                : now(),
            'support_expires_at' => data_get($envatoData, 'supported_until')
                ? date('Y-m-d H:i:s', strtotime(is_string(data_get($envatoData, 'supported_until')) ? data_get($envatoData, 'supported_until') : '') ?: time())
                : null,
            'license_expires_at' => null, // Lifetime license
        ];
        // Add product ID if specified
        if ($productId) {
            $licenseData['product_id'] = $productId;
        }
        // Add user ID
        $licenseData['user_id'] = $user->id;
        try {
            return License::create($licenseData);
        } catch (\Exception $e) {
            // Failed to create license from Envato data
            return null;
        }
    }

    /**
     * Check if user has access to specific product via any license.
     */
    public function userHasProductAccess(User $user, int $productId): bool
    {
        return $user->licenses()
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Check if user has access to KB content via product license.
     */
    public function userHasKbAccess(User $user, \App\Models\KbArticle $kbItem): bool
    {
        // If KB item is not linked to any product, allow access
        if (! $kbItem->product_id) {
            return true;
        }

        // Check if user has license for the linked product
        return $this->userHasProductAccess($user, $kbItem->product_id);
    }

    /**
     * Get accessible KB content for user based on their licenses.
     */
    /**
     * @return array<string, mixed>
     */
    public function getAccessibleKbContent(User $user): array
    {
        $userProductIds = $user->licenses()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('license_expires_at')
                    ->orWhere('license_expires_at', '>', now());
            })
            ->pluck('product_id')
            ->toArray();

        return [
            'categories' => \App\Models\KbCategory::where(function ($query) use ($userProductIds) {
                $query->whereNull('product_id')
                    ->orWhereIn('product_id', $userProductIds);
            })->where('is_published', true)->get(),
            'articles' => \App\Models\KbArticle::where(function ($query) use ($userProductIds) {
                $query->whereNull('product_id')
                    ->orWhereIn('product_id', $userProductIds);
            })->where('is_published', true)->get(),
        ];
    }

    /**
     * Check if user has access to KB category via product license.
     */
    public function userHasCategoryAccess(User $user, \App\Models\KbCategory $category): bool
    {
        // If category is not linked to any product, allow access
        if (! $category->product_id) {
            return true;
        }

        // Check if user has license for the linked product
        return $this->userHasProductAccess($user, $category->product_id);
    }

    /**
     * Check if user has access to KB article considering both direct product and category product.
     */
    public function userHasArticleAccess(User $user, \App\Models\KbArticle $article): bool
    {
        // If article has direct product link, check that first
        if (isset($article->product_id) && $article->product_id && $article->product_id > 0) {
            return $this->userHasProductAccess($user, $article->product_id);
        }
        // If article doesn't have direct product but category does, check category access
        if ($article->category->product_id) {
            return $this->userHasCategoryAccess($user, $article->category);
        }

        // No product restrictions
        return true;
    }
}
