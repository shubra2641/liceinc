<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * License Management Service - Handles license management operations.
 */
class LicenseManagementService
{
    public function __construct(
        private LicenseValidationService $validationService,
        private LicenseCalculationService $calculationService
    ) {
    }

    /**
     * Create license for user and product.
     */
    public function createLicense(User $user, Product $product, ?string $paymentGateway = null): License
    {
        try {
            $this->validationService->validateCreateLicenseParameters($user, $product, $paymentGateway);

            return DB::transaction(function () use ($user, $product, $paymentGateway) {
                $license = License::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'license_type' => $product->license_type ?? 'single',
                    'status' => 'active',
                    'max_domains' => $product->max_domains ?? 1,
                    'license_expires_at' => $this->calculationService->calculateLicenseExpiry($product),
                    'support_expires_at' => $this->calculationService->calculateSupportExpiry($product),
                    'notes' => $paymentGateway
                        ? "Purchased via {$this->validationService->sanitizeInput($paymentGateway)}"
                        : 'Manual creation',
                ]);

                Log::debug('License created successfully', [
                    'license_id' => $license->id,
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'payment_gateway' => $paymentGateway ? $this->validationService->hashForLogging($paymentGateway) : null,
                ]);

                return $license;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create license', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown',
                'product_id' => $product->id ?? 'unknown',
                'payment_gateway' => $paymentGateway ? $this->validationService->hashForLogging($paymentGateway) : null,
            ]);
            throw $e;
        }
    }

    /**
     * Get user licenses.
     */
    public function getUserLicenses(User $user, bool $activeOnly = true): Collection
    {
        try {
            $query = $user->licenses()->with('product');

            if ($activeOnly) {
                $query->where('status', 'active');
            }

            return $query->orderBy('created_at', 'desc')->get();
        } catch (\Exception $e) {
            Log::error('Failed to get user licenses', [
                'user_id' => $user->id,
                'active_only' => $activeOnly,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get active user license for product.
     */
    public function getActiveUserLicenseForProduct(User $user, Product $product): ?License
    {
        try {
            return $user->licenses()
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->first();
        } catch (\Exception $e) {
            Log::error('Failed to get active user license for product', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update license status.
     */
    public function updateLicenseStatus(License $license, string $status): bool
    {
        try {
            $license->update(['status' => $status]);

            Log::info('License status updated', [
                'license_id' => $license->id,
                'new_status' => $status
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update license status', [
                'license_id' => $license->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add authorized domain to license.
     */
    public function addAuthorizedDomain(License $license, string $domain): bool
    {
        try {
            $this->validationService->validateDomain($domain);

            $authorizedDomains = $license->authorized_domains ?? [];

            if (!in_array($domain, $authorizedDomains)) {
                $authorizedDomains[] = $domain;
                $license->update(['authorized_domains' => $authorizedDomains]);

                Log::info('Authorized domain added to license', [
                    'license_id' => $license->id,
                    'domain' => $this->validationService->hashForLogging($domain)
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to add authorized domain', [
                'license_id' => $license->id,
                'domain' => $this->validationService->hashForLogging($domain),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Remove authorized domain from license.
     */
    public function removeAuthorizedDomain(License $license, string $domain): bool
    {
        try {
            $authorizedDomains = $license->authorized_domains ?? [];
            $key = array_search($domain, $authorizedDomains);

            if ($key !== false) {
                unset($authorizedDomains[$key]);
                $license->update(['authorized_domains' => array_values($authorizedDomains)]);

                Log::info('Authorized domain removed from license', [
                    'license_id' => $license->id,
                    'domain' => $this->validationService->hashForLogging($domain)
                ]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to remove authorized domain', [
                'license_id' => $license->id,
                'domain' => $this->validationService->hashForLogging($domain),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if user can purchase product.
     */
    public function canUserPurchaseProduct(User $user, Product $product): array
    {
        try {
            return $this->validationService->validateUserCanPurchaseProduct($user, $product);
        } catch (\Exception $e) {
            Log::error('Failed to check if user can purchase product', [
                'user_id' => $user->id,
                'product_id' => $product->id,
                'error' => $e->getMessage()
            ]);

            return [
                'can_purchase' => false,
                'reason' => 'Error checking purchase eligibility'
            ];
        }
    }
}
