<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LicenseIntegrationFileRequest;
use App\Http\Requests\Api\LicenseVerifyRequest;
use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Services\EnvatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

/**
 * License API Controller with enhanced security.
 *
 * This controller handles license verification requests with support for both
 * local database licenses and Envato Market licenses. It provides comprehensive
 * license management and verification functionality with enhanced security measures.
 *
 * Features:
 * - License verification with local database and Envato API fallback
 * - Rate limiting and enhanced security measures
 * - Integration file generation for products
 * - Comprehensive error handling with database transactions
 * - Support for both system-generated and Envato Market licenses
 * - Domain verification and authorization
 * - Enhanced security measures (XSS protection, input validation, rate limiting)
 * - Proper logging for errors and warnings only
 * - Model scope integration for optimized queries
 * - Request class compatibility with comprehensive validation
 * - Authorization checks and middleware protection
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
class LicenseController extends Controller
{
    protected EnvatoService $envatoService;

    /**
     * Create a new controller instance.
     *
     * @param  EnvatoService  $envatoService  The Envato service for API integration
     *
     * @return void
     */
    public function __construct(EnvatoService $envatoService)
    {
        $this->envatoService = $envatoService;
    }

    /**
     * Verify license with enhanced security - checks local database first, then Envato as fallback.
     *
     * This method handles license verification requests by first checking the local
     * database for existing licenses, and if not found, falling back to Envato API
     * verification. It includes rate limiting, enhanced security, and comprehensive logging.
     *
     * @param  LicenseVerifyRequest  $request  The validated request containing purchase_code, product_slug, and domain
     *
     * @return JsonResponse JSON response with license verification result
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "purchase_code": "ABC123-DEF456-GHI789",
     *     "product_slug": "my-product",
     *     "domain": "example.com"
     * }
     *
     * // Success response:
     * {
     *     "valid": true,
     *     "source": "local",
     *     "license_type": "system_generated",
     *     "purchase_code": "ABC123-DEF456-GHI789",
     *     "product": {...},
     *     "domain_allowed": true,
     *     "status": "active"
     * }
     */
    public function verify(LicenseVerifyRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Get validated data from Request class
            $validated = $request->validated();
            // Extract sanitized inputs (already sanitized in Request class)
            $purchaseCode = $validated['purchase_code'];
            $productSlug = $validated['product_slug'];
            $domain = $validated['domain'];
            // Enhanced rate limiting with multiple keys
            $purchaseCodeStr = is_string($purchaseCode) ? $purchaseCode : '';
            $rateLimitKey = 'license-verify:' . $request->ip() . ':' . substr($purchaseCodeStr, 0, 8);
            $globalRateLimitKey = 'license-verify-global:' . $request->ip();
            if (
                RateLimiter::tooManyAttempts($rateLimitKey, 10) ||
                RateLimiter::tooManyAttempts($globalRateLimitKey, 50)
            ) {
                Log::warning('Rate limit exceeded for license verification', [
                    'purchase_code' => substr($purchaseCodeStr, 0, 4) . '...',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'rate_limit_key_attempts' => RateLimiter::attempts($rateLimitKey),
                    'global_rate_limit_attempts' => RateLimiter::attempts($globalRateLimitKey),
                ]);
                $response = [
                    'valid' => false,
                    'reason' => 'rate_limited',
                    'message' => 'Too many verification attempts. Please try again later.',
                ];
                $domainStr = is_string($domain) ? $domain : '';
                $this->logLicenseVerification(null, $domainStr, $request, $response, 'rate_limited');
                DB::commit();

                return response()->json($response, 429);
            }
            // Hit rate limiters
            RateLimiter::hit($rateLimitKey, 300); // 5 minutes
            RateLimiter::hit($globalRateLimitKey, 300); // 5 minutes
            // First, try to find license in local database
            $productSlugStr = is_string($productSlug) ? $productSlug : '';
            $domainStr = is_string($domain) ? $domain : '';
            $localLicense = $this->checkLocalLicense($purchaseCodeStr, $productSlugStr, $domainStr);
            if ($localLicense) {
                $response = [
                    'valid' => true,
                    'source' => 'local',
                    'license_type' => 'system_generated',
                    'purchase_code' => $localLicense->purchase_code ?? null,
                    'product' => property_exists($localLicense, 'product')
                        && $localLicense->product
                        && is_object($localLicense->product)
                        && method_exists($localLicense->product, 'only')
                        ? $localLicense->product->only(['id', 'name', 'slug', 'envato_item_id'])
                        : null,
                    'domain_allowed' => $localLicense->domain_allowed ?? null,
                    'status' => $localLicense->status ?? null,
                    'support_expires_at' => property_exists($localLicense, 'support_expires_at')
                        && is_object($localLicense->support_expires_at)
                        && method_exists($localLicense->support_expires_at, 'toDateString')
                        ? $localLicense->support_expires_at->toDateString()
                        : null,
                    'license_expires_at' => property_exists($localLicense, 'license_expires_at')
                        && is_object($localLicense->license_expires_at)
                        && method_exists($localLicense->license_expires_at, 'toDateString')
                        ? $localLicense->license_expires_at->toDateString()
                        : null,
                ];
                $this->logLicenseVerification(
                    $localLicense instanceof \App\Models\License ? $localLicense : null,
                    $domainStr,
                    $request,
                    $response,
                    'success'
                );
                DB::commit();

                return response()->json($response);
            }
            // If not found locally, check Envato
            $envatoResult = $this->checkEnvatoLicense($purchaseCodeStr, $productSlugStr, $domainStr);
            if ($envatoResult['valid']) {
                $response = [
                    'valid' => true,
                    'source' => 'envato',
                    'license_type' => 'envato_market',
                    'purchase_code' => $purchaseCode,
                    'product' => $envatoResult['product'],
                    'domain_allowed' => $envatoResult['domain_allowed'],
                    'status' => 'active',
                    'support_expires_at' => $envatoResult['support_expires_at'],
                    'license_expires_at' => $envatoResult['license_expires_at'],
                ];
                $this->logLicenseVerification(null, $domainStr, $request, $response, 'success');
                DB::commit();

                return response()->json($response);
            }
            // License not found in either source
            $response = [
                'valid' => false,
                'reason' => 'license_not_found',
                'message' => 'License not found in local database or Envato Market',
            ];
            $this->logLicenseVerification(null, $domainStr, $request, $response, 'failed');
            DB::commit();

            return response()->json($response, 404);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
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
                'reason' => 'internal_error',
                'message' => 'An error occurred while verifying the license',
            ], 500);
        }
    }

    /**
     * Check license in local database with enhanced security.
     *
     * Searches for an active license in the local database that matches the
     * provided purchase code, product slug, and domain using proper model scopes.
     *
     * @param  string  $purchaseCode  The purchase code to search for
     * @param  string  $productSlug  The product slug to match
     * @param  string  $domain  The domain to verify against
     *
     * @return object|null License object with domain_allowed flag, or null if not found
     *
     * @throws \Exception When database operations fail
     */
    private function checkLocalLicense(string $purchaseCode, string $productSlug, string $domain): ?object
    {
        try {
            $license = License::active()
                ->with(['product', 'domains'])
                ->where('purchase_code', $purchaseCode)
                ->whereHas('product', fn ($q) => $q->where('slug', $productSlug))
                ->first();
            if (! $license) {
                return null;
            }
            $domainAllowed = $license->domains()->where('domain', $domain)->exists();

            return (object)[
                'license' => $license,
                'domain_allowed' => $domainAllowed,
                'purchase_code' => $license->purchase_code,
                'product' => $license->product,
                'status' => $license->status,
                'support_expires_at' => $license->support_expires_at,
                'license_expires_at' => $license->license_expires_at,
            ];
        } catch (\Exception $e) {
            Log::error('Error checking local license', [
                'purchase_code' => substr($purchaseCode, 0, 4) . '...',
                'product_slug' => $productSlug,
                'domain' => $domain,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check license with Envato API with enhanced security.
     *
     * Verifies a license using the Envato API by checking the purchase code
     * against the Envato Market. Returns license information if valid.
     *
     * @param  string  $purchaseCode  The purchase code to verify
     * @param  string  $productSlug  The product slug to match
     * @param  string  $domain  The domain (not used for Envato verification)
     *
     * @return array<string, mixed> Array with 'valid' key and license information if valid
     *
     * @throws \Exception When database operations fail
     */
    private function checkEnvatoLicense(string $purchaseCode, string $productSlug, string $domain): array
    {
        try {
            // Find product by slug to get envato_item_id
            $product = Product::where('slug', $productSlug)->first();
            if (! $product || ! $product->envato_item_id) {
                Log::warning('Product not found or missing Envato item ID', [
                    'product_slug' => $productSlug,
                    'has_envato_id' => $product ? (bool)$product->envato_item_id : false,
                ]);

                return ['valid' => false];
            }
            // Verify with Envato
            $envatoData = $this->envatoService->verifyPurchase($purchaseCode);
            if (! $envatoData) {
                Log::warning('Envato verification failed', [
                    'purchase_code' => substr($purchaseCode, 0, 4) . '...',
                    'product_slug' => $productSlug,
                ]);

                return ['valid' => false];
            }
            // Check if the purchase belongs to the correct product
            $envatoItemIdValue = data_get($envatoData, 'item.id');
            $envatoItemId = is_string($envatoItemIdValue)
                ? $envatoItemIdValue
                : (is_scalar($envatoItemIdValue)
                    ? (string)$envatoItemIdValue
                    : '');
            if ((string)$product->envato_item_id !== $envatoItemId) {
                Log::warning('Envato item ID mismatch', [
                    'expected_item_id' => $product->envato_item_id,
                    'actual_item_id' => $envatoItemId,
                    'product_slug' => $productSlug,
                ]);

                return ['valid' => false];
            }

            // For Envato licenses, domain is always allowed (no domain restrictions)
            return [
                'valid' => true,
                'product' => $product->only(['id', 'name', 'slug', 'envato_item_id']),
                'domain_allowed' => true, // Envato licenses don't restrict domains
                'support_expires_at' => $envatoData['supported_until'] ?? null,
                'license_expires_at' => null, // Envato licenses don't expire
            ];
        } catch (\Exception $e) {
            Log::error('Envato license verification failed', [
                'purchase_code' => substr($purchaseCode, 0, 4) . '...',
                'product_slug' => $productSlug,
                'error' => $e->getMessage(),
            ]);

            return ['valid' => false];
        }
    }

    /**
     * Log license verification attempt.
     *
     * Creates a log entry for license verification attempts, including
     * request data, response data, and status information.
     *
     * @param  License|null  $license  The license object if found
     * @param  string  $domain  The domain being verified
     * @param  LicenseVerifyRequest  $request  The HTTP request object
     * @param  array  $response  The response data to log
     * @param  string  $status  The verification status (success, failed, rate_limited)
     */
    /**
     * @param array<string, mixed> $response
     */
    private function logLicenseVerification(
        ?License $license,
        string $domain,
        LicenseVerifyRequest $request,
        array $response,
        string $status,
    ): void {
        // Whitelist request fields to avoid storing sensitive or unexpected data
        $allowed = $request->only(['purchase_code', 'product_slug', 'domain', 'verification_key']);
        // Mask sensitive fields
        if (isset($allowed['purchase_code']) && is_string($allowed['purchase_code'])) {
            $allowed['purchase_code'] = substr($allowed['purchase_code'], 0, 4)
                . str_repeat('*', max(0, strlen($allowed['purchase_code']) - 4));
        }
        LicenseLog::create([
            'license_id' => $license?->id,
            'domain' => $domain,
            'ip_address' => $request->ip(),
            'serial' => $request->input('purchase_code'),
            'status' => $status,
            'user_agent' => $request->userAgent(),
            'request_data' => $allowed,
            'response_data' => $response,
        ]);
    }

    /**
     * Generate integration file for a product with enhanced security.
     *
     * Generates a PHP integration file that can be used by customers to
     * integrate license verification into their applications.
     *
     * @param  LicenseIntegrationFileRequest  $request  The validated request containing product_slug
     *
     * @return JsonResponse JSON response with integration file content
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "product_slug": "my-product"
     * }
     *
     * // Response:
     * {
     *     "product": {"name": "My Product", "slug": "my-product"},
     *     "integration_file": "<?php\nclass LicenseManager...",
     *     "filename": "license_integration_my-product.php"
     * }
     */
    public function generateIntegrationFile(LicenseIntegrationFileRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Get validated data from Request class
            $validated = $request->validated();
            // Extract sanitized input (already sanitized in Request class)
            $productSlug = $validated['product_slug'];
            // Rate limiting for integration file generation
            $rateLimitKey = 'license-integration:' . $request->ip();
            if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
                Log::warning('Rate limit exceeded for integration file generation', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'product_slug' => $productSlug,
                    'attempts' => RateLimiter::attempts($rateLimitKey),
                ]);
                DB::commit();

                return response()->json([
                    'error' => 'Too many requests. Please try again later.',
                ], 429);
            }
            RateLimiter::hit($rateLimitKey, 300); // 5 minutes
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                Log::warning('Product not found for integration file generation', [
                    'product_slug' => $productSlug,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json(['error' => 'Product not found'], 404);
            }
            $integrationCode = $this->generateIntegrationCode($product);
            DB::commit();

            return response()->json([
                'product' => [
                    'name' => $product->name,
                    'slug' => $product->slug,
                ],
                'integration_file' => $integrationCode,
                'filename' => 'license_integration_' . $product->slug . '.php',
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Integration file generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json(['error' => 'An error occurred while generating the integration file'], 500);
        }
    }

    /**
     * Generate PHP integration code for a product.
     *
     * Creates a complete PHP class that customers can use to integrate
     * license verification into their applications.
     *
     * @param  Product  $product  The product to generate integration code for
     *
     * @return string The complete PHP integration code
     */
    private function generateIntegrationCode(Product $product): string
    {
        $apiDomain = rtrim(is_string(config('app.url')) ? config('app.url') : '', '/');
        $verificationEndpoint = is_string(config('license.verification_endpoint'))
            ? config('license.verification_endpoint')
            : '/api/license/verify';
        $apiUrl = $apiDomain . '/' . ltrim($verificationEndpoint, '/');

        return "<?php
/**
 * License Integration for {$product->name}
 * Generated on " . now()->format('Y-m-d H:i:s') . "
 *
 * This file provides license verification functionality for {$product->name}
 * It supports both local system licenses and Envato Market licenses
 */
class LicenseManager
{
    private \$api_url = '{$apiUrl}';
    private \$product_slug = '{$product->slug}';
    private \$timeout = 30; // seconds
    /**
     * Verify license
     *
     * @param string \$license_key Purchase code or license key
     * @param string \$domain Domain to verify against
     * @return array Verification result
     */
    public function verifyLicense(\$license_key, \$domain = null)
    {
        if (empty(\$license_key)) {
            return [
                'valid' => false,
                'error' => 'License key is required'
            ];
        }
        // Use current domain if not provided
        if (\$domain === null) {
            \$domain = \$this->getCurrentDomain();
        }
        \$postData = [
            'purchase_code' => \$license_key,
            'product_slug'  => \$this->product_slug,
            'domain' => \$domain
        ];
        \$response = \$this->makeApiCall(\$this->api_url, \$postData);
        if (\$response === false) {
            return [
                'valid'  => false,
                'error' => 'Unable to verify license. Please check your internet connection.'
            ];
        }
        \$data = json_decode(\$response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'valid'  => false,
                'error' => 'Invalid response from license server'
            ];
        }
        if (!isset(\$data['valid'])) {
            return [
                'valid' => false,
                'error' => 'Invalid response format'
            ];
        }
        return \$data;
    }
    /**
     * Get current domain
     */
    private function getCurrentDomain()
    {
        // Use ServerHelper for safe domain retrieval
        return \App\Helpers\ServerHelper::getCurrentDomain();
    }
    /**
     * Make API call to license server
     */
    private function makeApiCall(\$url, \$data)
    {
        \$ch = curl_init();
        curl_setopt(\$ch, CURLOPT_URL, \$url);
        curl_setopt(\$ch, CURLOPT_POST, true);
        curl_setopt(\$ch, CURLOPT_POSTFIELDS, http_build_query(\$data));
        curl_setopt(\$ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(\$ch, CURLOPT_TIMEOUT, \$this->timeout);
        curl_setopt(\$ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt(\$ch, CURLOPT_USERAGENT, 'LicenseManager/1.0 ({$product->name})');
        \$response = curl_exec(\$ch);
        \$error = curl_error(\$ch);
        curl_close(\$ch);
        if (\$error) {
            return false;
        }
        return \$response;
    }
    /**
     * Check if license is valid and get details
     */
    public function getLicenseInfo(\$license_key, \$domain = null)
    {
        \$result = \$this->verifyLicense(\$license_key, \$domain);
        if (!\$result['valid']) {
            return false;
        }
        return [
            'license_key'  => \$license_key,
            'product' => \$result['product'] ?? null,
            'source' => \$result['source'] ?? 'unknown',
            'license_type' => \$result['license_type'] ?? 'unknown',
            'status' => \$result['status'] ?? 'unknown',
            'domain_allowed' => \$result['domain_allowed'] ?? false,
            'support_expires_at' => \$result['support_expires_at'] ?? null,
            'license_expires_at' => \$result['license_expires_at'] ?? null,
        ];
    }
}
// Usage example:
/*
\$licenseManager = new LicenseManager();
\$result = \$licenseManager->verifyLicense('YOUR_LICENSE_KEY');
if (\$result['valid']) {
    echo 'License is valid!';
    echo 'Source: ' . \$result['source'];
    echo 'Type: ' . \$result['license_type'];
} else {
    echo 'License is invalid: ' . (\$result['error'] ?? 'Unknown error');
}
*/
";
    }
}
