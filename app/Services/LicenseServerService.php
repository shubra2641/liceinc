<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * License Server Service - Simplified.
 */
class LicenseServerService
{
    protected string $baseUrl;

    protected int $timeout;

    private const CACHE_DURATION_UPDATES = 300;

    private const CACHE_DURATION_HISTORY = 600;

    private const CACHE_DURATION_PRODUCTS = 1800;

    public function __construct()
    {
        $this->baseUrl = 'https://my-logos.com/api';
        $this->timeout = (int)config('license_server.timeout', 30);
    }

    /**
     * Get license server domain.
     */
    public function getDomain(): string
    {
        try {
            $domain = config('license_server.domain');
            if (! $domain || $domain === 'my-logos.com') {
                $appUrl = config('app.url');
                $parsedDomain = parse_url($appUrl, PHP_URL_HOST);

                return $parsedDomain ?: 'localhost';
            }

            return $this->sanitizeDomain($domain);
        } catch (\Exception $e) {
            Log::error('Failed to get license server domain', ['error' => $e->getMessage()]);

            return 'localhost';
        }
    }

    /**
     * Get license server base URL.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Check for available updates.
     */
    public function checkUpdates(string $licenseKey, string $currentVersion, string $productSlug, ?string $domain = null): array
    {
        try {
            $this->validateParameters($licenseKey, $currentVersion, $productSlug, $domain);
            $cacheKey = "license_updates_{$this->hashForCache($licenseKey)}_{$productSlug}_{$currentVersion}";

            $cached = Cache::get($cacheKey);
            if ($cached) {
                return is_array($cached) ? $cached : [];
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->getBaseUrl()}/license/check-updates", [
                    'license_key' => $this->sanitizeInput($licenseKey),
                    'current_version' => $this->sanitizeInput($currentVersion),
                    'product_slug' => $this->sanitizeInput($productSlug),
                    'domain' => $domain ? $this->sanitizeDomain($domain) : null,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data) && isset($data['success'])) {
                    Cache::put($cacheKey, $data, self::CACHE_DURATION_UPDATES);

                    return $data;
                }

