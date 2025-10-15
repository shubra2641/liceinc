<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * License Validation Service - Handles validation for license operations.
 */
class LicenseValidationService
{
    /**
     * Validate create license parameters.
     */
    public function validateCreateLicenseParameters(User $user, Product $product, ?string $paymentGateway = null): void
    {
        if (!$user || !$user->id) {
            throw new InvalidArgumentException('Valid user is required');
        }

        if (!$product || !$product->id) {
            throw new InvalidArgumentException('Valid product is required');
        }

        if ($paymentGateway && !in_array($paymentGateway, ['stripe', 'paypal', 'manual'])) {
            throw new InvalidArgumentException('Invalid payment gateway');
        }
    }

    /**
     * Validate license key.
     */
    public function validateLicenseKey(string $licenseKey): void
    {
        if (empty($licenseKey)) {
            throw new InvalidArgumentException('License key is required');
        }

        if (strlen($licenseKey) < 10) {
            throw new InvalidArgumentException('License key must be at least 10 characters');
        }

        if (strlen($licenseKey) > 100) {
            throw new InvalidArgumentException('License key must not exceed 100 characters');
        }
    }

    /**
     * Validate domain.
     */
    public function validateDomain(?string $domain): void
    {
        if ($domain && !filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new InvalidArgumentException('Invalid domain format');
        }
    }

    /**
     * Validate license status.
     */
    public function validateLicenseStatus(License $license): array
    {
        if ($license->status !== 'active') {
            return [
                'valid' => false,
                'message' => 'License is not active',
                'status' => $license->status
            ];
        }

        if ($license->license_expires_at && $license->license_expires_at->isPast()) {
            return [
                'valid' => false,
                'message' => 'License has expired',
                'expires_at' => $license->license_expires_at
            ];
        }

        return [
            'valid' => true,
            'message' => 'License is valid'
        ];
    }

    /**
     * Validate domain authorization.
     */
    public function validateDomainAuthorization(License $license, ?string $domain): array
    {
        if (!$domain) {
            return [
                'authorized' => true,
                'message' => 'No domain specified'
            ];
        }

        $authorizedDomains = $license->authorized_domains ?? [];

        if (empty($authorizedDomains)) {
            return [
                'authorized' => true,
                'message' => 'No domain restrictions'
            ];
        }

        if (in_array($domain, $authorizedDomains)) {
            return [
                'authorized' => true,
                'message' => 'Domain is authorized'
            ];
        }

        return [
            'authorized' => false,
            'message' => 'Domain is not authorized',
            'authorized_domains' => $authorizedDomains
        ];
    }

    /**
     * Validate user can purchase product.
     */
    public function validateUserCanPurchaseProduct(User $user, Product $product): array
    {
        // Check if user already has active license for this product
        $existingLicense = $user->licenses()
            ->where('product_id', $product->id)
            ->where('status', 'active')
            ->first();

        if ($existingLicense) {
            return [
                'can_purchase' => false,
                'reason' => 'User already has an active license for this product',
                'existing_license_id' => $existingLicense->id
            ];
        }

        // Check if product is available for purchase
        if (!$product->is_available) {
            return [
                'can_purchase' => false,
                'reason' => 'Product is not available for purchase'
            ];
        }

        return [
            'can_purchase' => true,
            'reason' => 'User can purchase this product'
        ];
    }

    /**
     * Sanitize input.
     */
    public function sanitizeInput(string $input): string
    {
        return trim(strip_tags($input));
    }

    /**
     * Hash for logging.
     */
    public function hashForLogging(string $input): string
    {
        return substr(hash('sha256', $input), 0, 8);
    }
}
