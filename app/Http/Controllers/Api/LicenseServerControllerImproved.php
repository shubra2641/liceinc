<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckUpdatesRequest;
use App\Http\Requests\Api\GetLatestVersionRequest;
use App\Http\Requests\Api\GetUpdateInfoRequest;
use App\Models\License;
use App\Models\Product;
use App\Models\ProductUpdate;
use App\Services\License\LicenseVerificationService;
use App\Services\License\LicenseActivationService;
use App\Services\License\LicenseResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

/**
 * Improved License Server Controller
 * 
 * Simplified license server operations with better separation of concerns
 */
class LicenseServerControllerImproved extends Controller
{
    public function __construct(
        private LicenseVerificationService $verificationService,
        private LicenseActivationService $activationService,
        private LicenseResponseService $responseService
    ) {
    }

    /**
     * Check for updates
     */
    public function checkUpdates(CheckUpdatesRequest $request): JsonResponse
    {
        try {
            $this->applyRateLimit('license-update-check', $request->ip());
            
            DB::beginTransaction();
            
            $validated = $request->validated();
            $licenseKey = $validated['license_key'];
            $currentVersion = $validated['current_version'];
            $domain = $validated['domain'];
            $productSlug = $validated['product_slug'];
            
            // Verify license
            if (!$this->verifyLicense($licenseKey, $domain, $productSlug)) {
                DB::rollBack();
                return $this->responseService->forbidden('Invalid or expired license');
            }
            
            // Get product and update info
            $product = $this->getProductBySlug($productSlug);
            if (!$product) {
                DB::rollBack();
                return $this->responseService->notFound('Product not found');
            }
            
            $updateInfo = $this->getUpdateInfo($product, $currentVersion, $licenseKey, $productSlug);
            
            DB::commit();
            return $this->responseService->success($updateInfo);
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Update check failed', $e, $request);
            return $this->responseService->serverError('Failed to check for updates');
        }
    }

    /**
     * Get latest version
     */
    public function getLatestVersion(GetLatestVersionRequest $request): JsonResponse
    {
        try {
            $this->applyRateLimit('license-latest-version', $request->ip());
            
            $validated = $request->validated();
            $productSlug = $validated['product_slug'];
            
            $product = $this->getProductBySlug($productSlug);
            if (!$product) {
                return $this->responseService->notFound('Product not found');
            }
            
            $latestUpdate = $this->getLatestProductUpdate($product);
            if (!$latestUpdate) {
                return $this->responseService->success([
                    'version' => null,
                    'product' => $this->formatProductInfo($product),
                ]);
            }
            
            return $this->responseService->success([
                'version' => $latestUpdate->version,
                'title' => $latestUpdate->title,
                'description' => $latestUpdate->description,
                'released_at' => $latestUpdate->released_at?->toISOString(),
                'product' => $this->formatProductInfo($product),
            ]);
            
        } catch (\Exception $e) {
            $this->logError('Get latest version failed', $e, $request);
            return $this->responseService->serverError('Failed to get latest version');
        }
    }

    /**
     * Get update info
     */
    public function getUpdateInfo(GetUpdateInfoRequest $request): JsonResponse
    {
        try {
            $this->applyRateLimit('license-update-info', $request->ip());
            
            $validated = $request->validated();
            $licenseKey = $validated['license_key'];
            $version = $validated['version'];
            $productSlug = $validated['product_slug'];
            
            // Verify license
            if (!$this->verifyLicense($licenseKey, null, $productSlug)) {
                return $this->responseService->forbidden('Invalid or expired license');
            }
            
            $product = $this->getProductBySlug($productSlug);
            if (!$product) {
                return $this->responseService->notFound('Product not found');
            }
            
            $update = $this->getUpdateByVersion($product, $version);
            if (!$update) {
                return $this->responseService->notFound('Update not found');
            }
            
            return $this->responseService->success([
                'version' => $update->version,
                'title' => $update->title,
                'description' => $update->description,
                'changelog' => $update->changelog,
                'is_major' => $update->is_major,
                'is_required' => $update->is_required,
                'released_at' => $update->released_at?->toISOString(),
                'file_size' => $update->file_size,
                'download_url' => $this->generateDownloadUrl($licenseKey, $version, $productSlug),
            ]);
            
        } catch (\Exception $e) {
            $this->logError('Get update info failed', $e, $request);
            return $this->responseService->serverError('Failed to get update info');
        }
    }

