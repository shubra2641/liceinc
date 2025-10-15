<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Registration Validation Service - Handles license registration validation.
 */
class RegistrationValidationService
{
    /**
     * Validate purchase code.
     */
    public function validatePurchaseCode(string $purchaseCode): void
    {
        if (empty($purchaseCode)) {
            throw new \InvalidArgumentException('Purchase code is required');
        }

        if (strlen($purchaseCode) < 10) {
            throw new \InvalidArgumentException('Purchase code is too short');
        }

        if (strlen($purchaseCode) > 100) {
            throw new \InvalidArgumentException('Purchase code is too long');
        }

        if (!preg_match('/^[a-zA-Z0-9\-]+$/', $purchaseCode)) {
            throw new \InvalidArgumentException('Purchase code contains invalid characters');
        }
    }

    /**
     * Validate product ID.
     */
    public function validateProductId(?int $productId): void
    {
        if ($productId !== null && $productId <= 0) {
            throw new \InvalidArgumentException('Product ID must be positive');
        }
    }

    /**
     * Validate user authentication.
     */
    public function validateUserAuthentication(): User
    {
        $user = Auth::user();
        if (!$user) {
            throw new \InvalidArgumentException('User must be authenticated');
        }

        return $user;
    }

    /**
     * Check if license already exists.
     */
    public function checkExistingLicense(string $purchaseCode, int $userId): ?License
    {
        try {
            return License::where('purchase_code', $purchaseCode)
                ->where('user_id', $userId)
                ->first();
        } catch (\Exception $e) {
            Log::error('Failed to check existing license', [
                'error' => $e->getMessage(),
                'purchase_code' => $purchaseCode,
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Validate product exists.
     */
    public function validateProductExists(int $productId): Product
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                throw new \InvalidArgumentException('Product not found');
            }

            return $product;
        } catch (\Exception $e) {
            Log::error('Failed to validate product exists', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);
            throw $e;
        }
    }

    /**
     * Validate product stock.
     */
    public function validateProductStock(Product $product): void
    {
        if ($product->stock <= 0) {
            throw new \InvalidArgumentException('Product is out of stock');
        }
    }

    /**
     * Validate license creation data.
     */
    public function validateLicenseCreationData(array $data): array
    {
        $errors = [];

        if (!isset($data['purchase_code']) || empty($data['purchase_code'])) {
            $errors['purchase_code'] = 'Purchase code is required';
        }

        if (!isset($data['product_id']) || !is_numeric($data['product_id']) || $data['product_id'] <= 0) {
            $errors['product_id'] = 'Valid product ID is required';
        }

        if (!isset($data['user_id']) || !is_numeric($data['user_id']) || $data['user_id'] <= 0) {
            $errors['user_id'] = 'Valid user ID is required';
        }

        return $errors;
    }

    /**
     * Validate registration request.
     */
    public function validateRegistrationRequest(string $purchaseCode, ?int $productId): array
    {
        $errors = [];

        try {
            $this->validatePurchaseCode($purchaseCode);
        } catch (\InvalidArgumentException $e) {
            $errors['purchase_code'] = $e->getMessage();
        }

        try {
            $this->validateProductId($productId);
        } catch (\InvalidArgumentException $e) {
            $errors['product_id'] = $e->getMessage();
        }

        try {
            $this->validateUserAuthentication();
        } catch (\InvalidArgumentException $e) {
            $errors['user'] = $e->getMessage();
        }

        return $errors;
    }

    /**
     * Check if user can register license.
     */
    public function canUserRegisterLicense(int $userId, string $purchaseCode): array
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return [
                    'can_register' => false,
                    'reason' => 'User not found',
                ];
            }

            // Check if user is active
            if (!$user->is_active) {
                return [
                    'can_register' => false,
                    'reason' => 'User account is not active',
                ];
            }

            // Check if user has reached license limit
            $licenseCount = License::where('user_id', $userId)->count();
            $maxLicenses = $user->max_licenses ?? 10; // Default limit

            if ($licenseCount >= $maxLicenses) {
                return [
                    'can_register' => false,
                    'reason' => 'User has reached maximum license limit',
                ];
            }

            // Check if purchase code is already registered
            $existingLicense = $this->checkExistingLicense($purchaseCode, $userId);
            if ($existingLicense) {
                return [
                    'can_register' => false,
                    'reason' => 'Purchase code is already registered',
                ];
            }

            return [
                'can_register' => true,
                'reason' => 'User can register license',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check if user can register license', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'purchase_code' => $purchaseCode,
            ]);

            return [
                'can_register' => false,
                'reason' => 'Error checking registration eligibility',
            ];
        }
    }

    /**
     * Validate license data before creation.
     */
    public function validateLicenseData(array $licenseData): array
    {
        $errors = [];

        if (!isset($licenseData['purchase_code']) || empty($licenseData['purchase_code'])) {
            $errors['purchase_code'] = 'Purchase code is required';
        }

        if (!isset($licenseData['product_id']) || !is_numeric($licenseData['product_id'])) {
            $errors['product_id'] = 'Product ID is required';
        }

        if (!isset($licenseData['user_id']) || !is_numeric($licenseData['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        }

        if (isset($licenseData['expires_at']) && !strtotime($licenseData['expires_at'])) {
            $errors['expires_at'] = 'Invalid expiration date';
        }

        return $errors;
    }

    /**
     * Check if product is available for registration.
     */
    public function isProductAvailable(int $productId): array
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return [
                    'available' => false,
                    'reason' => 'Product not found',
                ];
            }

            if (!$product->is_active) {
                return [
                    'available' => false,
                    'reason' => 'Product is not active',
                ];
            }

            if ($product->stock <= 0) {
                return [
                    'available' => false,
                    'reason' => 'Product is out of stock',
                ];
            }

            return [
                'available' => true,
                'reason' => 'Product is available',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to check product availability', [
                'error' => $e->getMessage(),
                'product_id' => $productId,
            ]);

            return [
                'available' => false,
                'reason' => 'Error checking product availability',
            ];
        }
    }
}
