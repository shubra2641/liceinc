<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LicenseRegisterRequest;
use App\Http\Requests\Api\LicenseStatusRequest;
use App\Http\Requests\Api\LicenseVerifyRequest;
use App\Models\License;
use App\Models\Product;
use App\Services\EnvatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * License API Controller.
 *
 * Provides comprehensive license management API endpoints with Envato integration,
 * domain verification, and advanced security features.
 *
 * Features:
 * - License verification with database and Envato API fallback
 * - License registration and status checking
 * - Domain verification and auto-registration
 * - API token authentication with enhanced security
 * - Comprehensive error handling with database transactions
 * - Support for multiple license types and domain limits
 * - Automatic license creation from Envato purchases
 * - Enhanced security measures (XSS protection, input validation)
 * - Rate limiting and CSRF protection
 * - Proper logging for errors and warnings only
 *
 *
 * @example
 * // Verify a license
 * POST /api/license/verify
 * {
 *     "purchase_code": "ABC123-DEF456-GHI789",
 *     "product_slug": "my-product",
 *     "domain": "example.com"
 * }
 */
class LicenseApiController extends Controller
{
    protected EnvatoService $envatoService;

    public function __construct(EnvatoService $envatoService)
    {
        $this->envatoService = $envatoService;
    }

    /**
     * Get API token from database settings.
     */
    private function getApiToken(): string
    {
        return \App\Helpers\ConfigHelper::getSetting('license_api_token', '', 'LICENSE_API_TOKEN');
    }

