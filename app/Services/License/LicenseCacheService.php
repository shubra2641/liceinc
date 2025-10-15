<?php

declare(strict_types=1);

namespace App\Services\License;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * License Cache Service - Handles caching for license operations.
 */
class LicenseCacheService
{
    private const CACHE_DURATION_UPDATES = 300; // 5 minutes
    private const CACHE_DURATION_HISTORY = 600; // 10 minutes
    private const CACHE_DURATION_PRODUCTS = 1800; // 30 minutes

    /**
     * Get cached updates.
     */
    public function getCachedUpdates(string $licenseKey, string $productSlug): ?array
    {
        $cacheKey = $this->getUpdatesCacheKey($licenseKey, $productSlug);
        return Cache::get($cacheKey);
    }

    /**
     * Cache updates.
     */
    public function cacheUpdates(string $licenseKey, string $productSlug, array $data): void
    {
        $cacheKey = $this->getUpdatesCacheKey($licenseKey, $productSlug);
        Cache::put($cacheKey, $data, self::CACHE_DURATION_UPDATES);
    }

    /**
     * Get cached history.
     */
    public function getCachedHistory(string $licenseKey, string $productSlug): ?array
    {
        $cacheKey = $this->getHistoryCacheKey($licenseKey, $productSlug);
        return Cache::get($cacheKey);
    }

    /**
     * Cache history.
     */
    public function cacheHistory(string $licenseKey, string $productSlug, array $data): void
    {
        $cacheKey = $this->getHistoryCacheKey($licenseKey, $productSlug);
        Cache::put($cacheKey, $data, self::CACHE_DURATION_HISTORY);
    }

    /**
     * Get cached latest version.
     */
    public function getCachedLatestVersion(string $licenseKey, string $productSlug): ?array
    {
        $cacheKey = $this->getLatestVersionCacheKey($licenseKey, $productSlug);
        return Cache::get($cacheKey);
    }

    /**
     * Cache latest version.
     */
    public function cacheLatestVersion(string $licenseKey, string $productSlug, array $data): void
    {
        $cacheKey = $this->getLatestVersionCacheKey($licenseKey, $productSlug);
        Cache::put($cacheKey, $data, self::CACHE_DURATION_UPDATES);
    }

    /**
     * Get cached products.
     */
    public function getCachedProducts(): ?array
    {
        return Cache::get('license_products');
    }

    /**
     * Cache products.
     */
    public function cacheProducts(array $data): void
    {
        Cache::put('license_products', $data, self::CACHE_DURATION_PRODUCTS);
    }

    /**
     * Clear license cache.
     */
    public function clearLicenseCache(string $licenseKey, string $productSlug): void
    {
        try {
            $hashedLicenseKey = $this->hashForCache($licenseKey);
            $patterns = [
                "license_updates_{$hashedLicenseKey}_{$productSlug}_*",
                "license_history_{$hashedLicenseKey}_{$productSlug}",
                "license_latest_{$hashedLicenseKey}_{$productSlug}",
            ];

            foreach ($patterns as $pattern) {
                Cache::forget($pattern);
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear license cache', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
            ]);
        }
    }

    /**
     * Clear all cache.
     */
    public function clearAllCache(): void
    {
        try {
            Cache::forget('license_products');
        } catch (\Exception $e) {
            Log::error('Failed to clear all license cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Get updates cache key.
     */
    private function getUpdatesCacheKey(string $licenseKey, string $productSlug): string
    {
        $hashedLicenseKey = $this->hashForCache($licenseKey);
        return "license_updates_{$hashedLicenseKey}_{$productSlug}";
    }

    /**
     * Get history cache key.
     */
    private function getHistoryCacheKey(string $licenseKey, string $productSlug): string
    {
        $hashedLicenseKey = $this->hashForCache($licenseKey);
        return "license_history_{$hashedLicenseKey}_{$productSlug}";
    }

    /**
     * Get latest version cache key.
     */
    private function getLatestVersionCacheKey(string $licenseKey, string $productSlug): string
    {
        $hashedLicenseKey = $this->hashForCache($licenseKey);
        return "license_latest_{$hashedLicenseKey}_{$productSlug}";
    }

    /**
     * Hash for cache key.
     */
    private function hashForCache(string $input): string
    {
        return substr(hash('sha256', $input), 0, 16);
    }

    /**
     * Hash for logging.
     */
    private function hashForLogging(string $input): string
    {
        return substr(hash('sha256', $input), 0, 8);
    }
}