    /**
     * Verify license
     */
    private function verifyLicense(string $licenseKey, ?string $domain, string $productSlug): bool
    {
        try {
            $result = $this->verificationService->verifyLicenseKey($licenseKey, $domain);
            return $result['status'] === 'success';
        } catch (\Exception $e) {
            Log::error('License verification failed', [
                'license_key' => substr($licenseKey, 0, 8) . '...',
                'domain' => $domain,
                'product_slug' => $productSlug,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get product by slug
     */
    private function getProductBySlug(string $slug): ?Product
    {
        return Product::where('slug', $slug)->first();
    }

    /**
     * Get update info for product
     */
    private function getUpdateInfo(Product $product, string $currentVersion, string $licenseKey, string $productSlug): array
    {
        $latestUpdate = $this->getLatestProductUpdate($product);
        
        if (!$latestUpdate) {
            return [
                'current_version' => $currentVersion,
                'latest_version' => $currentVersion,
                'is_update_available' => false,
                'update_info' => null,
                'product' => $this->formatProductInfo($product),
            ];
        }
        
        $isUpdateAvailable = $this->compareVersions($latestUpdate->version, $currentVersion) > 0;
        
        return [
            'current_version' => $currentVersion,
            'latest_version' => $latestUpdate->version,
            'is_update_available' => $isUpdateAvailable,
            'product' => $this->formatProductInfo($product),
            'update_info' => $isUpdateAvailable ? $this->formatUpdateInfo($latestUpdate, $licenseKey, $productSlug) : null,
        ];
    }

    /**
     * Get latest product update
     */
    private function getLatestProductUpdate(Product $product): ?ProductUpdate
    {
        return ProductUpdate::where('product_id', $product->id)
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Get update by version
     */
    private function getUpdateByVersion(Product $product, string $version): ?ProductUpdate
    {
        return ProductUpdate::where('product_id', $product->id)
            ->where('version', $version)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Format product info
     */
    private function formatProductInfo(Product $product): array
    {
        return [
            'name' => $product->name,
            'slug' => $product->slug,
        ];
    }

    /**
     * Format update info
     */
    private function formatUpdateInfo(ProductUpdate $update, string $licenseKey, string $productSlug): array
    {
        return [
            'version' => $update->version,
            'title' => $update->title,
            'description' => $update->description,
            'changelog' => $update->changelog,
            'is_major' => $update->is_major,
            'is_required' => $update->is_required,
            'released_at' => $update->released_at?->toISOString(),
            'file_size' => $update->file_size,
            'download_url' => $this->generateDownloadUrl($licenseKey, $update->version, $productSlug),
        ];
    }

    /**
     * Generate download URL
     */
    private function generateDownloadUrl(string $licenseKey, string $version, string $productSlug): string
    {
        return route('api.license.download-update', [
            'license_key' => $licenseKey,
            'version' => $version,
        ]) . '?product_slug=' . $productSlug;
    }

    /**
     * Compare versions
     */
    private function compareVersions(string $version1, string $version2): int
    {
        return version_compare($version1, $version2);
    }

    /**
     * Apply rate limiting
     */
    private function applyRateLimit(string $key, string $ip): void
    {
        $rateLimitKey = $key . ':' . $ip;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            abort(429, 'Too many attempts. Please try again later.');
        }
        RateLimiter::hit($rateLimitKey, 300); // 5 minutes
    }

    /**
     * Log error
     */
    private function logError(string $message, \Exception $e, Request $request): void
    {
        Log::error($message, [
            'error' => $e->getMessage(),
            'license_key' => substr($request->input('license_key', ''), 0, 8) . '...',
            'product_slug' => $request->input('product_slug', ''),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