                return $this->createErrorResponse('Invalid response format', 'INVALID_RESPONSE');
            }

            return $this->createErrorResponse('Failed to check for updates', 'SERVER_ERROR');
        } catch (\Exception $e) {
            Log::error('License server update check failed', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
            ]);

            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }

    /**
     * Get version history.
     */
    public function getVersionHistory(string $licenseKey, string $productSlug, ?string $domain = null): array
    {
        try {
            $this->validateHistoryParameters($licenseKey, $productSlug, $domain);
            $cacheKey = "license_history_{$this->hashForCache($licenseKey)}_{$productSlug}";

            $cached = Cache::get($cacheKey);
            if ($cached) {
                return is_array($cached) ? $cached : [];
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->getBaseUrl()}/license/version-history", [
                    'license_key' => $this->sanitizeInput($licenseKey),
                    'product_slug' => $this->sanitizeInput($productSlug),
                    'domain' => $domain ? $this->sanitizeDomain($domain) : null,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    Cache::put($cacheKey, $data, self::CACHE_DURATION_HISTORY);

                    return $data;
                }
            }

            return $this->createErrorResponse('Failed to get version history', 'SERVER_ERROR');
        } catch (\Exception $e) {
            Log::error('License server version history failed', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
            ]);

            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }

    /**
     * Get update information without license verification.
     */
    public function getUpdateInfo(string $productSlug, string $currentVersion): array
    {
        try {
            $this->validateUpdateInfoParameters($productSlug, $currentVersion);
            $cacheKey = "update_info_{$productSlug}_{$currentVersion}";

            $cached = Cache::get($cacheKey);
            if ($cached) {
                return is_array($cached) ? $cached : [];
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->getBaseUrl()}/license/update-info", [
                    'product_slug' => $this->sanitizeInput($productSlug),
                    'current_version' => $this->sanitizeInput($currentVersion),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    Cache::put($cacheKey, $data, self::CACHE_DURATION_HISTORY);

                    return $data;
                }
            }

            return $this->createErrorResponse('Failed to get update info', 'SERVER_ERROR');
        } catch (\Exception $e) {
            Log::error('License server update info failed', [
                'error' => $e->getMessage(),
                'product_slug' => $productSlug,
                'current_version' => $currentVersion,
            ]);

            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }

    /**
     * Get latest version.
     */
    public function getLatestVersion(string $licenseKey, string $productSlug, ?string $domain = null): array
    {
        try {
            $this->validateLatestVersionParameters($licenseKey, $productSlug, $domain);
            $cacheKey = "license_latest_{$this->hashForCache($licenseKey)}_{$productSlug}";

            $cached = Cache::get($cacheKey);
            if ($cached) {
                return is_array($cached) ? $cached : [];
            }

            $response = Http::timeout($this->timeout)
                ->post("{$this->getBaseUrl()}/license/latest-version", [
                    'license_key' => $this->sanitizeInput($licenseKey),
                    'product_slug' => $this->sanitizeInput($productSlug),
                    'domain' => $domain ? $this->sanitizeDomain($domain) : null,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    Cache::put($cacheKey, $data, self::CACHE_DURATION_UPDATES);

                    return $data;
                }
            }

            return $this->createErrorResponse('Failed to get latest version', 'SERVER_ERROR');
        } catch (\Exception $e) {
            Log::error('License server latest version failed', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
            ]);

            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }

    /**
     * Download update file.
     */
    public function downloadUpdate(string $licenseKey, string $version, string $productSlug, ?string $domain = null): array
    {
        try {
            $this->validateDownloadParameters($licenseKey, $version, $productSlug, $domain);

            $url = "{$this->getBaseUrl()}/license/download-update/{$this->sanitizeInput($licenseKey)}/{$this->sanitizeInput($version)}";
            $params = [
                'product_slug' => $this->sanitizeInput($productSlug),
                'domain' => $domain ? $this->sanitizeDomain($domain) : null,
            ];

            $response = Http::timeout($this->timeout)->get($url, $params);

            if ($response->successful()) {
                $tempDir = storage_path('app/temp');
                if (! is_dir($tempDir)) {
                    if (! mkdir($tempDir, 0755, true)) {
                        return $this->createErrorResponse('Failed to create temporary directory', 'DIRECTORY_ERROR');
                    }
                }

                $tempPath = $tempDir.'/update_'.$this->sanitizeFilename($version).'_'.time().'.zip';
                if (file_put_contents($tempPath, $response->body()) === false) {
                    return $this->createErrorResponse('Failed to save update file', 'FILE_SAVE_ERROR');
                }

                return [
                    'success' => true,
                    'file_path' => $tempPath,
                    'file_size' => filesize($tempPath),
                ];
            }

            return $this->createErrorResponse('Failed to download update', 'DOWNLOAD_ERROR');
        } catch (\Exception $e) {
            Log::error('License server download failed', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
                'version' => $version,
            ]);

            return $this->createErrorResponse('Download error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }

    /**
     * Get available products.
     */
    public function getProducts(): array
    {
        try {
            $cacheKey = 'license_products';

            $cached = Cache::get($cacheKey);
            if ($cached) {
                return is_array($cached) ? $cached : [];
            }

            $response = Http::timeout($this->timeout)->get("{$this->getBaseUrl()}/license/products");

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    Cache::put($cacheKey, $data, self::CACHE_DURATION_PRODUCTS);

                    return $data;
                }
            }

            return $this->createErrorResponse('Failed to get products', 'SERVER_ERROR');
        } catch (\Exception $e) {
            Log::error('License server products failed', ['error' => $e->getMessage()]);

            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }

    /**
     * Clear cache for specific license.
     */
    public function clearCache(string $licenseKey, string $productSlug): void
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
     * Clear all license cache.
     */
    public function clearAllCache(): void
    {
        try {
            Cache::forget('license_products');
        } catch (\Exception $e) {
            Log::error('Failed to clear all license cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Validate update parameters.
     */
    private function validateParameters(string $licenseKey, string $currentVersion, string $productSlug, ?string $domain): void
    {
        if (empty($licenseKey)) {
            throw new \InvalidArgumentException('License key cannot be empty');
        }
        if (empty($currentVersion)) {
            throw new \InvalidArgumentException('Current version cannot be empty');
        }
        if (empty($productSlug)) {
            throw new \InvalidArgumentException('Product slug cannot be empty');
        }
        if ($domain && ! filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Invalid domain format');
        }
    }

    /**
     * Validate history parameters.
     */
    private function validateHistoryParameters(string $licenseKey, string $productSlug, ?string $domain): void
    {
        if (empty($licenseKey)) {
            throw new \InvalidArgumentException('License key cannot be empty');
        }
        if (empty($productSlug)) {
            throw new \InvalidArgumentException('Product slug cannot be empty');
        }
        if ($domain && ! filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Invalid domain format');
        }
    }

    /**
     * Validate update info parameters.
     */
    private function validateUpdateInfoParameters(string $productSlug, string $currentVersion): void
    {
        if (empty($productSlug)) {
            throw new \InvalidArgumentException('Product slug cannot be empty');
        }
        if (empty($currentVersion)) {
            throw new \InvalidArgumentException('Current version cannot be empty');
        }
    }

    /**
     * Validate latest version parameters.
     */
    private function validateLatestVersionParameters(string $licenseKey, string $productSlug, ?string $domain): void
    {
        if (empty($licenseKey)) {
            throw new \InvalidArgumentException('License key cannot be empty');
        }
        if (empty($productSlug)) {
            throw new \InvalidArgumentException('Product slug cannot be empty');
        }
        if ($domain && ! filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Invalid domain format');
        }
    }

    /**
     * Validate download parameters.
     */
    private function validateDownloadParameters(string $licenseKey, string $version, string $productSlug, ?string $domain): void
    {
        if (empty($licenseKey)) {
            throw new \InvalidArgumentException('License key cannot be empty');
        }
        if (empty($version)) {
            throw new \InvalidArgumentException('Version cannot be empty');
        }
        if (empty($productSlug)) {
            throw new \InvalidArgumentException('Product slug cannot be empty');
        }
        if ($domain && ! filter_var($domain, FILTER_VALIDATE_DOMAIN)) {
            throw new \InvalidArgumentException('Invalid domain format');
        }
    }

    /**
     * Create error response.
     */
    private function createErrorResponse(string $message, string $errorCode): array
    {
        return [
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
        ];
    }

    /**
     * Sanitize input.
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize domain.
     */
    private function sanitizeDomain(string $domain): string
    {
        return strtolower(trim($domain));
    }

    /**
     * Sanitize filename.
     */
    private function sanitizeFilename(string $filename): ?string
    {
        $result = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        return $result !== null ? $result : null;
    }

    /**
     * Hash data for cache keys.
     */
    private function hashForCache(string $data): string
    {
        $appKey = config('app.key');

        return hash('sha256', $data.$appKey);
    }

    /**
     * Hash data for logging.
     */
    private function hashForLogging(string $data): string
    {
        $appKey = config('app.key');

        return substr(hash('sha256', $data.$appKey), 0, 8).'...';
    }
}
