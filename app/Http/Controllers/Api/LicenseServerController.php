<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
 * Simplified License Server Controller
 */
class LicenseServerController extends Controller
{
    /**
     * Check for updates
     */
    public function checkUpdates(CheckUpdatesRequest $request): JsonResponse
    {
        try {
            $this->checkRateLimit('update-check', $request->ip(), 20);
            
            $data = $request->validated();
            $licenseKey = $data['license_key'];
            $currentVersion = $data['current_version'];
            $domain = $data['domain'];
            $productSlug = $data['product_slug'];

            if (!$this->verifyLicense($licenseKey, $domain, $productSlug)) {
                return $this->errorResponse('Invalid license', 'INVALID_LICENSE', 403);
            }

            $product = $this->getProduct($productSlug);
            $latestUpdate = $this->getLatestUpdate($product->id);

            if (!$latestUpdate) {
                return $this->successResponse([
                    'current_version' => $currentVersion,
                    'latest_version' => $currentVersion,
                    'is_update_available' => false,
                    'update_info' => null,
                    'product' => $this->getProductInfo($product),
                ]);
            }

            $isUpdateAvailable = $this->compareVersions($latestUpdate->version, $currentVersion) > 0;

            return $this->successResponse([
                'current_version' => $currentVersion,
                'latest_version' => $latestUpdate->version,
                'is_update_available' => $isUpdateAvailable,
                'product' => $this->getProductInfo($product),
                'update_info' => $isUpdateAvailable ? $this->getUpdateInfo($latestUpdate, $licenseKey, $productSlug) : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Update check failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Update check failed', 'SERVER_ERROR', 500);
        }
    }

    /**
     * Get version history
     */
    public function getVersionHistory(GetVersionHistoryRequest $request): JsonResponse
    {
        try {
            $this->checkRateLimit('version-history', $request->ip(), 10);
            
            $data = $request->validated();
            if (!$this->verifyLicense($data['license_key'], $data['domain'], $data['product_slug'])) {
                return $this->errorResponse('Invalid license', 'INVALID_LICENSE', 403);
            }

            $product = $this->getProduct($data['product_slug']);
            $updates = $this->getAllUpdates($product->id, $data['license_key'], $data['product_slug']);

            return $this->successResponse([
                'product' => $this->getProductInfo($product),
                'versions' => $updates,
            ]);
        } catch (\Exception $e) {
            Log::error('Version history failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Version history failed', 'SERVER_ERROR', 500);
        }
    }

    /**
     * Download update file
     */
    public function downloadUpdate(Request $request, string $licenseKey, string $version): Response|JsonResponse
    {
        try {
            $this->checkRateLimit('file-download', $request->ip(), 5);
            
            $domain = $request->input('domain');
            $productSlug = $request->input('product_slug');

            if (!$productSlug) {
                return $this->errorResponse('Product slug required', 'PRODUCT_SLUG_REQUIRED', 422);
            }

            if (!$this->verifyLicense($licenseKey, $domain, $productSlug)) {
                return $this->errorResponse('Invalid license', 'INVALID_LICENSE', 403);
            }

            $product = $this->getProduct($productSlug);
            $update = $this->getUpdateByVersion($product->id, $version);

            if (!$update || !Storage::exists($update->file_path)) {
                return $this->errorResponse('Update not found', 'UPDATE_NOT_FOUND', 404);
            }

            return Storage::download($update->file_path, $update->file_name ?? "update_{$version}.zip");
        } catch (\Exception $e) {
            Log::error('Download failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Download failed', 'SERVER_ERROR', 500);
        }
    }

    /**
     * Get latest version
     */
    public function getLatestVersion(GetLatestVersionRequest $request): JsonResponse
    {
        try {
            $this->checkRateLimit('latest-version', $request->ip(), 15);
            
            $data = $request->validated();
            if (!$this->verifyLicense($data['license_key'], $data['domain'], $data['product_slug'])) {
                return $this->errorResponse('Invalid license', 'INVALID_LICENSE', 403);
            }

            $product = $this->getProduct($data['product_slug']);
            $latestUpdate = $this->getLatestUpdate($product->id);

            if (!$latestUpdate) {
                return $this->errorResponse('No updates available', 'NO_UPDATES', 404);
            }

            return $this->successResponse([
                'product' => $this->getProductInfo($product),
                'version' => $latestUpdate->version,
                'title' => $latestUpdate->title,
                'description' => $latestUpdate->description,
                'changelog' => $latestUpdate->changelog,
                'is_major' => $latestUpdate->is_major,
                'is_required' => $latestUpdate->is_required,
                'released_at' => $latestUpdate->released_at?->toISOString(),
                'file_size' => $latestUpdate->file_size,
                'download_url' => $this->getDownloadUrl($latestUpdate->version, $data['license_key'], $data['product_slug']),
            ]);
        } catch (\Exception $e) {
            Log::error('Latest version failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Latest version failed', 'SERVER_ERROR', 500);
        }
    }

    /**
     * Get update info without license
     */
    public function getUpdateInfo(GetUpdateInfoRequest $request): JsonResponse
    {
        try {
            $this->checkRateLimit('update-info', $request->ip(), 30);
            
            $data = $request->validated();
            $product = $this->getProduct($data['product_slug']);
            $nextUpdate = $this->getNextUpdate($product->id, $data['current_version']);

            if (!$nextUpdate) {
                return $this->errorResponse('No updates available', 'NO_UPDATES', 404);
            }

            $isUpdateAvailable = version_compare($nextUpdate->version, $data['current_version'], '>');

            return $this->successResponse([
                'is_update_available' => $isUpdateAvailable,
                'current_version' => $data['current_version'],
                'next_version' => $nextUpdate->version,
                'message' => $isUpdateAvailable ? 'Update available' : 'No newer updates available',
                'update_info' => $isUpdateAvailable ? $this->getUpdateInfoData($nextUpdate) : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Update info failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Update info failed', 'SERVER_ERROR', 500);
        }
    }

    /**
     * Get all products
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            $this->checkRateLimit('products', $request->ip(), 50);
            
            $products = Product::where('is_active', true)
                ->select(['id', 'name', 'slug', 'description', 'version'])
                ->get();

            return $this->successResponse(['products' => $products]);
        } catch (\Exception $e) {
            Log::error('Get products failed', ['error' => $e->getMessage()]);
            return $this->errorResponse('Get products failed', 'SERVER_ERROR', 500);
        }
    }

    /**
     * Verify license
     */
    private function verifyLicense(string $licenseKey, ?string $domain, string $productSlug): bool
    {
        try {
            $product = Product::where('slug', $productSlug)->first();
            if (!$product) {
                return false;
            }

            $license = License::where('license_key', $licenseKey)
                ->where('product_id', $product->id)
                ->first();

            if (!$license || $license->status !== 'active') {
                return false;
            }

            if ($license->license_expires_at && $license->license_expires_at->isPast()) {
                return false;
            }

            if ($domain) {
                return $this->verifyDomain($license, $domain);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('License verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verify domain
     */
    private function verifyDomain(License $license, string $domain): bool
    {
        $cleanDomain = $this->cleanDomain($domain);
        $authorizedDomains = $license->domains()->where('status', 'active')->get();

        if ($authorizedDomains->isEmpty()) {
            return $this->autoRegisterDomain($license, $cleanDomain);
        }

        foreach ($authorizedDomains as $authDomain) {
            if ($this->isDomainMatch($cleanDomain, $authDomain->domain)) {
                $authDomain->update(['last_used_at' => now()]);
                return true;
            }
        }

        return false;
    }

    /**
     * Auto register domain
     */
    private function autoRegisterDomain(License $license, string $domain): bool
    {
        try {
            if ($license->hasReachedDomainLimit()) {
                return false;
            }

            $existingDomain = $license->domains()->where('domain', $domain)->first();
            if ($existingDomain) {
                $existingDomain->update(['last_used_at' => now()]);
                return true;
            }

            $license->domains()->create([
                'domain' => $domain,
                'status' => 'active',
                'added_at' => now(),
                'last_used_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Domain registration failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get product by slug
     */
    private function getProduct(string $slug): Product
    {
        $product = Product::where('slug', $slug)->first();
        if (!$product) {
            throw new \Exception('Product not found');
        }
        return $product;
    }

    /**
     * Get latest update
     */
    private function getLatestUpdate(int $productId): ?ProductUpdate
    {
        return ProductUpdate::where('product_id', $productId)
            ->where('is_active', true)
            ->orderBy('version', 'desc')
            ->first();
    }

    /**
     * Get all updates
     */
    private function getAllUpdates(int $productId, string $licenseKey, string $productSlug): array
    {
        return ProductUpdate::where('product_id', $productId)
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
                    'download_url' => $this->getDownloadUrl($update->version, $licenseKey, $productSlug),
                ];
            })->toArray();
    }

    /**
     * Get update by version
     */
    private function getUpdateByVersion(int $productId, string $version): ?ProductUpdate
    {
        return ProductUpdate::where('product_id', $productId)
            ->where('version', $version)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get next update
     */
    private function getNextUpdate(int $productId, string $currentVersion): ?ProductUpdate
    {
        return ProductUpdate::where('product_id', $productId)
            ->where('is_active', true)
            ->whereRaw('version > ?', [$currentVersion])
            ->orderBy('version', 'asc')
            ->first();
    }

    /**
     * Get product info
     */
    private function getProductInfo(Product $product): array
    {
        return [
            'name' => $product->name,
            'slug' => $product->slug,
            'current_version' => $product->current_version,
        ];
    }

    /**
     * Get update info
     */
    private function getUpdateInfo(ProductUpdate $update, string $licenseKey, string $productSlug): array
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
            'download_url' => $this->getDownloadUrl($update->version, $licenseKey, $productSlug),
        ];
    }

    /**
     * Get update info data
     */
    private function getUpdateInfoData(ProductUpdate $update): array
    {
        return [
            'title' => $update->title,
            'description' => $update->description,
            'changelog' => $update->changelog,
            'is_major' => $update->is_major,
            'is_required' => $update->is_required,
            'release_date' => $update->release_date,
            'file_size' => $update->file_size,
            'download_url' => route('api.license.download-update', [
                'license_key' => 'REQUIRED',
                'version' => $update->version,
            ]),
        ];
    }

    /**
     * Get download URL
     */
    private function getDownloadUrl(string $version, string $licenseKey, string $productSlug): string
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
     * Clean domain
     */
    private function cleanDomain(string $domain): string
    {
        $domain = preg_replace('/^https?:\/\//', '', $domain) ?? $domain;
        return preg_replace('/^www\./', '', $domain) ?? $domain;
    }

    /**
     * Check domain match
     */
    private function isDomainMatch(string $domain, string $authDomain): bool
    {
        if ($authDomain === $domain) {
            return true;
        }

        if (str_starts_with($authDomain, '*.')) {
            $pattern = str_replace('*.', '', $authDomain);
            return str_ends_with($domain, $pattern);
        }

        return false;
    }

    /**
     * Check rate limit
     */
    private function checkRateLimit(string $key, string $ip, int $maxAttempts): void
    {
        $rateKey = "license-{$key}:{$ip}";
        if (RateLimiter::tooManyAttempts($rateKey, $maxAttempts)) {
            throw new \Exception('Rate limit exceeded');
        }
        RateLimiter::hit($rateKey, 300);
    }

    /**
     * Success response
     */
    private function successResponse(array $data): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Error response
     */
    private function errorResponse(string $message, string $errorCode, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ], $status);
    }
}