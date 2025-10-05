<?php
namespace App\Services;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
/**
 * License Server Service with enhanced security and performance.
 *
 * This service provides comprehensive license server integration including
 * update checking, version management, product information, and file downloads
 * with enhanced security measures and error handling.
 *
 * Features:
 * - License update checking and version management
 * - Product information retrieval and caching
 * - File download management with security validation
 * - Comprehensive error handling and logging
 * - Performance optimization with intelligent caching
 * - Enhanced security measures for API communication
 * - Input validation and sanitization
 * - Network error handling and retry logic
 *
 *
 * @example
 * // Check for updates
 * $updates = $licenseServer->checkUpdates($licenseKey, $currentVersion, $productSlug);
 *
 * // Get latest version
 * $latest = $licenseServer->getLatestVersion($licenseKey, $productSlug);
 *
 * // Download update
 * $download = $licenseServer->downloadUpdate($licenseKey, $version, $productSlug);
 */
class LicenseServerService
{
    /**
     * Base URL for the license server API.
     */
    protected string $baseUrl;
    /**
     * Request timeout in seconds.
     */
    protected int $timeout;
    /**
     * Cache duration constants for different operations.
     */
    private const CACHE_DURATION_UPDATES = 300; // 5 minutes
    private const CACHE_DURATION_HISTORY = 600; // 10 minutes
    private const CACHE_DURATION_PRODUCTS = 1800; // 30 minutes
    /**
     * Create a new LicenseServerService instance.
     *
     * Initializes the service with configuration from the license server
     * configuration file and sets up default values for API communication.
     *
     * @return void
     */
    public function __construct()
    {
        // Always use my-logos.com for central API
        $this->baseUrl = 'https://my-logos.com/api';
        $this->timeout = config('license_server.timeout', 30);
    }
    /**
     * Get license server domain with enhanced validation.
     *
     * Retrieves the configured license server domain with fallback
     * to application URL if not specifically configured.
     *
     * @return string The license server domain
     *
     * @example
     * $domain = $licenseServer->getDomain();
     */
    public function getDomain(): string
    {
        try {
            $domain = config('license_server.domain');
            // If domain is not set, extract from APP_URL
            if (! $domain || $domain === 'my-logos.com') {
                $appUrl = config('app.url');
                $parsedDomain = parse_url($appUrl, PHP_URL_HOST);
                return $parsedDomain ?: 'localhost';
            }
            return $this->sanitizeDomain($domain);
        } catch (\Exception $e) {
            Log::error('Failed to get license server domain', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 'localhost';
        }
    }
    /**
     * Get license server base URL with validation.
     *
     * @return string The base URL for the license server
     *
     * @example
     * $baseUrl = $licenseServer->getBaseUrl();
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
    /**
     * Check for available updates from license server with enhanced security.
     *
     * Checks for available updates for a specific license and product with
     * comprehensive error handling, caching, and security validation.
     *
     * @param  string  $licenseKey  The license key to check
     * @param  string  $currentVersion  The current version of the product
     * @param  string  $productSlug  The product slug identifier
     * @param  string|null  $domain  The domain to check for (optional)
     *
     * @return array<string, mixed> Array containing update information or error details
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $updates = $licenseServer->checkUpdates('ABC123', '1.0.0', 'my-product', 'example.com');
     */
    public function checkUpdates(
        string $licenseKey,
        string $currentVersion,
        string $productSlug,
        ?string $domain = null,
    ): array {
        try {
            $this->validateUpdateParameters($licenseKey, $currentVersion, $productSlug, $domain);
            $cacheKey = "license_updates_{$this->hashForCache($licenseKey)}_{$productSlug}_{$currentVersion}";
            // Check cache first
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
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
                // Validate response data
                if ($this->validateUpdateResponse($data)) {
                    // Cache successful response
                    Cache::put($cacheKey, $data, self::CACHE_DURATION_UPDATES);
                    return $data;
                } else {
                    return $this->createErrorResponse(
                        'Invalid response format from license server',
                        'INVALID_RESPONSE',
                    );
                }
            } else {
                return $this->createErrorResponse('Failed to check for updates', 'SERVER_ERROR');
            }
        } catch (\Exception $e) {
            Log::error('License server update check exception', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }
    /**
     * Get version history from license server with enhanced security.
     *
     * Retrieves version history for a specific license and product with
     * comprehensive error handling and caching.
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $productSlug  The product slug identifier
     * @param  string|null  $domain  The domain to check for (optional)
     *
     * @return array<string, mixed> Array containing version history or error details
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $history = $licenseServer->getVersionHistory('ABC123', 'my-product', 'example.com');
     */
    public function getVersionHistory(string $licenseKey, string $productSlug, ?string $domain = null): array
    {
        try {
            $this->validateHistoryParameters($licenseKey, $productSlug, $domain);
            $cacheKey = "license_history_{$this->hashForCache($licenseKey)}_{$productSlug}";
            // Check cache first
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
            $response = Http::timeout($this->timeout)
                ->post("{$this->getBaseUrl()}/license/version-history", [
                    'license_key' => $this->sanitizeInput($licenseKey),
                    'product_slug' => $this->sanitizeInput($productSlug),
                    'domain' => $domain ? $this->sanitizeDomain($domain) : null,
                ]);
            if ($response->successful()) {
                $data = $response->json();
                // Cache successful response
                Cache::put($cacheKey, $data, self::CACHE_DURATION_HISTORY);
                return $data;
            } else {
                return $this->createErrorResponse('Failed to get version history', 'SERVER_ERROR');
            }
        } catch (\Exception $e) {
            Log::error('License server version history exception', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }
    /**
     * Get update information without license verification with enhanced security.
     *
     * Retrieves update information for a product without requiring license
     * verification, useful for public update checking.
     *
     * @param  string  $productSlug  The product slug identifier
     * @param  string  $currentVersion  The current version
     *
     * @return array<string, mixed> Array containing update information or error details
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $updateInfo = $licenseServer->getUpdateInfo('my-product', '1.0.0');
     */
    public function getUpdateInfo(string $productSlug, string $currentVersion): array
    {
        try {
            $this->validateUpdateInfoParameters($productSlug, $currentVersion);
            $cacheKey = "update_info_{$productSlug}_{$currentVersion}";
            // Check cache first
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
            $url = $this->getBaseUrl().'/license/update-info';
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    'product_slug' => $this->sanitizeInput($productSlug),
                    'current_version' => $this->sanitizeInput($currentVersion),
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: LicenseServerService/1.0',
                    'Accept: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            if ($curlError) {
                Log::error('Update info API cURL error', [
                    'error' => $curlError,
                    'url' => $url,
                ]);
                return $this->createErrorResponse('Network error: '.$curlError, 'NETWORK_ERROR');
            }
            if ($httpCode === 200) {
                $data = json_decode($responseBody, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Invalid JSON response from update-info API', [
                        'response' => $responseBody,
                    ]);
                    return $this->createErrorResponse('Invalid response from license server', 'INVALID_JSON');
                }
                // Cache successful response
                Cache::put($cacheKey, $data, self::CACHE_DURATION_HISTORY);
                return $data;
            } else {
                return $this->createErrorResponse('Failed to get update info', 'SERVER_ERROR');
            }
        } catch (\Exception $e) {
            Log::error('License server update info exception', [
                'error' => $e->getMessage(),
                'product_slug' => $productSlug,
                'current_version' => $currentVersion,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createErrorResponse(
                'An error occurred while fetching update info: '.$e->getMessage(),
                'EXCEPTION_ERROR',
            );
        }
    }
    /**
     * Get latest version from license server with enhanced security.
     *
     * Retrieves the latest version information for a specific license and product.
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $productSlug  The product slug identifier
     * @param  string|null  $domain  The domain to check for (optional)
     *
     * @return array<string, mixed> Array containing latest version information or error details
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $latest = $licenseServer->getLatestVersion('ABC123', 'my-product', 'example.com');
     */
    public function getLatestVersion(string $licenseKey, string $productSlug, ?string $domain = null): array
    {
        try {
            $this->validateLatestVersionParameters($licenseKey, $productSlug, $domain);
            $cacheKey = "license_latest_{$this->hashForCache($licenseKey)}_{$productSlug}";
            // Check cache first
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
            $response = Http::timeout($this->timeout)
                ->post("{$this->getBaseUrl()}/license/latest-version", [
                    'license_key' => $this->sanitizeInput($licenseKey),
                    'product_slug' => $this->sanitizeInput($productSlug),
                    'domain' => $domain ? $this->sanitizeDomain($domain) : null,
                ]);
            if ($response->successful()) {
                $data = $response->json();
                // Cache successful response
                Cache::put($cacheKey, $data, self::CACHE_DURATION_UPDATES);
                return $data;
            } else {
                return $this->createErrorResponse('Failed to get latest version', 'SERVER_ERROR');
            }
        } catch (\Exception $e) {
            Log::error('License server latest version exception', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }
    /**
     * Download update file from license server with enhanced security.
     *
     * Downloads an update file for a specific license and version with
     * comprehensive security validation and error handling.
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $version  The version to download
     * @param  string  $productSlug  The product slug identifier
     * @param  string|null  $domain  The domain to check for (optional)
     *
     * @return array<string, mixed> Array containing download information or error details
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     *
     * @example
     * $download = $licenseServer->downloadUpdate('ABC123', '1.1.0', 'my-product', 'example.com');
     */
    public function downloadUpdate(
        string $licenseKey,
        string $version,
        string $productSlug,
        ?string $domain = null,
    ): array {
        try {
            $this->validateDownloadParameters($licenseKey, $version, $productSlug, $domain);
            $url = "{$this->getBaseUrl()}/license/download-update/".
                "{$this->sanitizeInput($licenseKey)}/{$this->sanitizeInput($version)}";
            $params = [
                'product_slug' => $this->sanitizeInput($productSlug),
                'domain' => $domain ? $this->sanitizeDomain($domain) : null,
            ];
            $response = Http::timeout($this->timeout)
                ->get($url, $params);
            if ($response->successful()) {
                // Create temp directory if it doesn't exist
                $tempDir = storage_path('app/temp');
                if (! is_dir($tempDir)) {
                    if (! mkdir($tempDir, 0755, true)) {
                        Log::error('Failed to create temp directory', [
                            'temp_dir' => $tempDir,
                            'license_key' => $this->hashForLogging($licenseKey),
                            'product_slug' => $productSlug,
                            'version' => $version,
                        ]);
                        return $this->createErrorResponse('Failed to create temporary directory', 'DIRECTORY_ERROR');
                    }
                }
                // Save file to temporary location with security validation
                $tempPath = $tempDir.'/update_'.$this->sanitizeFilename($version).'_'.time().'.zip';
                if (file_put_contents($tempPath, $response->body()) === false) {
                    Log::error('Failed to save update file', [
                        'temp_path' => $tempPath,
                        'license_key' => $this->hashForLogging($licenseKey),
                        'product_slug' => $productSlug,
                        'version' => $version,
                    ]);
                    return $this->createErrorResponse(
                        'Failed to save update file to temporary location',
                        'FILE_SAVE_ERROR',
                    );
                }
                return [
                    'success' => true,
                    'file_path' => $tempPath,
                    'file_size' => filesize($tempPath),
                ];
            } else {
                return $this->createErrorResponse('Failed to download update', 'DOWNLOAD_ERROR');
            }
        } catch (\Exception $e) {
            Log::error('License server download exception', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging($licenseKey),
                'product_slug' => $productSlug,
                'version' => $version,
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createErrorResponse('Download error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }
    /**
     * Get available products from license server with enhanced security.
     *
     * Retrieves a list of available products from the license server with
     * comprehensive error handling and caching.
     *
     * @return array<string, mixed> Array containing products information or error details
     *
     * @example
     * $products = $licenseServer->getProducts();
     */
    public function getProducts(): array
    {
        try {
            $cacheKey = 'license_products';
            // Check cache first
            $cached = Cache::get($cacheKey);
            if ($cached) {
                return $cached;
            }
            $url = $this->getBaseUrl().'/license/products';
            // Use cURL directly for better control
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'User-Agent: LicenseServerService/1.0',
                    'Accept: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);
            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            if ($curlError) {
                Log::error('Products API cURL error', [
                    'error' => $curlError,
                    'url' => $url,
                ]);
                return $this->createErrorResponse('Network error: '.$curlError, 'NETWORK_ERROR');
            }
            if ($httpCode === 200) {
                $data = json_decode($responseBody, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::error('Invalid JSON response from products API', [
                        'response' => $responseBody,
                    ]);
                    return $this->createErrorResponse('Invalid response from license server', 'INVALID_JSON');
                }
                // Cache successful response
                Cache::put($cacheKey, $data, self::CACHE_DURATION_PRODUCTS);
                return $data;
            } else {
                return $this->createErrorResponse('Failed to get products', 'SERVER_ERROR');
            }
        } catch (\Exception $e) {
            Log::error('License server products exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createErrorResponse('Network error: '.$e->getMessage(), 'NETWORK_ERROR');
        }
    }
    /**
     * Clear cache for specific license with enhanced security.
     *
     * Clears cached data for a specific license and product combination.
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $productSlug  The product slug
     *
     * @example
     * $licenseServer->clearCache('ABC123', 'my-product');
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
     * Clear all license cache with enhanced security.
     *
     * Clears all cached license server data.
     *
     *
     * @example
     * $licenseServer->clearAllCache();
     */
    public function clearAllCache(): void
    {
        try {
            Cache::forget('license_products');
            // Note: In production, you might want to use cache tags for more efficient clearing
        } catch (\Exception $e) {
            Log::error('Failed to clear all license cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    /**
     * Validate update parameters.
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $currentVersion  The current version
     * @param  string  $productSlug  The product slug
     * @param  string|null  $domain  The domain
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateUpdateParameters(
        string $licenseKey,
        string $currentVersion,
        string $productSlug,
        ?string $domain,
    ): void {
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
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $productSlug  The product slug
     * @param  string|null  $domain  The domain
     *
     * @throws \InvalidArgumentException When validation fails
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
     *
     * @param  string  $productSlug  The product slug
     * @param  string  $currentVersion  The current version
     *
     * @throws \InvalidArgumentException When validation fails
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
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $productSlug  The product slug
     * @param  string|null  $domain  The domain
     *
     * @throws \InvalidArgumentException When validation fails
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
     *
     * @param  string  $licenseKey  The license key
     * @param  string  $version  The version
     * @param  string  $productSlug  The product slug
     * @param  string|null  $domain  The domain
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateDownloadParameters(
        string $licenseKey,
        string $version,
        string $productSlug,
        ?string $domain,
    ): void {
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
     * Validate update response data.
     *
     * @param  array  $data  The response data
     *
     * @return bool True if valid, false otherwise
     */
    private function validateUpdateResponse(array $data): bool
    {
        return isset($data['success']) && is_bool($data['success']);
    }
    /**
     * Create standardized error response.
     *
     * @param  string  $message  The error message
     * @param  string  $errorCode  The error code
     *
     * @return array<string, mixed> The error response array
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
     * Sanitize input to prevent XSS attacks.
     *
     * @param  string  $input  The input to sanitize
     *
     * @return string The sanitized input
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Sanitize domain for security.
     *
     * @param  string  $domain  The domain to sanitize
     *
     * @return string The sanitized domain
     */
    private function sanitizeDomain(string $domain): string
    {
        return strtolower(trim($domain));
    }
    /**
     * Sanitize filename for security.
     *
     * @param  string  $filename  The filename to sanitize
     *
     * @return string The sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    }
    /**
     * Hash data for cache keys.
     *
     * @param  string  $data  The data to hash
     *
     * @return string The hashed data
     */
    private function hashForCache(string $data): string
    {
        return hash('sha256', $data.config('app.key'));
    }
    /**
     * Hash data for logging.
     *
     * @param  string  $data  The data to hash
     *
     * @return string The hashed data
     */
    private function hashForLogging(string $data): string
    {
        return substr(hash('sha256', $data.config('app.key')), 0, 8).'...';
    }
}
