<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Update Cache Service - Handles cache operations during updates.
 */
class UpdateCacheService
{
    /**
     * Clear all application caches.
     */
    public function clearAllCaches(): array
    {
        try {
            $steps = [];

            // Clear application cache
            Artisan::call('cache:clear');
            $steps['application_cache'] = 'Application cache cleared';

            // Clear config cache
            Artisan::call('config:clear');
            $steps['config_cache'] = 'Configuration cache cleared';

            // Clear route cache
            Artisan::call('route:clear');
            $steps['route_cache'] = 'Route cache cleared';

            // Clear view cache
            Artisan::call('view:clear');
            $steps['view_cache'] = 'View cache cleared';

            // Clear compiled views
            Artisan::call('view:clear');
            $steps['compiled_views'] = 'Compiled views cleared';

            // Clear all caches
            Cache::flush();
            $steps['all_caches'] = 'All caches cleared';

            Log::info('All caches cleared successfully', [
                'steps' => $steps,
            ]);

            return $steps;
        } catch (\Exception $e) {
            Log::error('Failed to clear caches', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Clear specific cache type.
     */
    public function clearCacheType(string $cacheType): bool
    {
        try {
            switch ($cacheType) {
                case 'application':
                    Artisan::call('cache:clear');
                    break;
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'all':
                    Cache::flush();
                    break;
                default:
                    throw new \InvalidArgumentException('Invalid cache type');
            }

            Log::info('Cache cleared', [
                'cache_type' => $cacheType,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear cache', [
                'error' => $e->getMessage(),
                'cache_type' => $cacheType,
            ]);
            throw $e;
        }
    }

    /**
     * Warm up caches after update.
     */
    public function warmUpCaches(): array
    {
        try {
            $steps = [];

            // Cache configuration
            Artisan::call('config:cache');
            $steps['config_cache'] = 'Configuration cached';

            // Cache routes
            Artisan::call('route:cache');
            $steps['route_cache'] = 'Routes cached';

            // Cache views
            Artisan::call('view:cache');
            $steps['view_cache'] = 'Views cached';

            Log::info('Caches warmed up successfully', [
                'steps' => $steps,
            ]);

            return $steps;
        } catch (\Exception $e) {
            Log::error('Failed to warm up caches', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Optimize application.
     */
    public function optimizeApplication(): array
    {
        try {
            $steps = [];

            // Optimize autoloader
            Artisan::call('optimize');
            $steps['optimize'] = 'Application optimized';

            // Clear and rebuild caches
            $this->clearAllCaches();
            $this->warmUpCaches();

            Log::info('Application optimized successfully', [
                'steps' => $steps,
            ]);

            return $steps;
        } catch (\Exception $e) {
            Log::error('Failed to optimize application', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get cache status.
     */
    public function getCacheStatus(): array
    {
        try {
            $status = [
                'application_cache' => $this->isCacheEnabled('application'),
                'config_cache' => $this->isCacheEnabled('config'),
                'route_cache' => $this->isCacheEnabled('route'),
                'view_cache' => $this->isCacheEnabled('view'),
            ];

            return $status;
        } catch (\Exception $e) {
            Log::error('Failed to get cache status', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Check if cache is enabled.
     */
    private function isCacheEnabled(string $cacheType): bool
    {
        try {
            switch ($cacheType) {
                case 'application':
                    return config('cache.default') !== 'array';
                case 'config':
                    return file_exists(base_path('bootstrap/cache/config.php'));
                case 'route':
                    return file_exists(base_path('bootstrap/cache/routes-v7.php'));
                case 'view':
                    return file_exists(base_path('bootstrap/cache/packages.php'));
                default:
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Failed to check cache status', [
                'error' => $e->getMessage(),
                'cache_type' => $cacheType,
            ]);
            return false;
        }
    }

    /**
     * Clear specific cache files.
     */
    public function clearCacheFiles(): array
    {
        try {
            $steps = [];
            $cacheFiles = [
                'bootstrap/cache/config.php',
                'bootstrap/cache/routes-v7.php',
                'bootstrap/cache/packages.php',
                'bootstrap/cache/services.php',
            ];

            foreach ($cacheFiles as $file) {
                $filePath = base_path($file);
                if (file_exists($filePath)) {
                    unlink($filePath);
                    $steps[] = "Deleted cache file: {$file}";
                }
            }

            Log::info('Cache files cleared', [
                'steps' => $steps,
            ]);

            return $steps;
        } catch (\Exception $e) {
            Log::error('Failed to clear cache files', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Rebuild cache files.
     */
    public function rebuildCacheFiles(): array
    {
        try {
            $steps = [];

            // Clear existing cache files
            $this->clearCacheFiles();

            // Rebuild caches
            Artisan::call('config:cache');
            $steps[] = 'Configuration cache rebuilt';

            Artisan::call('route:cache');
            $steps[] = 'Route cache rebuilt';

            Artisan::call('view:cache');
            $steps[] = 'View cache rebuilt';

            Log::info('Cache files rebuilt', [
                'steps' => $steps,
            ]);

            return $steps;
        } catch (\Exception $e) {
            Log::error('Failed to rebuild cache files', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
