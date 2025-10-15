<?php

declare(strict_types=1);

namespace App\Services\Envato;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Envato Cache Service - Handles caching for Envato operations.
 */
class EnvatoCacheService
{
    /**
     * Get cached item info.
     */
    public function getCachedItemInfo(int $itemId): ?array
    {
        $cacheKey = 'envato_item_' . $itemId;
        return Cache::get($cacheKey);
    }

    /**
     * Cache item info.
     */
    public function cacheItemInfo(int $itemId, array $data): void
    {
        $cacheKey = 'envato_item_' . $itemId;
        Cache::put($cacheKey, $data, now()->addHours(6));
    }

    /**
     * Get cached user items.
     */
    public function getCachedUserItems(string $username): ?array
    {
        $cacheKey = 'envato_user_items_' . md5($username);
        return Cache::get($cacheKey);
    }

    /**
     * Cache user items.
     */
    public function cacheUserItems(string $username, array $data): void
    {
        $cacheKey = 'envato_user_items_' . md5($username);
        Cache::put($cacheKey, $data, now()->addHours(6));
    }

    /**
     * Get cached purchase verification.
     */
    public function getCachedPurchaseVerification(string $purchaseCode): ?array
    {
        $cacheKey = 'envato_purchase_' . md5($purchaseCode);
        return Cache::get($cacheKey);
    }

    /**
     * Cache purchase verification.
     */
    public function cachePurchaseVerification(string $purchaseCode, array $data): void
    {
        $cacheKey = 'envato_purchase_' . md5($purchaseCode);
        Cache::put($cacheKey, $data, now()->addHours(1));
    }

    /**
     * Get cached OAuth user.
     */
    public function getCachedOAuthUser(string $username): ?array
    {
        $cacheKey = 'envato_oauth_user_' . md5($username);
        return Cache::get($cacheKey);
    }

    /**
     * Cache OAuth user.
     */
    public function cacheOAuthUser(string $username, array $data): void
    {
        $cacheKey = 'envato_oauth_user_' . md5($username);
        Cache::put($cacheKey, $data, now()->addHours(2));
    }

    /**
     * Clear all Envato cache.
     */
    public function clearAllCache(): void
    {
        try {
            // Clear all Envato purchase cache entries
            if (Cache::getStore() instanceof \Illuminate\Cache\RedisStore) {
                $redis = Cache::getStore()->getRedis();
                if (method_exists($redis, 'keys')) {
                    $cacheKeys = $redis->keys('*envato_purchase_*');
                    if (is_array($cacheKeys)) {
                        foreach ($cacheKeys as $key) {
                            if (is_string($key)) {
                                Cache::forget($key);
                            }
                        }
                    }
                }
            }

            Cache::forget('envato_user_*');
            Cache::forget('envato_item_*');
            Cache::forget('envato_user_items_*');
            Cache::forget('envato_oauth_user_*');
        } catch (\Exception $e) {
            Log::error('Failed to clear Envato cache: ' . $e->getMessage());
        }
    }

    /**
     * Clear specific cache patterns.
     */
    public function clearCachePatterns(array $patterns): void
    {
        try {
            foreach ($patterns as $pattern) {
                Cache::forget($pattern);
            }
        } catch (\Exception $e) {
            Log::error('Failed to clear cache patterns', [
                'patterns' => $patterns,
                'error' => $e->getMessage()
            ]);
        }
    }
}
