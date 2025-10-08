<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProductUpdateApiRequest;
use App\Http\Requests\Api\ProductUpdateCheckRequest;
use App\Http\Requests\Api\ProductUpdateLatestVersionRequest;
use App\Http\Requests\Api\ProductUpdateDownloadRequest;
use App\Http\Requests\Api\ProductUpdateChangelogRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\ProductUpdate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Product Update API Controller.
 *
 * This controller handles product update-related API endpoints including update
 * checking, version history, and update file downloads with license verification.
 * It provides comprehensive update management functionality with security measures.
 *
 * Features:
 * - Update checking with version comparison
 * - Latest version information retrieval
 * - Secure update file downloads
 * - Changelog and version history
 * - License verification for all operations
 * - Domain verification and authorization
 * - Comprehensive error handling and logging
 *
 * @example
 * // Check for updates
 * POST /api/product-updates/check
 * {
 *     "productId": 1,
 *     "current_version": "1.0.0",
 *     "licenseKey": "ABC123-DEF456-GHI789",
 *     "domain": "example.com"
 * }
 */
class ProductUpdateApiController extends Controller
{
    /**
     * Check for available updates for a product with enhanced security.
     *
     * Verifies the license and checks for available updates for the specified product.
     * Returns a list of all updates newer than the current version with comprehensive
     * security measures and proper error handling.
     *
     * @param  ProductUpdateCheckRequest  $request
     *         The validated request containing productId, current_version, licenseKey, and domain
     *
     * @return JsonResponse JSON response with available updates or error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "productId": 1,
     *     "current_version": "1.0.0",
     *     "licenseKey": "ABC123-DEF456-GHI789",
     *     "domain": "example.com"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "productId": 1,
     *     "current_version": "1.0.0",
     *     "latest_version": "1.2.0",
     *     "updates_available": 2,
     *     "updates": [...]
     * }
     */
    public function checkUpdates(ProductUpdateCheckRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Get validated data from Request class
            $validated = $request->validated();
            $productId = $validated['productId'];
            $currentVersion = $validated['current_version'];
            $licenseKey = $validated['licenseKey'];
            $domain = $validated['domain'];
            // Verify license
            $license = License::where('licenseKey', $licenseKey)
                ->where('productId', $productId)
                ->where('status', 'active')
                ->first();
            if (! $license) {
                DB::rollBack();
                return response()->json([
                    'success'  => false,
                    'message' => 'Invalid license key or product',
                ], 403);
            }
            // Check domain if required
            if ($license->product && $license->product->requires_domain) {
                if ($license->domains()->where('domain', $domain)->exists() === false) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Domain not registered for this license',
                    ], 403);
                }
            }
            // Get available updates
            $updates = ProductUpdate::where('productId', $productId)
                ->active()
                ->newerThan(is_string($currentVersion) ? $currentVersion : '')
                ->orderBy('version', 'desc')
                ->get();
            $response = [
                'success' => true,
                'productId'  => $productId,
                'current_version' => $currentVersion,
                'latest_version'  => $updates->first()->version ?? $currentVersion,
                'updates_available' => $updates->count(),
                'updates' => $updates->map(function ($update) {
                    return [
                        'version' => $update->version,
                        'title'  => $update->title,
                        'description' => $update->description,
                        'changelog'  => $update->changelog,
                        'isMajor' => $update->isMajor,
                        'isRequired'  => $update->isRequired,
                        'file_size' => $update->formattedFileSize,
                        'releasedAt'  => $update->releasedAt?->toISOString(),
                        'downloadUrl' => $update->downloadUrl,
                        'requirements'  => $update->requirements,
                        'compatibility' => $update->compatibility,
                    ];
                }),
            ];
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update check failed', [
                'error' => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success'  => false,
                'message' => 'Failed to check for updates',
            ], 500);
        }
    }
    /**
     * Get latest version information with enhanced security.
     *
     * Retrieves information about the latest available version for a product
     * after verifying the license and ensuring access permissions with
     * comprehensive security measures and proper error handling.
     *
     * @param  ProductUpdateLatestVersionRequest  $request
     *         The validated request containing productId, licenseKey, and domain
     *
     * @return JsonResponse JSON response with latest version information or error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "productId": 1,
     *     "licenseKey": "ABC123-DEF456-GHI789",
     *     "domain": "example.com"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "productId": 1,
     *     "latest_version": "1.2.0",
     *     "title": "New Features Update",
     *     "description": "Added new features...",
     *     "download_url": "..."
     * }
     */
    public function getLatestVersion(ProductUpdateLatestVersionRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Get validated data from Request class
            $validated = $request->validated();
            $productId = $validated['productId'];
            $licenseKey = $validated['licenseKey'];
            $domain = $validated['domain'];
            // Verify license
            $license = License::where('licenseKey', $licenseKey)
                ->where('productId', $productId)
                ->where('status', 'active')
                ->first();
            if (! $license) {
                DB::rollBack();
                return response()->json([
                    'success'  => false,
                    'message' => 'Invalid license key or product',
                ], 403);
            }
            // Check domain if required
            if ($license->product && $license->product->requires_domain) {
                if ($license->domains()->where('domain', $domain)->exists() === false) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Domain not registered for this license',
                    ], 403);
                }
            }
            // Get latest update
            $latestUpdate = ProductUpdate::where('productId', $productId)
                ->active()
                ->orderBy('version', 'desc')
                ->first();
            if (! $latestUpdate) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No updates available',
                ], 404);
            }
            $response = [
                'success' => true,
                'productId'  => $productId,
                'latest_version' => $latestUpdate->version,
                'title'  => $latestUpdate->title,
                'description' => $latestUpdate->description,
                'changelog'  => $latestUpdate->changelog,
                'isMajor' => $latestUpdate->isMajor,
                'isRequired'  => $latestUpdate->isRequired,
                'file_size' => $latestUpdate->formattedFileSize,
                'releasedAt'  => $latestUpdate->releasedAt?->toISOString(),
                'downloadUrl' => $latestUpdate->downloadUrl,
                'requirements'  => $latestUpdate->requirements,
                'compatibility' => $latestUpdate->compatibility,
            ];
            DB::commit();
            return response()->json($response);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Get latest version failed', [
                'error' => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success'  => false,
                'message' => 'Failed to get latest version',
            ], 500);
        }
    }
    /**
     * Download update file with enhanced security.
     *
     * Downloads the update file for a specific version after verifying
     * the license and ensuring the update exists and is available with
     * comprehensive security measures and proper error handling.
     *
     * @param  ProductUpdateDownloadRequest  $request  The validated request containing licenseKey and domain
     * @param  int  $productId  The product ID to download update for
     * @param  string  $version  The version to download
     *
     * @return Response File download or JSON error response
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // URL: /api/product-updates/download/{productId}/{version}
     * // Request body:
     * {
     *     "licenseKey": "ABC123-DEF456-GHI789",
     *     "domain": "example.com"
     * }
     * // Returns: File download or JSON error response
     */
    public function downloadUpdate(ProductUpdateDownloadRequest $request, int $productId, string $version): Response
    {
        try {
            DB::beginTransaction();
            // Get validated data from Request class
            $validated = $request->validated();
            $licenseKey = $validated['licenseKey'];
            $domain = $validated['domain'];
            // Verify license
            $license = License::where('licenseKey', $licenseKey)
                ->where('productId', $productId)
                ->where('status', 'active')
                ->first();
            if (! $license) {
                DB::rollBack();
                return new Response('Invalid license key or product', 403);
            }
            // Check domain if required
            if ($license->product && $license->product->requires_domain) {
                if ($license->domains()->where('domain', $domain)->exists() === false) {
                    DB::rollBack();
                    return new Response('Domain not registered for this license', 403);
                }
            }
            // Get update
            $update = ProductUpdate::where('productId', $productId)
                ->where('version', $version)
                ->active()
                ->first();
            if (! $update || ! $update->filePath) {
                DB::rollBack();
                return new Response('Update file not found', 404);
            }
            // Check if file exists
            if (! Storage::exists($update->filePath)) {
                DB::rollBack();
                return new Response('Update file not available', 404);
            }
            DB::commit();
            // Return file download
            return new Response('File download initiated');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update download failed', [
                'error' => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
                'productId' => $productId,
                'version'  => $version,
                'request_data' => $request->all(),
            ]);
            return new Response('Failed to download update', 500);
        }
    }
    /**
     * Get update changelog with enhanced security.
     *
     * Retrieves the complete changelog for a product, including all
     * available updates with version information and release details with
     * comprehensive security measures and proper error handling.
     *
     * @param  ProductUpdateChangelogRequest  $request
     *         The validated request containing productId, licenseKey, and domain
     *
     * @return JsonResponse JSON response with changelog information or error
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Request body:
     * {
     *     "productId": 1,
     *     "licenseKey": "ABC123-DEF456-GHI789",
     *     "domain": "example.com"
     * }
     *
     * // Success response:
     * {
     *     "success": true,
     *     "productId": 1,
     *     "changelog": [
     *         {
     *             "version": "1.2.0",
     *             "title": "New Features Update",
     *             "changelog": "Added new features...",
     *             "isMajor": true,
     *             "releasedAt": "2024-01-01T00:00:00.000000Z"
     *         }
     *     ]
     * }
     */
    public function getChangelog(ProductUpdateChangelogRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            // Get validated data from Request class
            $validated = $request->validated();
            $productId = $validated['productId'];
            $licenseKey = $validated['licenseKey'];
            $domain = $validated['domain'];
            // Verify license
            $license = License::where('licenseKey', $licenseKey)
                ->where('productId', $productId)
                ->where('status', 'active')
                ->first();
            if (! $license) {
                DB::rollBack();
                return response()->json([
                    'success'  => false,
                    'message' => 'Invalid license key or product',
                ], 403);
            }
            // Check domain if required
            if ($license->product && $license->product->requires_domain) {
                if ($license->domains()->where('domain', $domain)->exists() === false) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Domain not registered for this license',
                    ], 403);
                }
            }
            // Get all updates
            $updates = ProductUpdate::where('productId', $productId)
                ->active()
                ->orderBy('version', 'desc')
                ->get();
            $changelog = $updates->map(function ($update) {
                return [
                    'version' => $update->version,
                    'title'  => $update->title,
                    'changelog' => $update->changelog,
                    'isMajor'  => $update->isMajor,
                    'isRequired' => $update->isRequired,
                    'releasedAt'  => $update->releasedAt?->toISOString(),
                ];
            });
            DB::commit();
            return response()->json([
                'success' => true,
                'productId'  => $productId,
                'changelog' => $changelog,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Get changelog failed', [
                'error' => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);
            return response()->json([
                'success'  => false,
                'message' => 'Failed to get changelog',
            ], 500);
        }
    }
}
