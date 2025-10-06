<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\License;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * License Auto Registration Service with enhanced security and comprehensive license management.
 *
 * This service provides automated license registration functionality for users,
 * including purchase code verification, license creation, invoice generation,
 * and stock management. It implements comprehensive security measures,
 * input validation, and error handling for reliable license operations.
 */
class LicenseAutoRegistrationService
{
    protected PurchaseCodeService $purchaseCodeService;
    protected InvoiceService $invoiceService;
    /**
     * Constructor with dependency injection and enhanced error handling.
     *
     * Initializes the service with required dependencies for purchase code
     * verification and invoice management. Includes proper type hints and
     * validation for dependency injection.
     *
     * @param  PurchaseCodeService  $purchaseCodeService  Service for purchase code verification
     * @param  InvoiceService  $invoiceService  Service for invoice management
     */
    public function __construct(PurchaseCodeService $purchaseCodeService, InvoiceService $invoiceService)
    {
        try {
            $this->purchaseCodeService = $purchaseCodeService;
            $this->invoiceService = $invoiceService;
        } catch (\Exception $e) {
            Log::error('Failed to initialize LicenseAutoRegistrationService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Automatically register a license for the authenticated user with enhanced security and error handling.
     *
     * Registers a license for the authenticated user if the purchase code is valid
     * and not already registered. Includes comprehensive validation, security measures,
     * database transactions, and error handling for reliable license registration.
     *
     * @param  string  $purchaseCode  The purchase code to register
     * @param  int|null  $productId  Optional product ID for the license
     *
     * @return array<string, mixed> Registration result with success status, license object, and message
     *
     * @throws InvalidArgumentException When purchase code is invalid
     * @throws \Exception When license registration fails
     *
     * @example
     * $result = $service->autoRegisterLicense('ABC123DEF456', 1);
     * if ($result['success']) {
     *     $license = $result['license'];
     * }
     */
    public function autoRegisterLicense(string $purchaseCode, ?int $productId = null): array
    {
        try {
            // Validate input parameters
            $this->validatePurchaseCode($purchaseCode);
            $this->validateProductId($productId);
            $user = Auth::user();
            if (! $user) {
                return [
                    'success' => false,
                    'license' => null,
                    'message' => 'User must be authenticated',
                ];
            }
            return DB::transaction(function () use ($purchaseCode, $productId, $user) {
                // Check if user already has this license
                $existingLicense = $this->findExistingLicense($purchaseCode, $user->id);
                if ($existingLicense) {
                    return [
                        'success' => true,
                        'license' => $existingLicense,
                        'message' => 'License already exists for this user',
                    ];
                }
                // Verify the purchase code
                $verificationResult = $this->purchaseCodeService->verifyRawCode($purchaseCode, $productId);
                if (! $verificationResult['success']) {
                    return [
                        'success' => false,
                        'license' => null,
                        'message' => $verificationResult['message'] ?? 'Invalid purchase code',
                    ];
                }
                // Determine product ID from verification result or provided parameter
                $licenseProductId = $this->determineProductId($productId, $verificationResult);
                if (! $licenseProductId) {
                    return [
                        'success' => false,
                        'license' => null,
                        'message' => 'Could not determine product for this purchase code',
                    ];
                }
                // Verify product exists
                $product = $this->findProduct($licenseProductId);
                if (! $product) {
                    return [
                        'success' => false,
                        'license' => null,
                        'message' => 'Product not found',
                    ];
                }
                // Create the license
                $license = $this->createLicense($purchaseCode, $licenseProductId, $user->id, $verificationResult);
                // Create initial invoice
                $this->createInitialInvoice($license);
                // Decrease product stock
                $this->decreaseProductStock($product);
                return [
                    'success' => true,
                    'license' => $license,
                    'message' => 'License registered successfully',
                ];
            });
        } catch (\Exception $e) {
            Log::error('Failed to auto-register license', [
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Check if a purchase code is valid without registering it with enhanced security and error handling.
     *
     * Validates a purchase code without creating a license record. Includes
     * comprehensive validation, security measures, and error handling for
     * reliable purchase code verification.
     *
     * @param  string  $purchaseCode  The purchase code to validate
     * @param  int|null  $productId  Optional product ID for validation
     *
     * @return array Validation result with validity status, message, and existing license
     *
     * @throws InvalidArgumentException When purchase code is invalid
     * @throws \Exception When purchase code validation fails
     *
     * @example
     * $result = $service->checkPurchaseCode('ABC123DEF456', 1);
     * if ($result['valid']) {
     *     // Purchase code is valid
     * }
     */
    /**
     * @return array<string, mixed>
     */
    public function checkPurchaseCode(string $purchaseCode, ?int $productId = null): array
    {
        try {
            // Validate input parameters
            $this->validatePurchaseCode($purchaseCode);
            $this->validateProductId($productId);
            $user = Auth::user();
            if (! $user) {
                return [
                    'valid' => false,
                    'message' => 'User must be authenticated',
                    'existing_license' => null,
                ];
            }
            // Check if user already has this license
            $existingLicense = $this->findExistingLicense($purchaseCode, $user->id);
            if ($existingLicense) {
                return [
                    'valid' => true,
                    'message' => 'License already exists for this user',
                    'existing_license' => $existingLicense,
                ];
            }
            // Verify the purchase code
            $verificationResult = $this->purchaseCodeService->verifyRawCode($purchaseCode, $productId);
            if (! $verificationResult['success']) {
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
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Validate purchase code format and content with enhanced security.
     *
     * @param  string  $purchaseCode  The purchase code to validate
     *
     * @throws InvalidArgumentException When purchase code is invalid
     */
    private function validatePurchaseCode(string $purchaseCode): void
    {
        if (empty($purchaseCode)) {
            throw new InvalidArgumentException('Purchase code cannot be empty');
        }
        if (strlen($purchaseCode) < 8 || strlen($purchaseCode) > 100) {
            throw new InvalidArgumentException('Purchase code must be between 8 and 100 characters');
        }
        // Basic format validation
        if (! preg_match('/^[A-Za-z0-9\-_]+$/', $purchaseCode)) {
            throw new InvalidArgumentException('Purchase code contains invalid characters');
        }
    }
    /**
     * Validate product ID with enhanced security.
     *
     * @param  int|null  $productId  The product ID to validate
     *
     * @throws InvalidArgumentException When product ID is invalid
     */
    private function validateProductId(?int $productId): void
    {
        if ($productId !== null && ($productId < 1 || $productId > 999999)) {
            throw new InvalidArgumentException('Product ID must be between 1 and 999999');
        }
    }
    /**
     * Find existing license for user with enhanced error handling.
     *
     * @param  string  $purchaseCode  The purchase code to search for
     * @param  int  $userId  The user ID to search for
     *
     * @return License|null The existing license or null
     *
     * @throws \Exception When database query fails
     */
    private function findExistingLicense(string $purchaseCode, int $userId): ?License
    {
        try {
            return License::where('purchase_code', $purchaseCode)
                ->where('user_id', $userId)
                ->first();
        } catch (\Exception $e) {
            Log::error('Failed to find existing license', [
                'purchase_code' => $purchaseCode,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Determine product ID from verification result or provided parameter.
     *
     * @param  int|null  $productId  Provided product ID
     * @param  array  $verificationResult  Verification result from service
     *
     * @return int|null Determined product ID
     */
    /**
     * @param array<string, mixed> $verificationResult
     */
    private function determineProductId(?int $productId, array $verificationResult): ?int
    {
        if ($productId) {
            return $productId;
        }
        return $verificationResult['product_id'] ?? null;
    }
    /**
     * Find product by ID with enhanced error handling.
     *
     * @param  int  $productId  The product ID to find
     *
     * @return Product|null The product or null
     *
     * @throws \Exception When database query fails
     */
    private function findProduct(int $productId): ?Product
    {
        try {
            return Product::find($productId);
        } catch (\Exception $e) {
            Log::error('Failed to find product', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Create license with enhanced security and error handling.
     *
     * @param  string  $purchaseCode  The purchase code
     * @param  int  $productId  The product ID
     * @param  int  $userId  The user ID
     * @param  array  $verificationResult  Verification result data
     *
     * @return License The created license
     *
     * @throws \Exception When license creation fails
     */
    /**
     * @param array<string, mixed> $verificationResult
     */
    private function createLicense(
        string $purchaseCode,
        int $productId,
        int $userId,
        array $verificationResult,
    ): License {
        try {
            $licenseData = [
                'purchase_code' => htmlspecialchars($purchaseCode, ENT_QUOTES, 'UTF-8'),
                'product_id' => $productId,
                'user_id' => $userId,
                'license_type' => 'regular',
                'status' => 'active',
                'support_expires_at' => $verificationResult['support_expires_at'] ?? null,
            ];
            return License::create($licenseData);
        } catch (\Exception $e) {
            Log::error('Failed to create license', [
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Create initial invoice with enhanced error handling.
     *
     * @param  License  $license  The license to create invoice for
     *
     * @throws \Exception When invoice creation fails
     */
    private function createInitialInvoice(License $license): void
    {
        try {
            $this->invoiceService->createInitialInvoice($license);
        } catch (\Exception $e) {
            Log::error('Failed to create initial invoice', [
                'license_id' => $license->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Decrease product stock with enhanced error handling.
     *
     * @param  Product  $product  The product to decrease stock for
     *
     * @throws \Exception When stock decrease fails
     */
    private function decreaseProductStock(Product $product): void
    {
        try {
            $product->decreaseStock();
        } catch (\Exception $e) {
            Log::error('Failed to decrease product stock', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
