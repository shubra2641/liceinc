<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ApiUpdateRequest;
use App\Http\Requests\Api\CheckUpdatesRequest;
use App\Http\Requests\Api\GetVersionHistoryRequest;
use App\Http\Requests\Api\GetLatestVersionRequest;
use App\Http\Requests\Api\GetUpdateInfoRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

/**
 * License Server Controller with Enhanced Security.
 *
 * This controller handles license server operations including update checking,
 * version history, file downloads, and license verification for the update system.
 * It provides comprehensive update management functionality with enhanced security measures.
 *
 * Features:
 * - Update checking and version comparison with rate limiting
 * - Version history and latest version information with enhanced security
 * - Secure update file downloads with security headers
 * - License verification for update access with database transactions
 * - Domain verification and auto-registration with enhanced validation
 * - Product discovery and information with rate limiting
 * - Comprehensive error handling and logging with enhanced security
 * - Request class validation for all endpoints
 * - Database transaction support for data integrity
 * - Rate limiting for all operations to prevent abuse
 *
 * @example
 * // Check for updates
 * POST /api/license/check-updates
 * {
 *     "license_key": "ABC123-DEF456-GHI789",
 *     "current_version": "1.0.0",
 *     "domain": "example.com",
 *     "product_slug": "my-product"
 * }
 */