    /**
     * Verify license endpoint with enhanced security.
     *
     * This endpoint is used by the generated license files to verify licenses
     * with comprehensive validation, security measures, and proper error handling.
     *
     * @param  LicenseVerifyRequest  $request  The validated request containing license verification data
     *
     * @return JsonResponse Response with license verification result
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     */
    public function verify(LicenseVerifyRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Check authorization header
            $authHeader = $request->header('Authorization');
            $expectedToken = 'Bearer '.$this->getApiToken();
            if (! $expectedToken || $authHeader !== $expectedToken) {
                Log::warning('Unauthorized license verification attempt', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'headers' => $request->headers->all(),
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'Unauthorized',
                    'error_code' => 'UNAUTHORIZED',
                ], 401);
            }
            $validated = $request->validated();
            // Get validated and sanitized data from Request class
            $purchaseCode = $validated['purchase_code'];
            $productSlug = $validated['product_slug'];
            $domain = $validated['domain'] ?? null;
            $verificationKey = $validated['verification_key'] ?? null;
            // Find product by slug
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                Log::warning('Product not found during license verification', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND',
                ], 404);
            }
            // Verify verification key if provided
            if ($verificationKey && ! $this->verifyVerificationKey($product, $verificationKey)) {
                Log::warning('Invalid verification key during license verification', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'Invalid verification key',
                    'error_code' => 'INVALID_VERIFICATION_KEY',
                ], 403);
            }
            // Step 1: Check if license exists in our database
            $license = License::where('purchase_code', $purchaseCode)
                ->where('product_id', $product->id)
                ->first();
            $databaseValid = false;
            $envatoValid = false;
            $verificationMethod = '';
            // Step 2: Check database license first
            if ($license) {
                // License exists, check its status
                if ($license->status === 'active') {
                    // Check if license has expired
                    if ($license->license_expires_at && $license->license_expires_at->isPast()) {
                        $this->logApiVerificationAttempt($request, false, 'License has expired', 'api');

                        return response()->json([
                            'valid' => false,
                            'message' => 'License has expired',
                            'error_code' => 'LICENSE_EXPIRED',
                            'data' => [
                                'expires_at' => $license->license_expires_at->toISOString(),
                            ],
                        ], 403);
                    }
                    $databaseValid = true;
                    $verificationMethod = 'database_only';
                } elseif ($license->status === 'suspended') {
                    $this->logApiVerificationAttempt($request, false, 'License is suspended', 'api');

                    return response()->json([
                        'valid' => false,
                        'message' => 'License is suspended',
                        'error_code' => 'LICENSE_SUSPENDED',
                    ], 403);
                } else {
                    $this->logApiVerificationAttempt($request, false, 'License is not active', 'api');

                    return response()->json([
                        'valid' => false,
                        'message' => 'License is not active',
                        'error_code' => 'LICENSE_INACTIVE',
                    ], 403);
                }
            } else {
                // Step 3: If not in database, try Envato API
                $envatoData = $this->envatoService->verifyPurchase($purchaseCode);
                if (
                    $envatoData && is_array($envatoData) && isset($envatoData['item']) && is_array($envatoData['item']) && isset($envatoData['item']['id'])
                    && $envatoData['item']['id'] == $product->envato_item_id
                ) {
                    $envatoValid = true;
                    // Create license automatically from Envato
                    $license = $this->createLicenseFromEnvato($product, $purchaseCode, $envatoData);
                    $databaseValid = true;
                    $verificationMethod = 'envato_auto_created';
                } else {
                    // Both invalid - reject and log error
                    $this->logApiVerificationAttempt($request, false, 'License not found', 'api');

                    return response()->json([
                        'valid' => false,
                        'message' => 'License not found',
                        'error_code' => 'LICENSE_NOT_FOUND',
                    ], 404);
                }
            }
            // Step 5: Handle domain registration/verification
            if ($domain) {
                // Check if auto domain registration is enabled
                $autoRegisterDomains = \App\Helpers\ConfigHelper::getSetting('license_auto_register_domains', false);
                $isTestMode = config('app.env') === 'local' || config('app.debug') === true;
                if ($autoRegisterDomains || $isTestMode) {
                    // Auto register mode: Register domain automatically
                    try {
                        $this->registerDomainForLicense($license, $domain);
                        $this->logApiVerificationAttempt($request, true, 'Domain registered automatically', 'api', [
                            'domain' => $domain,
                            'license_id' => $license->id,
                            'mode' => $isTestMode ? 'test' : 'auto_register',
                        ]);
                    } catch (\Exception $e) {
                        // Domain limit exceeded
                        $this->logApiVerificationAttempt($request, false, $e->getMessage(), 'api');

                        return response()->json([
                            'valid' => false,
                            'message' => $e->getMessage(),
                            'error_code' => 'DOMAIN_LIMIT_EXCEEDED',
                            'data' => [
                                'max_domains' => $license->max_domains ?? 1,
                                'current_domains' => $license->active_domains_count,
                                'remaining_domains' => $license->remaining_domains,
                                'license_type' => $license->license_type,
                            ],
                        ], 403);
                    }
                } else {
                    // Verification mode: Verify domain authorization
                    if (! $this->verifyDomain($license, $domain)) {
                        $this->logApiVerificationAttempt(
                            $request,
                            false,
                            'Domain not authorized for this license',
                            'api',
                        );

                        return response()->json([
                            'valid' => false,
                            'message' => 'Domain not authorized for this license',
                            'error_code' => 'DOMAIN_NOT_AUTHORIZED',
                            'data' => [
                                'max_domains' => $license->max_domains ?? 1,
                                'current_domains' => $license->active_domains_count,
                                'remaining_domains' => $license->remaining_domains,
                                'license_type' => $license->license_type,
                            ],
                        ], 403);
                    }
                }
            }
            // Step 6: Log successful verification
            $this->logApiVerificationAttempt($request, true, 'License verified successfully', 'api', [
                'license_id' => $license->id,
                'verification_method' => $verificationMethod,
                'envato_valid' => $envatoValid,
                'database_valid' => $databaseValid,
            ]);
            // Step 7: Log verification in database
            $this->logVerification($license, $domain, $verificationMethod);
            DB::commit();

            return response()->json([
                'valid' => true,
                'message' => 'License verified successfully',
                'data' => [
                    'license_id' => $license->id,
                    'license_type' => $license->license_type,
                    'max_domains' => $license->max_domains ?? 1,
                    'current_domains' => $license->active_domains_count,
                    'remaining_domains' => $license->remaining_domains,
                    'expires_at' => $license->license_expires_at?->toISOString(),
                    'support_expires_at' => $license->support_expires_at?->toISOString(),
                    'status' => $license->status,
                    'verification_method' => $verificationMethod,
                    'envato_valid' => $envatoValid,
                    'database_valid' => $databaseValid,
                ],
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'valid' => false,
                'message' => 'Verification failed: '.$e->getMessage(),
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Verify verification key.
     */
    private function verifyVerificationKey(Product $product, string $verificationKey): bool
    {
        $appKey = config('app.key');
        $appKeyStr = is_string($appKey) ? $appKey : (is_scalar($appKey) ? (string)$appKey : '');
        $expectedKey = hash('sha256', $product->id.$product->slug.$appKeyStr);

        return hash_equals($expectedKey, $verificationKey);
    }

    /**
     * Create license from Envato data.
     */
    /**
     * @param array<string, mixed> $envatoData
     */
    private function createLicenseFromEnvato(Product $product, string $purchaseCode, array $envatoData): License
    {
        // Determine max_domains based on license type
        $maxDomains = $this->getMaxDomainsForLicenseType($product->license_type ?? 'single');
        $license = License::create([
            'product_id' => $product->id,
            'purchase_code' => $purchaseCode,
            'license_key' => $purchaseCode, // Use same value as purchase_code
            'license_type' => $product->license_type ?? 'single',
            'max_domains' => $maxDomains,
            'support_expires_at' => now()->addDays($product->support_days ?? 365),
            'license_expires_at' => $product->license_type === 'extended' ? now()->addYear() : null,
            'status' => 'active',
        ]);
        // Log the license creation from Envato
        $this->logVerification($license, null, 'envato_auto_created');

        return $license;
    }

    /**
     * Get maximum domains allowed for license type.
     */
    private function getMaxDomainsForLicenseType(string $licenseType): int
    {
        return match ($licenseType) {
            'single' => 1,
            'multi' => 5,
            'developer' => 10,
            'extended' => 3,
            default => 1,
        };
    }

    /**
     * Verify domain authorization.
     */
    private function verifyDomain(License $license, string $domain): bool
    {
        // Remove protocol and www
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        $domain = preg_replace('/^www\./', '', $domain);
        $authorizedDomains = $license->domains()->where('status', 'active')->get();
        // If no domains are configured, check if we can register the current domain
        if ($authorizedDomains->isEmpty()) {
            // Check domain limit before auto-registering
            try {
                $this->checkDomainLimit($license, $domain);
                $this->registerDomainForLicense($license, $domain);

                return true;
            } catch (\Exception $e) {
                \Log::warning('Cannot auto-register domain due to limit', [
                    'license_id' => $license->id,
                    'domain' => $domain,
                    'error' => $e->getMessage(),
                    'ip' => request()->ip(),
                ]);

                return false;
            }
        }
        foreach ($authorizedDomains as $authorizedDomain) {
            $authDomain = preg_replace('/^https?:\/\//', '', $authorizedDomain->domain);
            $authDomain = preg_replace('/^www\./', '', $authDomain);
            if ($authDomain === $domain) {
                // Update last used timestamp
                $authorizedDomain->update(['last_used_at' => now()]);
                // Log verification for exact domain match
                $this->logVerification($license, $domain, 'domain_verification_exact');

                return true;
            }
            // Check wildcard domains
            if (str_starts_with($authDomain, '*.')) {
                $pattern = str_replace('*.', '', $authDomain);
                if (str_ends_with($domain, $pattern)) {
                    // Update last used timestamp
                    $authorizedDomain->update(['last_used_at' => now()]);
                    // Log verification for wildcard domain match
                    $this->logVerification($license, $domain, 'domain_verification_wildcard');

                    return true;
                }
            }
        }

        // Domain not found in authorized domains
        return false;
    }

    /**
     * Register domain for license automatically.
     */
    private function registerDomainForLicense(License $license, string $domain): void
    {
        // Clean domain (remove protocol and www)
        $cleanDomain = preg_replace('/^https?:\/\//', '', $domain);
        $cleanDomain = preg_replace('/^www\./', '', $cleanDomain);
        // Check if domain already exists for this license
        $existingDomain = $license->domains()
            ->where('domain', $cleanDomain)
            ->first();
        if ($existingDomain) {
            // Update last used timestamp
            $existingDomain->update(['last_used_at' => now()]);
            // Log verification for existing domain
            $this->logVerification($license, $cleanDomain, 'domain_verification_existing');
        } else {
            // Check domain limit before creating new domain
            $this->checkDomainLimit($license, $cleanDomain);
            // Create new domain record
            $license->domains()->create([
                'domain' => $cleanDomain,
                'status' => 'active',
                'added_at' => now(),
                'last_used_at' => now(),
            ]);
            // Domain automatically registered for license
            // Log verification for new domain
            $this->logVerification($license, $cleanDomain, 'domain_verification_new');
        }
    }

    /**
     * Check if license has reached its domain limit.
     */
    private function checkDomainLimit(License $license, string $domain): void
    {
        if ($license->hasReachedDomainLimit()) {
            \Log::warning('Domain limit exceeded for license', [
                'license_id' => $license->id,
                'purchase_code' => substr($license->purchase_code, 0, 8).'...',
                'domain' => $domain,
                'current_domains' => $license->active_domains_count,
                'max_domains' => $license->max_domains ?? 1,
                'license_type' => $license->license_type,
                'ip' => request()->ip(),
            ]);
            $maxDomains = $license->max_domains ?? 1;
            throw new \Exception("License has reached its maximum domain limit ({$maxDomains} domain".
                ($maxDomains > 1 ? 's' : '')."). Cannot register new domain: {$domain}");
        }
    }

    /**
     * Check if license is active.
     */
    private function isLicenseActive(License $license): bool
    {
        if ($license->status !== 'active') {
            return false;
        }
        // Check license expiration
        if ($license->license_expires_at && $license->license_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique license key.
     */
    private function generateLicenseKey(): string
    {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8).'-'.
                         substr(md5(uniqid(mt_rand(), true)), 0, 8).'-'.
                         substr(md5(uniqid(mt_rand(), true)), 0, 8).'-'.
                         substr(md5(uniqid(mt_rand(), true)), 0, 8));
    }

    /**
     * Log API verification attempt.
     *
     * @param  Request  $request  The HTTP request
     * @param  bool  $success  Whether the verification was successful
     * @param  string  $message  The log message
     * @param  string  $source  The verification source
     * @param  array  $additionalData  Additional data to log
     */
    /**
     * @param array<string, mixed> $additionalData
     */
    private function logApiVerificationAttempt(
        Request $request,
        bool $success,
        string $message,
        string $source,
        array $additionalData = [],
    ): void {
        $logData = [
            'success' => $success,
            'message' => $message,
            'source' => $source,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
        if (! empty($additionalData)) {
            $logData = array_merge($logData, $additionalData);
        }
        if ($success) {
            // No logging for successful operations per Envato compliance rules
        } else {
            Log::warning('License verification attempt failed', $logData);
        }
    }

    /**
     * Sanitize output to prevent XSS attacks.
     *
     * @param  string|null  $output  The output to sanitize
     *
     * @return string The sanitized output
     */
    private function sanitizeOutput(?string $output): string
    {
        if ($output === null) {
            return '';
        }

        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Log license verification.
     */
    private function logVerification(License $license, ?string $domain, string $method): void
    {
        $license->logs()->create([
            'domain' => $domain ?? 'unknown',
            'ip_address' => request()->ip(),
            'serial' => $license->purchase_code,
            'status' => 'success',
            'user_agent' => request()->userAgent(),
            'request_data' => [
                'verification_method' => $method,
                'domain' => $domain,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
            'response_data' => [
                'valid' => true,
                'method' => $method,
            ],
        ]);
    }

    /**
     * Register license endpoint with enhanced security.
     *
     * This endpoint is used to register licenses from Envato with comprehensive
     * validation, security measures, and proper error handling.
     *
     * @param  LicenseRegisterRequest  $request  The validated request containing license registration data
     *
     * @return JsonResponse Response with license registration result
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     */
    public function register(LicenseRegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Check authorization header
            $authHeader = $request->header('Authorization');
            $expectedToken = 'Bearer '.$this->getApiToken();
            if (! $expectedToken || $authHeader !== $expectedToken) {
                Log::warning('Unauthorized license registration attempt', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'headers' => $request->headers->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            $validated = $request->validated();
            // Get validated and sanitized data from Request class
            $purchaseCode = $validated['purchase_code'];
            $productSlug = $validated['product_slug'];
            $domain = $validated['domain'] ?? null;
            $envatoData = $validated['envato_data'] ?? [];
            // Find product by slug
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                Log::warning('Product not found during license registration', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }
            // Check if license already exists
            $existingLicense = License::where('purchase_code', $purchaseCode)
                ->where('product_id', $product->id)
                ->first();
            if ($existingLicense) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'License already exists',
                ]);
            }
            // Determine max_domains based on license type
            $maxDomains = $this->getMaxDomainsForLicenseType($product->license_type ?? 'single');
            // Create new license
            $license = License::create([
                'product_id' => $product->id,
                'purchase_code' => $purchaseCode,
                'license_key' => $this->generateLicenseKey(),
                'license_type' => $product->license_type ?? 'single',
                'max_domains' => $maxDomains,
                'support_expires_at' => now()->addDays($product->support_days ?? 365),
                'license_expires_at' => $product->license_type === 'extended' ? now()->addYear() : null,
                'status' => 'active',
            ]);
            // Add domain if provided
            if ($domain) {
                $license->domains()->create([
                    'domain' => $domain,
                    'status' => 'active',
                ]);
            }
            // Log the license registration
            $this->logVerification($license, $domain, 'license_registration');
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'License registered successfully',
                'license_id' => $license->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get license status endpoint with enhanced security.
     *
     * Retrieves license status information with comprehensive validation,
     * security measures, and proper error handling.
     *
     * @param  LicenseStatusRequest  $request  The validated request containing license status check data
     *
     * @return JsonResponse Response with license status information
     *
     * @throws \Exception When database operations fail
     *
     * @version 1.0.6
     */
    public function status(LicenseStatusRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            // Get validated and sanitized data from Request class
            $licenseKey = $validated['license_key'];
            $productSlug = $validated['product_slug'];
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                Log::warning('Product not found during license status check', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'Product not found',
                ], 404);
            }
            $license = License::where('license_key', $licenseKey)
                ->where('product_id', $product->id)
                ->first();
            if (! $license) {
                Log::warning('License not found during status check', [
                    'license_key' => $licenseKey,
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'valid' => false,
                    'message' => 'License not found',
                ], 404);
            }
            $isActive = $this->isLicenseActive($license);
            // Log the status check
            if ($isActive) {
                $this->logVerification($license, null, 'status_check_success');
            }
            DB::commit();

            return response()->json([
                'valid' => $isActive,
                'license' => [
                    'id' => $license->id,
                    'type' => $license->license_type,
                    'expires_at' => $license->license_expires_at?->toISOString(),
                    'support_expires_at' => $license->support_expires_at?->toISOString(),
                    'status' => $license->status,
                ],
                'product' => [
                    'name' => $this->sanitizeOutput($product->name),
                    'version' => $this->sanitizeOutput($product->version),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License status check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'valid' => false,
                'message' => 'Status check failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
