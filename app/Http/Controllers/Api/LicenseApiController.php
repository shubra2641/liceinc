<?php

declare(strict_types=1);

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
 * License API Controller - Simplified
 */
class LicenseApiController extends Controller
{
    public function __construct(private EnvatoService $envatoService) {}

    /**
     * Get API token
     */
    private function getApiToken(): string
    {
        return \App\Helpers\ConfigHelper::getSetting('license_api_token', '', 'LICENSE_API_TOKEN');
    }

    /**
     * Verify license
     */
    public function verify(LicenseVerifyRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check authorization
            if (!$this->checkAuthorization($request)) {
                return $this->unauthorizedResponse();
            }

            $validated = $request->validated();
            $purchaseCode = $validated['purchase_code'];
            $productSlug = $validated['product_slug'];
            $domain = $validated['domain'] ?? null;

            // Find product
            $product = Product::where('slug', $productSlug)->first();
            if (!$product) {
                return $this->licenseErrorResponse('Product not found', 'PRODUCT_NOT_FOUND', 404);
            }

            // Find license
            $license = License::where('purchase_code', $purchaseCode)
                ->where('product_id', $product->id)
                ->first();

            if ($license) {
                // Check license status
                if (!$this->isLicenseActive($license)) {
                    return $this->handleInactiveLicense($license);
                }
            } else {
                // Try Envato API
                $envatoData = $this->envatoService->verifyPurchase($purchaseCode);
                if ($this->isValidEnvatoData($envatoData, $product)) {
                    $license = $this->createLicenseFromEnvato($product, $purchaseCode, $envatoData);
                } else {
                    return $this->licenseErrorResponse('License not found', 'LICENSE_NOT_FOUND', 404);
                }
            }

            // Handle domain verification
            if ($domain && !$this->handleDomainVerification($license, $domain)) {
                return $this->domainLimitResponse($license);
            }

            // Log verification
            $this->logVerification($license, $domain, 'api_verification');
            DB::commit();

            return $this->licenseSuccessResponse($license, $domain);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License verification failed', ['error' => $e->getMessage()]);
            return $this->licenseErrorResponse('Verification failed: ' . $e->getMessage(), 'INTERNAL_ERROR', 500);
        }
    }

    /**
     * Register license
     */
    public function register(LicenseRegisterRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Check authorization
            if (!$this->checkAuthorization($request)) {
                return $this->unauthorizedResponse();
            }

            $validated = $request->validated();
            $purchaseCode = $validated['purchase_code'];
            $productSlug = $validated['product_slug'];
            $domain = $validated['domain'] ?? null;

            // Find product
            $product = Product::where('slug', $productSlug)->first();
            if (!$product) {
                return $this->licenseErrorResponse('Product not found', 'PRODUCT_NOT_FOUND', 404);
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

            // Create new license
            $license = $this->createLicense($product, $purchaseCode, $domain);

            // Log registration
            $this->logVerification($license, $domain, 'license_registration');
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'License registered successfully',
                'license_id' => $license->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License registration failed', ['error' => $e->getMessage()]);
            return $this->licenseErrorResponse('Registration failed: ' . $e->getMessage(), 'INTERNAL_ERROR', 500);
        }
    }

    /**
     * Get license status
     */
    public function status(LicenseStatusRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $licenseKey = $validated['license_key'];
            $productSlug = $validated['product_slug'];

            // Find product
            $product = Product::where('slug', $productSlug)->first();
            if (!$product) {
                return $this->licenseErrorResponse('Product not found', 'PRODUCT_NOT_FOUND', 404);
            }

            // Find license
            $license = License::where('license_key', $licenseKey)
                ->where('product_id', $product->id)
                ->first();

            if (!$license) {
                return $this->licenseErrorResponse('License not found', 'LICENSE_NOT_FOUND', 404);
            }

            $isActive = $this->isLicenseActive($license);

            if ($isActive) {
                $this->logVerification($license, null, 'status_check_success');
            }

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
                    'name' => htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'),
                    'version' => htmlspecialchars($product->version, ENT_QUOTES, 'UTF-8'),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('License status check failed', ['error' => $e->getMessage()]);
            return $this->licenseErrorResponse('Status check failed: ' . $e->getMessage(), 'INTERNAL_ERROR', 500);
        }
    }

    /**
     * Check authorization
     */
    private function checkAuthorization($request): bool
    {
        $authHeader = $request->header('Authorization');
        $expectedToken = 'Bearer ' . $this->getApiToken();
        return $authHeader === $expectedToken;
    }

    /**
     * Unauthorized response
     */
    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'valid' => false,
            'message' => 'Unauthorized',
            'error_code' => 'UNAUTHORIZED',
        ], 401);
    }

    /**
     * License error response
     */
    private function licenseErrorResponse(string $message, string $errorCode, int $status = 400): JsonResponse
    {
        return response()->json([
            'valid' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ], $status);
    }

    /**
     * License success response
     */
    private function licenseSuccessResponse(License $license, ?string $domain): JsonResponse
    {
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
            ],
        ]);
    }

    /**
     * Check if license is active
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
     * Handle inactive license
     */
    private function handleInactiveLicense(License $license): JsonResponse
    {
        if ($license->status === 'suspended') {
            return $this->licenseErrorResponse('License is suspended', 'LICENSE_SUSPENDED', 403);
        }

        if ($license->license_expires_at && $license->license_expires_at->isPast()) {
            return response()->json([
                'valid' => false,
                'message' => 'License has expired',
                'error_code' => 'LICENSE_EXPIRED',
                'data' => [
                    'expires_at' => $license->license_expires_at->toISOString(),
                ],
            ], 403);
        }

        return $this->licenseErrorResponse('License is not active', 'LICENSE_INACTIVE', 403);
    }

    /**
     * Check if Envato data is valid
     */
    private function isValidEnvatoData(?array $envatoData, Product $product): bool
    {
        return $envatoData && 
               isset($envatoData['item']) && 
               is_array($envatoData['item']) && 
               isset($envatoData['item']['id']) && 
               $envatoData['item']['id'] == $product->envato_item_id;
    }

    /**
     * Create license from Envato data
     */
    private function createLicenseFromEnvato(Product $product, string $purchaseCode, array $envatoData): License
    {
        $maxDomains = $this->getMaxDomainsForLicenseType($product->license_type ?? 'single');
        
        $license = License::create([
            'product_id' => $product->id,
            'purchase_code' => $purchaseCode,
            'license_key' => $purchaseCode,
            'license_type' => $product->license_type ?? 'single',
            'max_domains' => $maxDomains,
            'support_expires_at' => now()->addDays($product->support_days ?? 365),
            'license_expires_at' => $product->license_type === 'extended' ? now()->addYear() : null,
            'status' => 'active',
        ]);

        $this->logVerification($license, null, 'envato_auto_created');
        return $license;
    }

    /**
     * Create license
     */
    private function createLicense(Product $product, string $purchaseCode, ?string $domain): License
    {
        $maxDomains = $this->getMaxDomainsForLicenseType($product->license_type ?? 'single');
        
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

        if ($domain) {
            $license->domains()->create([
                'domain' => $domain,
                'status' => 'active',
            ]);
        }

        return $license;
    }

    /**
     * Get max domains for license type
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
     * Handle domain verification
     */
    private function handleDomainVerification(License $license, string $domain): bool
    {
        $autoRegister = \App\Helpers\ConfigHelper::getSetting('license_auto_register_domains', false);
        $isTestMode = config('app.env') === 'local' || config('app.debug') === true;

        if ($autoRegister || $isTestMode) {
            try {
                $this->registerDomainForLicense($license, $domain);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        return $this->verifyDomain($license, $domain);
    }

    /**
     * Verify domain
     */
    private function verifyDomain(License $license, string $domain): bool
    {
        $cleanDomain = $this->cleanDomain($domain);
        $authorizedDomains = $license->domains()->where('status', 'active')->get();

        if ($authorizedDomains->isEmpty()) {
            try {
                $this->checkDomainLimit($license, $domain);
                $this->registerDomainForLicense($license, $domain);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        }

        foreach ($authorizedDomains as $authorizedDomain) {
            $authDomain = $this->cleanDomain($authorizedDomain->domain ?? '');
            
            if ($authDomain === $cleanDomain) {
                $authorizedDomain->update(['last_used_at' => now()]);
                return true;
            }

            if (str_starts_with($authDomain, '*.')) {
                $pattern = str_replace('*.', '', $authDomain);
                if (str_ends_with($cleanDomain, $pattern)) {
                    $authorizedDomain->update(['last_used_at' => now()]);
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Register domain for license
     */
    private function registerDomainForLicense(License $license, string $domain): void
    {
        $cleanDomain = $this->cleanDomain($domain);
        
        $existingDomain = $license->domains()
            ->where('domain', $cleanDomain)
            ->first();

        if ($existingDomain) {
            $existingDomain->update(['last_used_at' => now()]);
        } else {
            $this->checkDomainLimit($license, $cleanDomain);
            $license->domains()->create([
                'domain' => $cleanDomain,
                'status' => 'active',
                'added_at' => now(),
                'last_used_at' => now(),
            ]);
        }
    }

    /**
     * Check domain limit
     */
    private function checkDomainLimit(License $license, string $domain): void
    {
        if ($license->hasReachedDomainLimit()) {
            $maxDomains = $license->max_domains ?? 1;
            throw new \Exception("License has reached its maximum domain limit ({$maxDomains} domain" . 
                ($maxDomains > 1 ? 's' : '') . "). Cannot register new domain: {$domain}");
        }
    }

    /**
     * Clean domain
     */
    private function cleanDomain(string $domain): string
    {
        $domain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;
        return $domain;
    }

    /**
     * Domain limit response
     */
    private function domainLimitResponse(License $license): JsonResponse
    {
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

    /**
     * Generate license key
     */
    private function generateLicenseKey(): string
    {
        return strtoupper(
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8) . '-' .
            substr(md5(uniqid((string)mt_rand(), true)), 0, 8)
        );
    }

    /**
     * Log verification
     */
    private function logVerification(License $license, ?string $domain, string $method): void
    {
        \App\Services\LicenseVerificationLogger::log(
            purchaseCode: $license->purchase_code,
            domain: $domain ?? 'unknown',
            isValid: true,
            message: 'License verified successfully',
            source: 'api',
            responseData: ['method' => $method]
        );
    }
}