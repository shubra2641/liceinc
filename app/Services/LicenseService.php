<?php
declare(strict_types=1);
namespace App\Services;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
/**
 * License Service with enhanced security and comprehensive license management.
 *
 * This service provides comprehensive license management functionality including
 * license creation, validation, domain activation, and user license management
 * with enhanced security measures and error handling.
 *
 * Features:
 * - License creation and management for users and products
 * - License validation and domain activation
 * - User license checking and active license retrieval
 * - Product purchase eligibility validation
 * - License expiry and support period calculation
 * - Comprehensive error handling and logging
 * - Database transaction support for data integrity
 * - Enhanced security measures for license operations
 * - Input validation and sanitization
 *
 *
 * @example
 * // Create a license for a user and product
 * $license = $licenseService->createLicense($user, $product, 'stripe');
 *
 * // Check if user can purchase a product
 * $canPurchase = $licenseService->canUserPurchaseProduct($user, $product);
 *
 * // Validate a license key
 * $validation = $licenseService->isLicenseValid($licenseKey, $domain);
 */
class LicenseService
{
    /**
     * Create license for a user and product with enhanced security.
     *
     * Creates a new license for a user and product with comprehensive validation,
     * proper expiry calculation, and database transaction support.
     *
     * @param  User  $user  The user to create the license for
     * @param  Product  $product  The product to create the license for
     * @param  string|null  $paymentGateway  The payment gateway used for purchase (optional)
     *
     * @return License The created license instance
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     * @throws \Exception When database operations fail
     *
     * @example
     * $license = $licenseService->createLicense($user, $product, 'stripe');
     */
    public function createLicense(User $user, Product $product, ?string $paymentGateway = null): License
    {
        try {
            $this->validateCreateLicenseParameters($user, $product, $paymentGateway);
            return DB::transaction(function () use ($user, $product, $paymentGateway) {
                $license = License::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'license_type' => $product->license_type ?? 'single',
                    'status' => 'active',
                    'max_domains' => $product->max_domains ?? 1,
                    'license_expires_at' => $this->calculateLicenseExpiry($product),
                    'support_expires_at' => $this->calculateSupportExpiry($product),
                    'notes' => $paymentGateway
                        ? "Purchased via {$this->sanitizeInput($paymentGateway)}"
                        : 'Manual creation',
                ]);
                Log::debug('License created successfully', [
                    'license_id' => $license->id,
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'payment_gateway' => $paymentGateway ? $this->hashForLogging($paymentGateway) : null,
                ]);
                return $license;
            });
        } catch (Throwable $e) {
            Log::error('Failed to create license', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown',
                'product_id' => $product->id ?? 'unknown',
                'payment_gateway' => $paymentGateway ? $this->hashForLogging($paymentGateway) : null,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate license expiry date with enhanced validation.
     *
     * Calculates the license expiry date based on product configuration
     * with proper handling of lifetime licenses and renewal periods.
     *
     * @param  Product  $product  The product to calculate expiry for
     *
     * @return Carbon|null The expiry date or null for lifetime licenses
     *
     * @throws \InvalidArgumentException When invalid product is provided
     *
     * @example
     * $expiry = $licenseService->calculateLicenseExpiry($product);
     */
    protected function calculateLicenseExpiry(Product $product): ?Carbon
    {
        try {
            if (! $product) {
                throw new \InvalidArgumentException('Product cannot be null');
            }
            // If product has lifetime license or renewal_period is lifetime, return null (no expiry)
            if ($product->license_type === 'lifetime' || $product->renewal_period === 'lifetime') {
                return null;
            }
            // Calculate expiry based on renewal_period from product
            $days = $this->getRenewalPeriodInDays($product->renewal_period);
            // If no valid renewal period, use default from settings
            if ($days === null) {
                $days = \App\Helpers\ConfigHelper::getSetting('license_default_duration', 365);
            }
            return now()->addDays($days);
        } catch (Throwable $e) {
            Log::error('Failed to calculate license expiry', [
                'error' => $e->getMessage(),
                'product_id' => $product->id ?? 'unknown',
                'license_type' => $product->license_type ?? 'unknown',
                'renewal_period' => $product->renewal_period ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate support expiry date with enhanced validation.
     *
     * Calculates the support expiry date based on product configuration
     * with proper validation and error handling.
     *
     * @param  Product  $product  The product to calculate support expiry for
     *
     * @return Carbon The support expiry date
     *
     * @throws \InvalidArgumentException When invalid product is provided
     *
     * @example
     * $supportExpiry = $licenseService->calculateSupportExpiry($product);
     */
    protected function calculateSupportExpiry(Product $product): Carbon
    {
        try {
            if (! $product) {
                throw new \InvalidArgumentException('Product cannot be null');
            }
            // Use product's support_days or default from settings
            $supportDuration = $product->support_days
                ?? \App\Helpers\ConfigHelper::getSetting('license_support_duration', 365);
            // Calculate support expiry based on duration in days
            return now()->addDays($supportDuration);
        } catch (Throwable $e) {
            Log::error('Failed to calculate support expiry', [
                'error' => $e->getMessage(),
                'product_id' => $product->id ?? 'unknown',
                'support_days' => $product->support_days ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Convert renewal period to days with enhanced validation.
     *
     * Converts renewal period strings to days with comprehensive
     * validation and error handling.
     *
     * @param  string|null  $renewalPeriod  The renewal period string
     *
     * @return int|null The number of days or null for lifetime
     *
     * @example
     * $days = $licenseService->getRenewalPeriodInDays('annual');
     */
    protected function getRenewalPeriodInDays(?string $renewalPeriod): ?int
    {
        try {
            if ($renewalPeriod === null) {
                return null;
            }
            return match ($renewalPeriod) {
                'monthly' => 30,
                'quarterly' => 90,
                'semi-annual' => 180,
                'annual' => 365,
                'three-years' => 1095, // 3 years
                'lifetime' => null, // No expiry
                default => null,
            };
        } catch (Throwable $e) {
            Log::error('Failed to convert renewal period to days', [
                'error' => $e->getMessage(),
                'renewal_period' => $renewalPeriod,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    /**
     * Check if user can purchase product with enhanced validation.
     *
     * Validates if a user can purchase a specific product with comprehensive
     * checks for existing licenses, product availability, and pricing.
     *
     * @param  User  $user  The user to check
     * @param  Product  $product  The product to check
     *
     * @return array<string, mixed> Array containing purchase eligibility information
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $canPurchase = $licenseService->canUserPurchaseProduct($user, $product);
     */
    public function canUserPurchaseProduct(User $user, Product $product): array
    {
        try {
            $this->validateCanPurchaseParameters($user, $product);
            // Check if user already owns this product
            $existingLicense = $user->licenses()
                ->where('product_id', $product->id)
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('license_expires_at')
                        ->orWhere('license_expires_at', '>', now());
                })
                ->first();
            if ($existingLicense) {
                return [
                    'can_purchase' => false,
                    'reason' => 'already_owned',
                    'message' => trans('app.You already own this product'),
                ];
            }
            // Check if product is available for purchase
            if (! $product->is_active) {
                return [
                    'can_purchase' => false,
                    'reason' => 'product_inactive',
                    'message' => trans('app.Product is not available for purchase'),
                ];
            }
            // Check if product has a price
            if ($product->price <= 0) {
                return [
                    'can_purchase' => false,
                    'reason' => 'free_product',
                    'message' => trans('app.This product is free and does not require purchase'),
                ];
            }
            return [
                'can_purchase' => true,
                'reason' => 'available',
                'message' => trans('app.Product is available for purchase'),
            ];
        } catch (Throwable $e) {
            Log::error('Failed to check user purchase eligibility', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown',
                'product_id' => $product->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'can_purchase' => false,
                'reason' => 'error',
                'message' => trans('app.An error occurred while checking purchase eligibility'),
            ];
        }
    }
    /**
     * Get user's active licenses with enhanced security.
     *
     * Retrieves all active licenses for a user with comprehensive
     * validation and error handling.
     *
     * @param  User  $user  The user to get licenses for
     *
     * @return Collection<License> Collection of active licenses
     *
     * @throws \InvalidArgumentException When invalid user is provided
     *
     * @example
     * $licenses = $licenseService->getUserActiveLicenses($user);
     */
    public function getUserActiveLicenses(User $user): Collection
    {
        try {
            if (! $user) {
                throw new \InvalidArgumentException('User cannot be null');
            }
            return $user->licenses()
                ->with('product')
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('license_expires_at')
                        ->orWhere('license_expires_at', '>', now());
                })
                ->get();
        } catch (Throwable $e) {
            Log::error('Failed to get user active licenses', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Check if license is valid with enhanced security.
     *
     * Validates a license key with comprehensive checks for existence,
     * status, expiry, and domain authorization.
     *
     * @param  string  $licenseKey  The license key to validate
     * @param  string|null  $domain  The domain to check authorization for (optional)
     *
     * @return array<string, mixed> Array containing validation results
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $validation = $licenseService->isLicenseValid($licenseKey, $domain);
     */
    public function isLicenseValid(string $licenseKey, ?string $domain = null): array
    {
        try {
            $this->validateLicenseKey($licenseKey);
            $license = License::where('license_key', $this->sanitizeInput($licenseKey))
                ->with('product', 'user')
                ->first();
            if (! $license) {
                Log::warning('License validation failed: License key not found', [
                    'license_key' => $this->hashForLogging($licenseKey),
                    'domain' => $domain ? $this->hashForLogging($domain) : null,
                ]);
                return [
                    'valid' => false,
                    'message' => 'License key not found',
                ];
            }
            if ($license->status !== 'active') {
                Log::warning('License validation failed: License is not active', [
                    'license_id' => $license->id,
                    'status' => $license->status,
                    'domain' => $domain ? $this->hashForLogging($domain) : null,
                ]);
                return [
                    'valid' => false,
                    'message' => 'License is not active',
                ];
            }
            if ($license->license_expires_at && $license->license_expires_at->isPast()) {
                Log::warning('License validation failed: License has expired', [
                    'license_id' => $license->id,
                    'expires_at' => $license->license_expires_at->toISOString(),
                    'domain' => $domain ? $this->hashForLogging($domain) : null,
                ]);
                return [
                    'valid' => false,
                    'message' => 'License has expired',
                ];
            }
            // Check domain if provided
            if ($domain && $license->license_domains) {
                $allowedDomains = json_decode($license->license_domains, true);
                if (! in_array($this->sanitizeDomain($domain), $allowedDomains)) {
                    Log::warning('License validation failed: Domain not authorized', [
                        'license_id' => $license->id,
                        'domain' => $this->hashForLogging($domain),
                        'allowed_domains_count' => count($allowedDomains),
                    ]);
                    return [
                        'valid' => false,
                        'message' => 'Domain not authorized for this license',
                    ];
                }
            }
            return [
                'valid' => true,
                'license' => $license,
                'message' => 'License is valid',
            ];
        } catch (Throwable $e) {
            Log::error('License validation exception', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'domain' => $domain ? $this->hashForLogging($domain) : null,
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'valid' => false,
                'message' => 'An error occurred during license validation',
            ];
        }
    }
    /**
     * Activate license for domain with enhanced security.
     *
     * Activates a license for a specific domain with comprehensive
     * validation and database transaction support.
     *
     * @param  string  $licenseKey  The license key to activate
     * @param  string  $domain  The domain to activate for
     *
     * @return array<string, mixed> Array containing activation results
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $activation = $licenseService->activateLicenseForDomain($licenseKey, $domain);
     */
    public function activateLicenseForDomain(string $licenseKey, string $domain): array
    {
        try {
            $this->validateActivationParameters($licenseKey, $domain);
            return DB::transaction(function () use ($licenseKey, $domain) {
                $license = License::where('license_key', $this->sanitizeInput($licenseKey))->first();
                if (! $license) {
                    Log::warning('License activation failed: License key not found', [
                        'license_key' => $this->hashForLogging($licenseKey),
                        'domain' => $this->hashForLogging($domain),
                    ]);
                    return [
                        'success' => false,
                        'message' => 'License key not found',
                    ];
                }
                if ($license->status !== 'active') {
                    Log::warning('License activation failed: License is not active', [
                        'license_id' => $license->id,
                        'status' => $license->status,
                        'domain' => $this->hashForLogging($domain),
                    ]);
                    return [
                        'success' => false,
                        'message' => 'License is not active',
                    ];
                }
                // Get current domains
                $currentDomains = $license->license_domains ? json_decode($license->license_domains, true) : [];
                $sanitizedDomain = $this->sanitizeDomain($domain);
                // Add new domain if not already present
                if (! in_array($sanitizedDomain, $currentDomains)) {
                    $currentDomains[] = $sanitizedDomain;
                    $license->update([
                        'license_domains' => json_encode($currentDomains),
                    ]);
                    Log::debug('License activated for domain', [
                        'license_id' => $license->id,
                        'domain' => $this->hashForLogging($domain),
                        'total_domains' => count($currentDomains),
                    ]);
                }
                return [
                    'success' => true,
                    'message' => 'License activated for domain',
                    'domains' => $currentDomains,
                ];
            });
        } catch (Throwable $e) {
            Log::error('License activation exception', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'domain' => $this->hashForLogging($domain),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'An error occurred during license activation',
            ];
        }
    }
    /**
     * Validate create license parameters.
     *
     * @param  User  $user  The user
     * @param  Product  $product  The product
     * @param  string|null  $paymentGateway  The payment gateway
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateCreateLicenseParameters(User $user, Product $product, ?string $paymentGateway): void
    {
        if (! $user) {
            throw new \InvalidArgumentException('User cannot be null');
        }
        if (! $product) {
            throw new \InvalidArgumentException('Product cannot be null');
        }
        if ($paymentGateway && empty(trim($paymentGateway))) {
            throw new \InvalidArgumentException('Payment gateway cannot be empty');
        }
    }
    /**
     * Validate can purchase parameters.
     *
     * @param  User  $user  The user
     * @param  Product  $product  The product
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateCanPurchaseParameters(User $user, Product $product): void
    {
        if (! $user) {
            throw new \InvalidArgumentException('User cannot be null');
        }
        if (! $product) {
            throw new \InvalidArgumentException('Product cannot be null');
        }
    }
    /**
     * Validate license key parameter.
     *
     * @param  string  $licenseKey  The license key
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateLicenseKey(string $licenseKey): void
    {
        if (empty(trim($licenseKey))) {
            throw new \InvalidArgumentException('License key cannot be empty');
        }
    }
    /**
     * Validate activation parameters.
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $domain  The domain
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateActivationParameters(string $licenseKey, string $domain): void
    {
        if (empty(trim($licenseKey))) {
            throw new \InvalidArgumentException('License key cannot be empty');
        }
        if (empty(trim($domain))) {
            throw new \InvalidArgumentException('Domain cannot be empty');
        }
        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Invalid domain format');
        }
    }
    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  string  $input  The input to sanitize
     *
     * @return string The sanitized input
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Sanitize domain for security.
     *
     * @param  string  $domain  The domain to sanitize
     *
     * @return string The sanitized domain
     */
    private function sanitizeDomain(string $domain): string
    {
        return strtolower(trim($domain));
    }
    /**
     * Hash data for logging.
     *
     * @param  string  $data  The data to hash
     *
     * @return string The hashed data
     */
    private function hashForLogging(string $data): string
    {
        return substr(hash('sha256', $data.config('app.key')), 0, 8).'...';
    }
}
