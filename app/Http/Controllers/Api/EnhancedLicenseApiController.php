<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Api\LicenseRegisterRequest;
use App\Http\Requests\Api\LicenseStatusRequest;
use App\Http\Requests\Api\LicenseVerifyRequest;
use App\Models\License;
use App\Models\Product;
use App\Services\EnhancedSecurityService;
use App\Services\EnvatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Enhanced License API Controller.
 *
 * Provides secure, well-structured API endpoints for license management
 * with comprehensive security measures and proper error handling.
 *
 * Features:
 * - License verification with Envato API integration
 * - License registration and status checking
 * - Enhanced security with rate limiting and IP blacklisting
 * - Domain verification and authorization
 * - Comprehensive error handling with database transactions
 * - API token authentication with enhanced security
 * - Suspicious activity detection and prevention
 * - XSS protection and input validation
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
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
class EnhancedLicenseApiController extends BaseController
{
    public function __construct(
        private readonly EnvatoService $envatoService,
        private readonly EnhancedSecurityService $securityService,
    ) {
        $this->middleware('throttle:api');
    }

    /**
     * Verify license endpoint with enhanced security.
     *
     * Verifies a license using purchase code and product slug with comprehensive
     * security measures including rate limiting, IP blacklisting, and domain verification.
     *
     * @param  LicenseVerifyRequest  $request  The validated request containing license verification data
     *
     * @return JsonResponse Response with license verification result
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /api/license/verify
     * {
     *     "purchase_code": "ABC123-DEF456-GHI789",
     *     "product_slug": "my-product",
     *     "domain": "example.com"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "message": "License verified successfully",
     *     "data": {
     *         "license_id": 123,
     *         "license_type": "regular",
     *         "status": "active"
     *     }
     * }
     */
    public function verify(LicenseVerifyRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Perform common API checks
            $commonCheckResult = $this->performCommonApiChecks($request, 'license_verification');
            if ($commonCheckResult) {
                DB::rollBack();

                return $commonCheckResult;
            }
            // Get validated data from Request class
            $validated = $request->validated();
            // Additional authorization check for verification
            if (! $this->isAuthorized($request)) {
                $this->logSecurityEvent('Unauthorized API access', $request, [
                    'purchase_code' => $this->securityService->hashForLogging(
                        is_string($validated['purchase_code'])
                            ? $validated['purchase_code']
                            : ''
                    ),
                ]);

                return $this->errorResponse('Unauthorized', null, Response::HTTP_UNAUTHORIZED);
            }
            // Find product
            $productSlug = is_string($validated['product_slug']) ? $validated['product_slug'] : '';
            $product = $this->findProduct($productSlug);
            if (! $product) {
                Log::warning('Product not found during enhanced license verification', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->errorResponse('Product not found', null, Response::HTTP_NOT_FOUND);
            }
            // Verify verification key if provided
            $verificationKey = is_string($validated['verification_key'] ?? null) ? $validated['verification_key'] : '';
            if (
                isset($validated['verification_key']) &&
                ! $this->verifyVerificationKey($product, $verificationKey)
            ) {
                $this->logSecurityEvent('Invalid verification key', $request, [
                    'product_slug' => $validated['product_slug'],
                ]);

                return $this->errorResponse('Invalid verification key', null, Response::HTTP_FORBIDDEN);
            }
            // Process license verification
            $result = $this->processLicenseVerification($product, $validated, $request);
            DB::commit();

            return $this->jsonResponse($result, 'License verified successfully');
        } catch (ValidationException $e) {
            DB::rollBack();

            /**
 * @var array<string, mixed> $errors
*/
            $errors = $e->errors();
            return $this->errorResponse(
                'Validation failed',
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleException($e, $request, 'License verification');
        }
    }

    /**
     * Register license endpoint with enhanced security.
     *
     * Registers a new license using purchase code and product slug with comprehensive
     * security measures including rate limiting, IP blacklisting, and duplicate checking.
     *
     * @param  LicenseRegisterRequest  $request  The validated request containing license registration data
     *
     * @return JsonResponse Response with license registration result
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /api/license/register
     * {
     *     "purchase_code": "ABC123-DEF456-GHI789",
     *     "product_slug": "my-product",
     *     "domain": "example.com"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "message": "License registered successfully",
     *     "data": {
     *         "license_id": 123,
     *         "status": "created"
     *     }
     * }
     */
    public function register(LicenseRegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Perform common API checks
            $commonCheckResult = $this->performCommonApiChecks($request);
            if ($commonCheckResult) {
                DB::rollBack();

                return $commonCheckResult;
            }
            // Get validated data from Request class
            $validated = $request->validated();
            // Find product
            $productSlug = is_string($validated['product_slug']) ? $validated['product_slug'] : '';
            $product = $this->findProduct($productSlug);
            if (! $product) {
                Log::warning('Product not found during enhanced license registration', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->errorResponse('Product not found', null, Response::HTTP_NOT_FOUND);
            }
            // Check if license already exists
            $existingLicense = License::where('purchase_code', $validated['purchase_code'])
                ->where('product_id', $product->id)
                ->first();
            if ($existingLicense) {
                DB::commit();

                return $this->jsonResponse([
                    'license_id' => $existingLicense->id,
                    'status' => 'already_exists',
                ], 'License already exists');
            }
            // Create new license
            $license = $this->createLicense($product, $validated);
            DB::commit();

            return $this->jsonResponse([
                'license_id' => $license->id,
                'status' => 'created',
            ], 'License registered successfully');
        } catch (ValidationException $e) {
            DB::rollBack();

            /**
 * @var array<string, mixed> $errors
*/
            $errors = $e->errors();
            return $this->errorResponse(
                'Validation failed',
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleException($e, $request, 'License registration');
        }
    }

    /**
     * Get license status endpoint with enhanced security.
     *
     * Retrieves license status information using license key and product slug with
     * comprehensive security measures including rate limiting and IP blacklisting.
     *
     * @param  LicenseStatusRequest  $request  The validated request containing license status check data
     *
     * @return JsonResponse Response with license status information
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request:
     * POST /api/license/status
     * {
     *     "license_key": "ABC123-DEF456-GHI789",
     *     "product_slug": "my-product"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "message": "License status retrieved",
     *     "data": {
     *         "license_id": 123,
     *         "status": "active",
     *         "is_active": true
     *     }
     * }
     */
    public function status(LicenseStatusRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Perform common API checks
            $commonCheckResult = $this->performCommonApiChecks($request);
            if ($commonCheckResult) {
                DB::rollBack();

                return $commonCheckResult;
            }
            // Get validated data from Request class
            $validated = $request->validated();
            // Find product
            $productSlug = is_string($validated['product_slug']) ? $validated['product_slug'] : '';
            $product = $this->findProduct($productSlug);
            if (! $product) {
                Log::warning('Product not found during enhanced license status check', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->errorResponse('Product not found', null, Response::HTTP_NOT_FOUND);
            }
            // Find license
            $license = License::where('license_key', $validated['license_key'])
                ->where('product_id', $product->id)
                ->first();
            if (! $license) {
                Log::warning('License not found during enhanced status check', [
                    'license_key' => $validated['license_key'],
                    'product_slug' => $validated['product_slug'],
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $this->errorResponse('License not found', null, Response::HTTP_NOT_FOUND);
            }
            // Check license status
            $isActive = $this->isLicenseActive($license);
            $statusData = [
                'license_id' => $license->id,
                'type' => $license->license_type,
                'expires_at' => $license->license_expires_at?->toISOString(),
                'support_expires_at' => $license->support_expires_at?->toISOString(),
                'status' => $license->status,
                'is_active' => $isActive,
            ];
            DB::commit();

            return $this->jsonResponse($statusData, 'License status retrieved');
        } catch (ValidationException $e) {
            DB::rollBack();

            /**
 * @var array<string, mixed> $errors
*/
            $errors = $e->errors();
            return $this->errorResponse(
                'Validation failed',
                $errors,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Exception $e) {
            DB::rollBack();

            return $this->handleException($e, $request, 'License status check');
        }
    }

    /**
     * Perform comprehensive security checks.
     */
    private function performSecurityChecks(Request $request): void
    {
        // Check for suspicious activity
        $suspicious = $this->securityService->detectSuspiciousActivity($request);
        if (! empty($suspicious)) {
            $this->logSecurityEvent('Suspicious activity detected', $request, [
                'suspicious_patterns' => $suspicious,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Suspicious activity detected');
        }
        // Check IP blacklist
        $clientIp = $request->ip();
        if ($clientIp && $this->securityService->isIpBlacklisted($clientIp)) {
            $this->logSecurityEvent('Blacklisted IP access attempt', $request);
            abort(Response::HTTP_FORBIDDEN, 'Access denied');
        }
    }

    /**
     * Perform common API request validation and security checks.
     */
    private function performCommonApiChecks(Request $request, string $rateLimitType = 'api_requests'): ?JsonResponse
    {
        // Security checks
        $this->performSecurityChecks($request);
        // Rate limiting
        $clientFingerprint = $this->securityService->getClientFingerprint($request);
        if (! $this->securityService->checkRateLimit($rateLimitType, $clientFingerprint)) {
            $message = $rateLimitType === 'license_verification'
                ? 'Too many verification attempts. Please try again later.'
                : 'Too many requests. Please try again later.';
            if ($rateLimitType === 'license_verification') {
                $this->logSecurityEvent('Rate limit exceeded', $request, [
                    'action' => 'license_verification',
                    'client_fingerprint' => $clientFingerprint,
                ]);
            }

            return $this->errorResponse($message, null, Response::HTTP_TOO_MANY_REQUESTS);
        }
        // Check authorization
        if (! $this->isAuthorized($request)) {
            return $this->errorResponse('Unauthorized', null, Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    /**
     * Check if request is authorized.
     */
    private function isAuthorized(Request $request): bool
    {
        $authHeader = $request->header('Authorization');
        $expectedToken = 'Bearer ' . $this->getApiToken();

        return $authHeader === $expectedToken;
    }

    /**
     * Get API token from settings.
     */
    private function getApiToken(): string
    {
        $token = \App\Helpers\ConfigHelper::getSetting('license_api_token', '', 'LICENSE_API_TOKEN');
        return is_string($token) ? $token : '';
    }

    /**
     * Find product by slug.
     */
    private function findProduct(string $slug): ?Product
    {
        $result = Cache::remember("product_slug_{$slug}", 3600, function () use ($slug) {
            return Product::where('slug', $slug)->first();
        });
        return $result instanceof Product ? $result : null;
    }

    /**
     * Verify verification key.
     */
    private function verifyVerificationKey(Product $product, string $verificationKey): bool
    {
        $appKey = config('app.key');
        $appKeyStr = is_string($appKey) ? $appKey : (is_scalar($appKey) ? (string)$appKey : '');
        $expectedKey = hash('sha256', $product->id . $product->slug . $appKeyStr);

        return hash_equals($expectedKey, $verificationKey);
    }

    /**
     * Process license verification.
     *
     * @param array<string, mixed> $validated
     *
     * @return array<string, mixed>
     */
    private function processLicenseVerification(Product $product, array $validated, Request $request): array
    {
        $purchaseCode = is_string($validated['purchase_code']) ? $validated['purchase_code'] : '';
        $domain = is_string($validated['domain'] ?? null) ? $validated['domain'] : null;
        // Check database first
        $license = License::where('purchase_code', $purchaseCode)
            ->where('product_id', $product->id)
            ->first();
        if ($license) {
            return $this->verifyExistingLicense($license, $domain, $request);
        }

        // Try Envato API
        return $this->verifyWithEnvato($product, $purchaseCode, $domain, $request);
    }

    /**
     * Verify existing license.
     *
     * @return array<string, mixed>
     */
    private function verifyExistingLicense(License $license, ?string $domain, Request $request): array
    {
        if (! $this->isLicenseActive($license)) {
            $this->logSecurityEvent('Inactive license verification attempt', $request, [
                'license_id' => $license->id,
                'status' => $license->status,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'License is not active');
        }
        // Handle domain verification
        if ($domain && ! $this->verifyDomain($license, $domain)) {
            $this->logSecurityEvent('Unauthorized domain verification attempt', $request, [
                'license_id' => $license->id,
                'domain' => $domain,
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Domain not authorized for this license');
        }

        return [
            'license_id' => $license->id,
            'license_type' => $license->license_type,
            'expires_at' => $license->license_expires_at?->toISOString(),
            'support_expires_at' => $license->support_expires_at?->toISOString(),
            'status' => $license->status,
            'verification_method' => 'database',
        ];
    }

    /**
     * Verify with Envato API.
     *
     * @return array<string, mixed>
     */
    private function verifyWithEnvato(Product $product, string $purchaseCode, ?string $domain, Request $request): array
    {
        $envatoData = $this->envatoService->verifyPurchase($purchaseCode);

        if (
            ! is_array($envatoData)
            || ! isset($envatoData['item'])
            || ! is_array($envatoData['item'])
            || ! isset($envatoData['item']['id'])
        ) {
            $this->logSecurityEvent('Invalid Envato verification', $request, [
                'purchase_code' => $this->securityService->hashForLogging($purchaseCode),
                'product_id' => $product->id,
            ]);
            abort(Response::HTTP_NOT_FOUND, 'License not found');
        }

        if ($envatoData['item']['id'] != $product->envato_item_id) {
            $this->logSecurityEvent('Invalid Envato verification', $request, [
                'purchase_code' => $this->securityService->hashForLogging($purchaseCode),
                'product_id' => $product->id,
            ]);
            abort(Response::HTTP_NOT_FOUND, 'License not found');
        }
        // Create license from Envato data
        /**
 * @var array<string, mixed> $envatoDataTyped
*/
        $envatoDataTyped = $envatoData;
        $license = $this->createLicenseFromEnvato($product, $purchaseCode, $envatoDataTyped);

        return [
            'license_id' => $license->id,
            'license_type' => $license->license_type,
            'expires_at' => $license->license_expires_at?->toISOString(),
            'support_expires_at' => $license->support_expires_at?->toISOString(),
            'status' => $license->status,
            'verification_method' => 'envato_auto_created',
        ];
    }

    /**
     * Check if license is active.
     */
    private function isLicenseActive(License $license): bool
    {
        if ($license->status !== 'active') {
            return false;
        }
        if ($license->license_expires_at && $license->license_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Verify domain authorization.
     */
    private function verifyDomain(License $license, string $domain): bool
    {
        // Clean domain
        $domain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;
        $authorizedDomains = $license->domains()->where('status', 'active')->get();
        if ($authorizedDomains->isEmpty()) {
            $this->registerDomainForLicense($license, $domain);

            return true;
        }
        foreach ($authorizedDomains as $authorizedDomain) {
            $authDomain = preg_replace('/^https?:\/\//', '', $authorizedDomain->domain ?? '');
            $authDomain = preg_replace('/^www\./', '', $authDomain ?? '');
            if ($authDomain === $domain) {
                $authorizedDomain->update(['last_used_at' => now()]);

                return true;
            }
            // Check wildcard domains
            if ($authDomain && str_starts_with($authDomain, '*.')) {
                $pattern = str_replace('*.', '', $authDomain);
                if ($domain && str_ends_with($domain, $pattern)) {
                    $authorizedDomain->update(['last_used_at' => now()]);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Register domain for license.
     */
    private function registerDomainForLicense(License $license, string $domain): void
    {
        $cleanDomain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;
        $cleanDomain = preg_replace('/^www\./', '', $cleanDomain) ?? $cleanDomain;
        $existingDomain = $license->domains()
            ->where('domain', $cleanDomain)
            ->first();
        if (! $existingDomain) {
            $license->domains()->create([
                'domain' => $cleanDomain,
                'status' => 'active',
                'added_at' => now(),
                'last_used_at' => now(),
            ]);
        } else {
            $existingDomain->update(['last_used_at' => now()]);
        }
    }

    /**
     * Create license from Envato data.
     *
     * @param array<string, mixed> $envatoData
     */
    private function createLicenseFromEnvato(Product $product, string $purchaseCode, array $envatoData): License
    {
        return License::create([
            'product_id' => $product->id,
            'purchase_code' => $purchaseCode,
            'license_key' => $purchaseCode,
            'license_type' => $product->license_type ?? 'regular',
            'support_expires_at' => now()->addDays($product->support_days ?? 365),
            'license_expires_at' => $product->license_type === 'extended' ? now()->addYear() : null,
            'status' => 'active',
        ]);
    }

    /**
     * Create new license.
     *
     * @param array<string, mixed> $validated
     */
    private function createLicense(Product $product, array $validated): License
    {
        $license = License::create([
            'product_id' => $product->id,
            'purchase_code' => $validated['purchase_code'],
            'license_key' => $this->generateLicenseKey(),
            'license_type' => $product->license_type ?? 'regular',
            'support_expires_at' => now()->addDays($product->support_days ?? 365),
            'license_expires_at' => $product->license_type === 'extended' ? now()->addYear() : null,
            'status' => 'active',
        ]);
        if (isset($validated['domain'])) {
            $license->domains()->create([
                'domain' => $validated['domain'],
                'status' => 'active',
            ]);
        }

        return $license;
    }

    /**
     * Generate unique license key.
     */
    private function generateLicenseKey(): string
    {
        do {
            $key = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
                         substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
                         substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
                         substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
        } while (License::where('license_key', $key)->exists());

        return $key;
    }
}
