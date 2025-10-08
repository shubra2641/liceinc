<?php

declare(strict_types=1);

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
 *     "purchaseCode": "ABC123-DEF456-GHI789",
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
     * @param  LicenseVerifyRequest  $request  The validated request containing purchaseCode, product_slug, and domain
     *
     * @return JsonResponse JSON response with license verification result
     *
     * @throws ValidationException When validation fails
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "purchaseCode": "ABC123-DEF456-GHI789",
     *     "product_slug": "my-product",
     *     "domain": "example.com"
     * }
     *
     * // Success response:
     * {
     *     "valid": true,
     *     "source": "local",
     *     "licenseType": "system_generated",
     *     "purchaseCode": "ABC123-DEF456-GHI789",
     *     "product": {...},
     *     "domainAllowed": true,
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
            $purchaseCode = $validated['purchaseCode'];
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
                    'purchaseCode' => substr($purchaseCodeStr, 0, 4) . '...',
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
                    'licenseType' => 'system_generated',
                    'purchaseCode' => $localLicense->purchaseCode ?? null,
                    'product' => property_exists($localLicense, 'product') && $localLicense->product
                        && is_object($localLicense->product) && method_exists($localLicense->product, 'only')
                        ? $localLicense->product->only(['id', 'name', 'slug', 'envatoItemId'])
                        : null,
                    'domainAllowed' => $localLicense->domainAllowed ?? null,
                    'status' => $localLicense->status ?? null,
                    'supportExpiresAt' => property_exists($localLicense, 'supportExpiresAt')
                        && is_object($localLicense->supportExpiresAt)
                        && method_exists($localLicense->supportExpiresAt, 'toDateString')
                        ? $localLicense->supportExpiresAt->toDateString()
                        : null,
                    'licenseExpiresAt' => property_exists($localLicense, 'licenseExpiresAt')
                        && is_object($localLicense->licenseExpiresAt)
                        && method_exists($localLicense->licenseExpiresAt, 'toDateString')
                        ? $localLicense->licenseExpiresAt->toDateString()
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
                    'licenseType' => 'envato_market',
                    'purchaseCode' => $purchaseCode,
                    'product' => $envatoResult['product'],
                    'domainAllowed' => $envatoResult['domainAllowed'],
                    'status' => 'active',
                    'supportExpiresAt' => $envatoResult['supportExpiresAt'],
                    'licenseExpiresAt' => $envatoResult['licenseExpiresAt'],
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
     * @return object|null License object with domainAllowed flag, or null if not found
     *
     * @throws \Exception When database operations fail
     */
    private function checkLocalLicense(string $purchaseCode, string $productSlug, string $domain): ?object
    {
        try {
            $license = License::active()
                ->with(['product', 'domains'])
                ->where('purchaseCode', $purchaseCode)
                ->whereHas('product', fn ($q) => $q->where('slug', $productSlug))
                ->first();
            if (! $license) {
                return null;
            }
            $domainAllowed = $license->domains()->where('domain', $domain)->exists();

            return (object)[
                'license' => $license,
                'domainAllowed' => $domainAllowed,
                'purchaseCode' => $license->purchaseCode,
                'product' => $license->product,
                'status' => $license->status,
                'supportExpiresAt' => $license->supportExpiresAt,
                'licenseExpiresAt' => $license->licenseExpiresAt,
            ];
        } catch (\Exception $e) {
            Log::error('Error checking local license', [
                'purchaseCode' => substr($purchaseCode, 0, 4) . '...',
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
            // Find product by slug to get envatoItemId
            $product = Product::where('slug', $productSlug)->first();
            if (! $product || ! $product->envatoItemId) {
                Log::warning('Product not found or missing Envato item ID', [
                    'product_slug' => $productSlug,
                    'has_envatoId' => $product ? (bool)$product->envatoItemId : false,
                ]);

                return ['valid' => false];
            }
            // Verify with Envato
            $envatoData = $this->envatoService->verifyPurchase($purchaseCode);
            if (! $envatoData) {
                Log::warning('Envato verification failed', [
                    'purchaseCode' => substr($purchaseCode, 0, 4) . '...',
                    'product_slug' => $productSlug,
                ]);

                return ['valid' => false];
            }
            // Check if the purchase belongs to the correct product
            $envatoItemIdValue = data_get($envatoData, 'item.id');
            $envatoItemId = is_string($envatoItemIdValue)
                ? $envatoItemIdValue
                : (is_scalar($envatoItemIdValue) ? (string)$envatoItemIdValue : '');
            if (is_string($product->envatoItemId) && $product->envatoItemId !== $envatoItemId) {
                Log::warning('Envato item ID mismatch', [
                    'expected_item_id' => $product->envatoItemId,
                    'actual_item_id' => $envatoItemId,
                    'product_slug' => $productSlug,
                ]);

                return ['valid' => false];
            }

            // For Envato licenses, domain is always allowed (no domain restrictions)
            return [
                'valid' => true,
                'product' => $product->only(['id', 'name', 'slug', 'envatoItemId']),
                'domainAllowed' => true, // Envato licenses don't restrict domains
                'supportExpiresAt' => $envatoData['supported_until'] ?? null,
                'licenseExpiresAt' => null, // Envato licenses don't expire
            ];
        } catch (\Exception $e) {
            Log::error('Envato license verification failed', [
                'purchaseCode' => substr($purchaseCode, 0, 4) . '...',
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
        $allowed = $request->only(['purchaseCode', 'product_slug', 'domain', 'verification_key']);
        // Mask sensitive fields
        if (isset($allowed['purchaseCode']) && is_string($allowed['purchaseCode'])) {
            $allowed['purchaseCode'] = substr($allowed['purchaseCode'], 0, 4)
                . str_repeat('*', max(0, strlen($allowed['purchaseCode']) - 4));
        }
        LicenseLog::create([
            'licenseId' => $license?->id,
            'domain' => $domain,
            'ipAddress' => $request->ip(),
            'serial' => $request->input('purchaseCode'),
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
     *     "integration_file": "<?php
declare(strict_types=1);\nclass LicenseManager...",
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
declare(strict_types=1);
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
     * @param string \$licenseKey Purchase code or license key
     * @param string \$domain Domain to verify against
     * @return array Verification result
     */
    public function verifyLicense(\$licenseKey, \$domain = null)
    {
        if (empty(\$licenseKey)) {
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
            'purchaseCode' => \$licenseKey,
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
    public function getLicenseInfo(\$licenseKey, \$domain = null)
    {
        \$result = \$this->verifyLicense(\$licenseKey, \$domain);
        if (!\$result['valid']) {
            return false;
        }
        return [
            'licenseKey'  => \$licenseKey,
            'product' => \$result['product'] ?? null,
            'source' => \$result['source'] ?? 'unknown',
            'licenseType' => \$result['licenseType'] ?? 'unknown',
            'status' => \$result['status'] ?? 'unknown',
            'domainAllowed' => \$result['domainAllowed'] ?? false,
            'supportExpiresAt' => \$result['supportExpiresAt'] ?? null,
            'licenseExpiresAt' => \$result['licenseExpiresAt'] ?? null,
        ];
    }
}
// Usage example:
/*
\$licenseManager = new LicenseManager();
\$result = \$licenseManager->verifyLicense('YOUR_licenseKey');
if (\$result['valid']) {
    echo 'License is valid!';
    echo 'Source: ' . \$result['source'];
    echo 'Type: ' . \$result['licenseType'];
} else {
    echo 'License is invalid: ' . (\$result['error'] ?? 'Unknown error');
}
*/
";
    }
}