class LicenseServerController extends Controller
{
    /**
     * Check for available updates for a license with enhanced security.
     *
     * Verifies the license and checks if there are any available updates
     * for the specified product. Returns update information if available.
     *
     * @param  CheckUpdatesRequest  $request  The validated request containing license data
     *
     * @return JsonResponse JSON response with update information
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "license_key": "ABC123-DEF456-GHI789",
     *     "current_version": "1.0.0",
     *     "domain": "example.com",
     *     "product_slug": "my-product"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "data": {
     *         "current_version": "1.0.0",
     *         "latest_version": "1.1.0",
     *         "is_update_available": true,
     *         "update_info": {...}
     *     }
     * }
     */
    public function checkUpdates(CheckUpdatesRequest $request): JsonResponse
    {
        // Rate limiting for update checks
        $key = 'license-update-check:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many update check attempts. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }
        RateLimiter::hit($key, 300); // 5 minutes
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $licenseKey = $validated['license_key'];
            $currentVersion = $validated['current_version'];
            $domain = $validated['domain'];
            $productSlug = $validated['product_slug'];
            // Verify license
            if (
                ! $this->verifyLicense(
                    is_string($licenseKey) ? $licenseKey : '',
                    is_string($domain) ? $domain : null,
                    is_string($productSlug) ? $productSlug : ''
                )
            ) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired license',
                    'error_code' => 'INVALID_LICENSE',
                ], 403);
            }
            // Get product
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND',
                ], 404);
            }
            // Get latest update
            $latestUpdate = ProductUpdate::where('product_id', $product->id)
                ->where('is_active', true)
                ->orderBy('version', 'desc')
                ->first();
            if (! $latestUpdate) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'current_version' => $currentVersion,
                        'latest_version' => $currentVersion,
                        'is_update_available' => false,
                        'update_info' => null,
                        'product' => [
                            'name' => $product->name,
                            'slug' => $product->slug,
                        ],
                    ],
                ]);
            }
            // Check if update is available
            $latestVersion = $latestUpdate->version;
            $currentVersionStr = $currentVersion;
            $isUpdateAvailable = $this->compareVersions(
                $latestVersion,
                is_string($currentVersionStr) ? $currentVersionStr : ''
            ) > 0;
            $responseData = [
                'current_version' => $currentVersion,
                'latest_version' => $latestUpdate->version,
                'is_update_available' => $isUpdateAvailable,
                'product' => [
                    'name' => $product->name,
                    'slug' => $product->slug,
                ],
                'update_info' => $isUpdateAvailable ? [
                    'version' => $latestUpdate->version,
                    'title' => $latestUpdate->title,
                    'description' => $latestUpdate->description,
                    'changelog' => $latestUpdate->changelog,
                    'is_major' => $latestUpdate->is_major,
                    'is_required' => $latestUpdate->is_required,
                    'released_at' => $latestUpdate->released_at?->toISOString(),
                    'file_size' => $latestUpdate->file_size,
                    'download_url' => route('api.license.download-update', [
                        'license_key' => $licenseKey,
                        'version' => $latestUpdate->version,
                    ]) . '?product_slug=' . (is_string($productSlug) ? $productSlug : ''),
                ] : null,
            ];
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $responseData,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update check failed', [
                'error' => $e->getMessage(),
                'license_key' => substr(
                    is_string($request->input('license_key', ''))
                        ? $request->input('license_key', '')
                        : '',
                    0,
                    8
                ) . '...',
                'product_slug' => $request->input('product_slug', ''),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check for updates. Please try again.',
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Get version history for a license with enhanced security.
     *
     * Retrieves the complete version history for a product, including all
     * available updates with download links and version information.
     *
     * @param  GetVersionHistoryRequest  $request  The validated request containing license data
     *
     * @return JsonResponse JSON response with version history
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "license_key": "ABC123-DEF456-GHI789",
     *     "domain": "example.com",
     *     "product_slug": "my-product"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "data": {
     *         "product": {...},
     *         "versions": [...]
     *     }
     * }
     */
    public function getVersionHistory(GetVersionHistoryRequest $request): JsonResponse
    {
        // Rate limiting for version history requests
        $key = 'license-version-history:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many version history requests. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }
        RateLimiter::hit($key, 600); // 10 minutes
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $licenseKey = $validated['license_key'];
            $domain = $validated['domain'];
            $productSlug = $validated['product_slug'];
            // Verify license
            if (
                ! $this->verifyLicense(
                    is_string($licenseKey) ? $licenseKey : '',
                    is_string($domain) ? $domain : null,
                    is_string($productSlug) ? $productSlug : ''
                )
            ) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired license',
                    'error_code' => 'INVALID_LICENSE',
                ], 403);
            }
            // Get product
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND',
                ], 404);
            }
            // Get all updates
            $updates = ProductUpdate::where('product_id', $product->id)
                ->where('is_active', true)
                ->orderBy('version', 'desc')
                ->get()
                ->map(function ($update) use ($licenseKey, $productSlug) {
                    return [
                        'version' => $update->version,
                        'title' => $update->title,
                        'description' => $update->description,
                        'changelog' => $update->changelog,
                        'is_major' => $update->is_major,
                        'is_required' => $update->is_required,
                        'released_at' => $update->released_at?->toISOString(),
                        'file_size' => $update->file_size,
                        'download_url' => route('api.license.download-update', [
                            'license_key' => $licenseKey,
                            'version' => $update->version,
                        ]) . '?product_slug=' . (is_string($productSlug) ? $productSlug : ''),
                    ];
                });
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => [
                        'name' => $product->name,
                        'slug' => $product->slug,
                        'current_version' => $product->current_version,
                    ],
                    'versions' => $updates,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Version history request failed', [
                'error' => $e->getMessage(),
                'license_key' => substr(
                    is_string($request->input('license_key', ''))
                        ? $request->input('license_key', '')
                        : '',
                    0,
                    8
                ) . '...',
                'product_slug' => $request->input('product_slug', ''),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get version history. Please try again.',
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Download update file with enhanced security and rate limiting.
     *
     * Downloads the update file for a specific version after verifying
     * the license and ensuring the update exists and is available.
     *
     * @param  Request  $request  The HTTP request containing domain and product_slug
     * @param  string  $licenseKey  The license key for verification
     * @param  string  $version  The version to download
     *
     * @return Response|JsonResponse File download or error response
     *
     * @throws \Exception When download operations fail
     *
     * @example
     * // URL: /api/license/download-update/{licenseKey}/{version}?product_slug=my-product&domain=example.com
     * // Returns: File download or JSON error response
     */
    public function downloadUpdate(Request $request, string $licenseKey, string $version): Response|JsonResponse
    {
        // Rate limiting for file downloads
        $key = 'license-file-download:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many download attempts. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }
        RateLimiter::hit($key, 600); // 10 minutes
        try {
            DB::beginTransaction();
            $domain = $request->input('domain');
            $productSlug = $request->input('product_slug');
            // Validate product_slug is required
            if (! $productSlug) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Product slug is required',
                    'error_code' => 'PRODUCT_SLUG_REQUIRED',
                ], 422);
            }
            // Verify license
            $licenseKeyStr = $licenseKey;
            $domainStr = $domain;
            $productSlugStr = $productSlug;
            if (
                ! $this->verifyLicense(
                    $licenseKeyStr,
                    is_string($domainStr) ? $domainStr : null,
                    is_string($productSlugStr) ? $productSlugStr : ''
                )
            ) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired license',
                    'error_code' => 'INVALID_LICENSE',
                ], 403);
            }
            // Get product
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND',
                ], 404);
            }
            // Get update
            $update = ProductUpdate::where('product_id', $product->id)
                ->where('version', $version)
                ->where('is_active', true)
                ->first();
            if (! $update) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Update not found',
                    'error_code' => 'UPDATE_NOT_FOUND',
                ], 404);
            }
            if (! $update->file_path || ! Storage::exists($update->file_path)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Update file not available',
                    'error_code' => 'FILE_NOT_AVAILABLE',
                ], 404);
            }
            DB::commit();

            // Return file download with security headers
            $response = Storage::download($update->file_path, $update->file_name ?? "update_{$version}.zip");
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
            return new JsonResponse(['success' => true, 'download_url' => $update->file_path]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update download failed', [
                'error' => $e->getMessage(),
                'license_key' => substr($licenseKey, 0, 8) . '...',
                'product_slug' => $request->input('product_slug', ''),
                'version' => $version,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download update. Please try again.',
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Get latest version info with enhanced security.
     *
     * Retrieves information about the latest available version for a product
     * after verifying the license and ensuring access permissions.
     *
     * @param  GetLatestVersionRequest  $request  The validated request containing license data
     *
     * @return JsonResponse JSON response with latest version information
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "license_key": "ABC123-DEF456-GHI789",
     *     "domain": "example.com",
     *     "product_slug": "my-product"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "data": {
     *         "product": {...},
     *         "version": "1.1.0",
     *         "title": "New Features Update",
     *         "download_url": "..."
     *     }
     * }
     */
    public function getLatestVersion(GetLatestVersionRequest $request): JsonResponse
    {
        // Rate limiting for latest version requests
        $key = 'license-latest-version:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 15)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many latest version requests. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }
        RateLimiter::hit($key, 300); // 5 minutes
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $licenseKey = $validated['license_key'];
            $domain = $validated['domain'];
            $productSlug = $validated['product_slug'];
            // Verify license
            if (
                ! $this->verifyLicense(
                    is_string($licenseKey) ? $licenseKey : '',
                    is_string($domain) ? $domain : null,
                    is_string($productSlug) ? $productSlug : ''
                )
            ) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired license',
                    'error_code' => 'INVALID_LICENSE',
                ], 403);
            }
            // Get product
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND',
                ], 404);
            }
            // Get latest update
            $latestUpdate = ProductUpdate::where('product_id', $product->id)
                ->where('is_active', true)
                ->orderBy('version', 'desc')
                ->first();
            if (! $latestUpdate) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No updates available',
                    'error_code' => 'NO_UPDATES',
                ], 404);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => [
                        'name' => $product->name,
                        'slug' => $product->slug,
                    ],
                    'version' => $latestUpdate->version,
                    'title' => $latestUpdate->title,
                    'description' => $latestUpdate->description,
                    'changelog' => $latestUpdate->changelog,
                    'is_major' => $latestUpdate->is_major,
                    'is_required' => $latestUpdate->is_required,
                    'released_at' => $latestUpdate->released_at?->toISOString(),
                    'file_size' => $latestUpdate->file_size,
                    'download_url' => route('api.license.download-update', [
                        'license_key' => $licenseKey,
                        'version' => $latestUpdate->version,
                    ]) . '?product_slug=' . (is_string($productSlug) ? $productSlug : ''),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Latest version request failed', [
                'error' => $e->getMessage(),
                'license_key' => substr(
                    is_string($request->input('license_key', ''))
                        ? $request->input('license_key', '')
                        : '',
                    0,
                    8
                ) . '...',
                'product_slug' => $request->input('product_slug', ''),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get latest version. Please try again.',
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Get update information without license verification with enhanced security.
     *
     * Retrieves update information for a product without requiring license
     * verification. This is useful for public update checking.
     *
     * @param  GetUpdateInfoRequest  $request  The validated request containing product data
     *
     * @return JsonResponse JSON response with update information
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "product_slug": "my-product",
     *     "current_version": "1.0.0"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "data": {
     *         "is_update_available": true,
     *         "current_version": "1.0.0",
     *         "next_version": "1.1.0",
     *         "update_info": {...}
     *     }
     * }
     */
    public function getUpdateInfo(GetUpdateInfoRequest $request): JsonResponse
    {
        // Rate limiting for update info requests
        $key = sprintf('license-update-info:%s', $request->ip()); // security-ignore: SQL_STRING_CONCAT
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many update info requests. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }
        RateLimiter::hit($key, 300); // 5 minutes
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $productSlug = $validated['product_slug'];
            $currentVersion = $validated['current_version'];
            // Get product
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'error_code' => 'PRODUCT_NOT_FOUND',
                ], 404);
            }
            // Get the next sequential update (not the latest)
            $nextUpdate = ProductUpdate::where('product_id', $product->id)
                ->where('is_active', true)
                ->whereRaw('version > ?', [$currentVersion])
                ->orderBy('version', 'asc') // Get the next version, not the latest
                ->first();
            if (! $nextUpdate) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No updates available',
                    'error_code' => 'NO_UPDATES',
                ], 404);
            }
            // Check if update is available and validate version progression
            $isUpdateAvailable = version_compare(
                $nextUpdate->version,
                is_string($currentVersion) ? $currentVersion : '',
                '>'
            );
            // Additional validation: Ensure the update is actually newer
            if ($isUpdateAvailable) {
                $versionComparison = version_compare(
                    $nextUpdate->version,
                    is_string($currentVersion) ? $currentVersion : ''
                );
                // If not newer, mark as no update available
                if ($versionComparison <= 0) {
                    $isUpdateAvailable = false;
                }
            }
            $responseData = [
                'success' => true,
                'data' => [
                    'is_update_available' => $isUpdateAvailable,
                    'current_version' => $currentVersion,
                    'next_version' => $nextUpdate->version,
                    'message' => $isUpdateAvailable ? 'Update available' : 'No newer updates available',
                    'update_info' => $isUpdateAvailable ? [
                        'title' => $nextUpdate->title,
                        'description' => $nextUpdate->description,
                        'changelog' => $nextUpdate->changelog,
                        'is_major' => $nextUpdate->is_major,
                        'is_required' => $nextUpdate->is_required,
                        'release_date' => $nextUpdate->release_date,
                        'file_size' => $nextUpdate->file_size,
                        'download_url' => route('api.license.download-update', [
                            'license_key' => 'REQUIRED',
                            'version' => $nextUpdate->version,
                        ]),
                    ] : null,
                ],
            ];
            DB::commit();

            return response()->json($responseData);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update info exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching update info. Please try again.',
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Get all available products (for discovery) with enhanced security.
     *
     * Retrieves a list of all active products available in the system.
     * This endpoint is useful for product discovery and listing.
     *
     * @param  Request  $request  The HTTP request (no parameters required)
     *
     * @return JsonResponse JSON response with product list
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request: GET /api/license/products
     * // Success response:
     * {
     *     "success": true,
     *     "data": {
     *         "products": [
     *             {
     *                 "id": 1,
     *                 "name": "My Product",
     *                 "slug": "my-product",
     *                 "description": "Product description",
     *                 "version": "1.0.0"
     *             }
     *         ]
     *     }
     * }
     */
    public function getProducts(Request $request): JsonResponse
    {
        // Rate limiting for product discovery
        $key = 'license-products:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 50)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many product requests. Please try again later.',
                'error_code' => 'RATE_LIMIT_EXCEEDED',
            ], 429);
        }
        RateLimiter::hit($key, 300); // 5 minutes
        try {
            DB::beginTransaction();
            $products = Product::where('is_active', true)
                ->select(['id', 'name', 'slug', 'description', 'version'])
                ->get();
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Get products request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get products. Please try again.',
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Verify license validity using database lookup with enhanced security.
     *
     * Verifies a license by checking the database for the license key,
     * product association, status, expiration, and domain authorization.
     *
     * @param  string  $licenseKey  The license key to verify
     * @param  string|null  $domain  The domain to verify against (optional)
     * @param  string  $productSlug  The product slug to match
     *
     * @return bool True if license is valid, false otherwise
     *
     * @throws \Exception When license verification fails
     */
    private function verifyLicense(string $licenseKey, ?string $domain, string $productSlug): bool
    {
        try {
            // Get product
            $product = Product::where('slug', $productSlug)->first();
            if (! $product) {
                Log::warning('Product not found for license verification', [
                    'license_key' => substr($licenseKey, 0, 8) . '...',
                    'product_slug' => $productSlug,
                ]);

                return false;
            }
            // Find license by license_key (which is same as purchase_code in our system)
            $license = License::where('license_key', $licenseKey)
                ->where('product_id', $product->id)
                ->first();
            if (! $license) {
                Log::warning('License not found in database', [
                    'license_key' => substr($licenseKey, 0, 8) . '...',
                    'product_slug' => $productSlug,
                ]);

                return false;
            }
            // Check if license is active
            if ($license->status !== 'active') {
                Log::warning('License is not active', [
                    'license_key' => substr($licenseKey, 0, 8) . '...',
                    'status' => $license->status,
                ]);

                return false;
            }
            // Check if license has expired
            if ($license->license_expires_at && $license->license_expires_at->isPast()) {
                Log::warning('License has expired', [
                    'license_key' => substr($licenseKey, 0, 8) . '...',
                    'expires_at' => $license->license_expires_at->toISOString(),
                ]);

                return false;
            }
            // Check domain if provided
            if ($domain) {
                // Check if auto domain registration is enabled
                $autoRegisterDomains = \App\Helpers\ConfigHelper::getSetting('license_auto_register_domains', false);
                $isTestMode = config('app.env') === 'local' || config('app.debug') === true;
                if ($autoRegisterDomains || $isTestMode) {
                    // Auto register mode: Register domain automatically
                    try {
                        $this->registerDomainForLicense($license, $domain);
                    } catch (\Exception $e) {
                        Log::warning('Domain limit exceeded', [
                            'license_key' => substr($licenseKey, 0, 8) . '...',
                            'domain' => $domain,
                            'error' => $e->getMessage(),
                        ]);

                        return false;
                    }
                } else {
                    // Verification mode: Verify domain authorization
                    if (! $this->verifyDomain($license, $domain)) {
                        Log::warning('Domain not authorized for this license', [
                            'license_key' => substr($licenseKey, 0, 8) . '...',
                            'domain' => $domain,
                        ]);

                        return false;
                    }
                }
            }

            // License verified successfully
            return true;
        } catch (\Exception $e) {
            Log::error('License verification exception', [
                'error' => $e->getMessage(),
                'license_key' => substr($licenseKey, 0, 8) . '...',
                'domain' => $domain,
                'product_slug' => $productSlug,
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Verify domain authorization with enhanced security.
     *
     * Checks if a domain is authorized for a specific license, including
     * wildcard domain support and auto-registration capabilities.
     *
     * @param  License  $license  The license to check against
     * @param  string  $domain  The domain to verify
     *
     * @return bool True if domain is authorized, false otherwise
     */
    private function verifyDomain(License $license, string $domain): bool
    {
        // Remove protocol and www
        $domain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;
        $domain = preg_replace('/^www\./', '', $domain) ?? $domain;
        $authorizedDomains = $license->domains()->where('status', 'active')->get();
        // If no domains are configured, check if we can register the current domain
        if ($authorizedDomains->isEmpty()) {
            // Check domain limit before auto-registering
            try {
                $this->checkDomainLimit($license, $domain);
                $this->registerDomainForLicense($license, $domain);

                return true;
            } catch (\Exception $e) {
                Log::warning(
                    'Cannot auto-register domain due to limit',
                    [
                    'license_id' => $license->id,
                    'domain' => $domain,
                    'error' => $e->getMessage(),
                    'ip' => request()->ip(),
                    ]
                );

                return false;
            }
        }
        foreach ($authorizedDomains as $authorizedDomain) {
            $authDomain = preg_replace(
                '/^https?:\/\//',
                '',
                $authorizedDomain->domain ?? ''
            ) ?? $authorizedDomain->domain ?? '';
            $authDomain = preg_replace('/^www\./', '', $authDomain) ?? $authDomain;
            if ($authDomain === $domain) {
                // Update last used timestamp
                $authorizedDomain->update(['last_used_at' => now()]);

                return true;
            }
            // Check wildcard domains
            if ($authDomain && str_starts_with($authDomain, '*.')) {
                $pattern = str_replace('*.', '', $authDomain);
                if ($domain && str_ends_with($domain, $pattern)) {
                    // Update last used timestamp
                    $authorizedDomain->update(['last_used_at' => now()]);

                    return true;
                }
            }
        }

        // Domain not found in authorized domains
        return false;
    }

    /**
     * Register domain for license automatically with enhanced security.
     *
     * Automatically registers a domain for a license, checking domain limits
     * and creating the domain record if it doesn't already exist.
     *
     * @param  License  $license  The license to register the domain for
     * @param  string  $domain  The domain to register
     *
     * @throws \Exception If domain limit is exceeded
     */
    private function registerDomainForLicense(License $license, string $domain): void
    {
        // Clean domain (remove protocol and www)
        $cleanDomain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;
        $cleanDomain = preg_replace('/^www\./', '', $cleanDomain) ?? $cleanDomain;
        // Check if domain already exists for this license
        $existingDomain = $license->domains()
            ->where('domain', $cleanDomain)
            ->first();
        if ($existingDomain) {
            // Update last used timestamp
            $existingDomain->update(['last_used_at' => now()]);
        // Domain already exists, updated last used timestamp
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
        }
    }

    /**
     * Check if license has reached its domain limit with enhanced security.
     *
     * Verifies if a license has reached its maximum allowed domain count
     * and throws an exception if the limit is exceeded.
     *
     * @param  License  $license  The license to check
     * @param  string  $domain  The domain being added
     *
     * @throws \Exception If domain limit is exceeded
     */
    private function checkDomainLimit(License $license, string $domain): void
    {
        if ($license->hasReachedDomainLimit() === true) {
            Log::warning('Domain limit exceeded for license', [
                'license_id' => $license->id,
                'purchase_code' => substr($license->purchase_code, 0, 8) . '...',
                'domain' => $domain,
                'current_domains' => $license->active_domains_count,
                'max_domains' => $license->max_domains ?? 1,
                'license_type' => $license->license_type,
                'ip' => request()->ip(),
            ]);
            $maxDomains = $license->max_domains ?? 1;
            throw new \Exception("License has reached its maximum domain limit ({$maxDomains} domain"
                . ($maxDomains > 1 ? 's' : '') . "). Cannot register new domain: {$domain}");
        }
    }

    /**
     * Compare two version strings with enhanced validation.
     *
     * Compares two semantic version strings and returns the comparison result.
     * Supports standard semantic versioning format (e.g., 1.0.0, 2.1.3).
     *
     * @param  string  $version1  The first version to compare
     * @param  string  $version2  The second version to compare
     *
     * @return int Returns 1 if version1 > version2, -1 if version1 < version2, 0 if equal
     *
     * @example
     * $result = $this->compareVersions('1.2.0', '1.1.0'); // Returns 1
     * $result = $this->compareVersions('1.0.0', '1.0.0'); // Returns 0
     * $result = $this->compareVersions('1.0.0', '1.1.0'); // Returns -1
     */
    private function compareVersions(string $version1, string $version2): int
    {
        $v1Parts = array_map('intval', explode('.', $version1));
        $v2Parts = array_map('intval', explode('.', $version2));
        $maxLength = max(count($v1Parts), count($v2Parts));
        for ($i = 0; $i < $maxLength; $i++) {
            $v1Part = $v1Parts[$i] ?? 0;
            $v2Part = $v2Parts[$i] ?? 0;
            if ($v1Part > $v2Part) {
                return 1;
            } elseif ($v1Part < $v2Part) {
                return -1;
            }
        }

        return 0;
    }
}
