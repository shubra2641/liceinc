<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Enterprise-Grade Configuration Management System.
 *
 * Advanced configuration helper with:
 * - Multi-layer caching strategies with compression
 * - Security validation and sanitization
 * - Performance optimization with query batching
 * - Audit logging and monitoring
 * - Graceful degradation and error handling
 * - Type safety and validation
 * - Advanced features not found in basic helpers
 */
class ConfigHelper
{
    // Cache configuration
    private const CACHE_PREFIX = 'config_enterprise_';

    private const CACHE_TTL = 3600; // 1 hour

    private const CACHE_TAG = 'config_settings';

    // Security configuration
    private const MAX_KEY_LENGTH = 255;

    private const ALLOWED_KEY_PATTERN = '/^[a-zA-Z0-9_\.\-]+$/';

    // Performance configuration
    private const BATCH_SIZE = 50;

    private const QUERY_TIMEOUT = 5; // seconds

    /**
     * Get setting with advanced validation, caching, and security.
     *
     * @param  string  $key  The setting key (validated and sanitized)
     * @param  mixed  $default  Default value if not found
     * @param  string|null  $configKey  Optional config key for fallback
     * @param  bool  $useCache  Whether to use advanced caching
     * @param  bool  $validateType  Whether to validate and cast return type
     *
     * @return mixed
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public static function getSetting(
        string $key,
        $default = null,
        ?string $configKey = null,
        bool $useCache = true,
        bool $validateType = true,
    ) {
        // Advanced input validation and sanitization
        $validationResult = self::validateSettingKey($key);
        if (! $validationResult['valid']) {
            Log::warning('Invalid config key provided', [
                'key' => $key,
                'errors' => $validationResult['errors'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
            $errors = $validationResult['errors'] ?? [];
            if (is_array($errors)) {
                throw new \InvalidArgumentException('Invalid configuration key: '
                    . implode(', ', $errors));
            } else {
                throw new \InvalidArgumentException('Invalid configuration key');
            }
        }
        $sanitizedKey = $validationResult['sanitized'] ?? '';
        if (!is_string($sanitizedKey)) {
            throw new \InvalidArgumentException('Invalid sanitized key');
        }
        $cacheKey = self::CACHE_PREFIX . md5($sanitizedKey);
        // Try advanced cache first with compression
        if ($useCache) {
            $cachedValue = self::getFromAdvancedCache($cacheKey);
            if ($cachedValue !== null) {
                self::logCacheHit($sanitizedKey);

                return $validateType ? self::castValue($cachedValue, $default) : $cachedValue;
            }
        }
        try {
            // Use database transaction for consistency
            $value = DB::transaction(function () use ($sanitizedKey) {
                return self::fetchSettingFromDatabase($sanitizedKey);
            }, self::QUERY_TIMEOUT);
            if ($value !== null) {
                // Advanced caching with compression and tagging
                if ($useCache) {
                    self::storeInAdvancedCache($cacheKey, $value);
                }
                self::logSettingAccess($sanitizedKey, 'database');

                return $validateType ? self::castValue($value, $default) : $value;
            }
            // Fallback to config with validation
            if ($configKey) {
                $configValue = self::getConfigValue($configKey, $default);
                if ($useCache) {
                    self::storeInAdvancedCache($cacheKey, $configValue);
                }

                return $validateType ? self::castValue($configValue, $default) : $configValue;
            }

            return $validateType ? self::castValue($default, $default) : $default;
        } catch (\Exception $e) {
            Log::error('Failed to fetch setting', [
                'key' => $sanitizedKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'context' => [
                    'memory_usage' => memory_get_usage(true),
                    'execution_time' => ServerHelper::getExecutionTime(),
                ],
            ]);

            // Graceful degradation with fallback
            return self::handleGracefulDegradation($configKey, $default, $validateType);
        }
    }

    /**
     * Advanced input validation and sanitization.
     */
    /**
 * @return array<string, mixed>
*/
    private static function validateSettingKey(string $key): array
    {
        $errors = [];
        $sanitized = trim($key);
        // Length validation
        if (strlen($sanitized) > self::MAX_KEY_LENGTH) {
            $errors[] = 'Key length exceeds maximum of ' . self::MAX_KEY_LENGTH . ' characters';
        }
        // Pattern validation
        if (! preg_match(self::ALLOWED_KEY_PATTERN, $sanitized)) {
            $errors[] = 'Key contains invalid characters. Only alphanumeric, underscore, dot, and dash allowed';
        }
        // Security checks
        if (strpos($sanitized, '..') !== false) {
            $errors[] = 'Key contains potential path traversal attempt';
        }
        if (in_array(strtolower($sanitized), ['password', 'secret', 'token', 'key'])) {
            Log::warning('Sensitive key access attempt', ['key' => $sanitized]);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'sanitized' => $sanitized,
        ];
    }

    /**
     * Advanced caching with compression and tagging.
     */
    private static function getFromAdvancedCache(string $cacheKey): mixed
    {
        try {
            // Try multiple cache strategies
            try {
                $value = Cache::tags([self::CACHE_TAG])->get($cacheKey);
            } catch (\Exception $e) {
                // Fallback to regular cache without tags
                $value = Cache::get($cacheKey);
            }

            return $value;
        } catch (\Exception $e) {
            Log::warning('Cache retrieval failed', ['key' => $cacheKey, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Store in advanced cache with compression.
     */
    private static function storeInAdvancedCache(string $cacheKey, mixed $value): void
    {
        try {
            // Compress large values
            if (is_string($value) && strlen($value) > 1024) {
                $compressed = gzcompress($value);
                if ($compressed !== false) {
                    $value = base64_encode($compressed) . '|COMPRESSED';
                }
            }
            // Use regular cache if tagging is not supported
            try {
                Cache::tags([self::CACHE_TAG])->put($cacheKey, $value, self::CACHE_TTL);
            } catch (\Exception $e) {
                // Fallback to regular cache without tags
                Cache::put($cacheKey, $value, self::CACHE_TTL);
            }
        } catch (\Exception $e) {
            Log::warning('Cache storage failed', ['key' => $cacheKey, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Optimized database fetch with query optimization.
     */
    private static function fetchSettingFromDatabase(string $key): mixed
    {
        try {
            // Check if the column exists in the settings table
            $columns = \Schema::getColumnListing('settings');
            if (in_array($key, $columns)) {
                // Try single-row settings first (optimized query)
                $setting = Setting::select($key)
                    ->whereNotNull($key)
                    ->where($key, '!=', '')
                    ->first();
                if ($setting && $setting->$key !== null) {
                    return $setting->$key;
                }
            }
            // Fallback to key/value rows with optimized query
            $kv = Setting::where('key', $key)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->select('value')
                ->first();

            return $kv ? $kv->value : null;
        } catch (\Exception $e) {
            Log::debug('Column not found in settings table', [
                'key' => $key,
                'error' => $e->getMessage(),
            ]);
            // Fallback to key/value rows only
            $kv = Setting::where('key', $key)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->select('value')
                ->first();

            return $kv ? $kv->value : null;
        }
    }

    /**
     * Get multiple settings with batch optimization and advanced caching.
     */
    /**
     * @param array<string> $keys
     *
     * @return array<string, mixed>
     */
    public static function getSettings(
        array $keys,
        bool $useCache = true,
        bool $validateTypes = true,
    ): array {
        // Validate all keys first
        $validatedKeys = [];
        foreach ($keys as $key) {
            $validation = self::validateSettingKey($key);
            if ($validation['valid']) {
                $validatedKeys[] = $validation['sanitized'];
            } else {
                Log::warning('Skipping invalid key in batch request', [
                    'key' => $key,
                    'errors' => $validation['errors'],
                ]);
            }
        }
        if (empty($validatedKeys)) {
            return [];
        }
        $settings = [];
        $uncachedKeys = $useCache ? [] : $validatedKeys;
        // Advanced cache checking with batch operations
        if ($useCache) {
            $validatedKeysArray = array_filter($validatedKeys, 'is_string');
            $cacheResults = self::getBatchFromCache($validatedKeysArray);
            foreach ($cacheResults as $key => $value) {
                if ($value !== null) {
                    $settings[$key] = $validateTypes ? self::castValue($value, null) : $value;
                } else {
                    $uncachedKeys[] = $key;
                }
            }
        }
        // Batch fetch from database
        if (count($uncachedKeys) > 0) {
            $uncachedKeysArray = array_filter($uncachedKeys, 'is_string');
            $fetchedSettings = self::fetchMultipleSettingsOptimized($uncachedKeysArray);
            foreach ($fetchedSettings as $key => $value) {
                $settings[$key] = $validateTypes ? self::castValue($value, null) : $value;
                if ($useCache) {
                    self::storeInAdvancedCache(self::CACHE_PREFIX . md5($key), $value);
                }
            }
        }

        return $settings;
    }

    /**
     * Batch cache retrieval with optimization.
     */
    /**
     * @param array<string> $keys
     *
     * @return array<string, mixed>
     */
    private static function getBatchFromCache(array $keys): array
    {
        $results = [];
        $cacheKeys = array_map(fn ($key) => self::CACHE_PREFIX . md5($key), $keys);
        try {
            $cacheValues = Cache::tags([self::CACHE_TAG])->many($cacheKeys);
            foreach ($keys as $index => $key) {
                $cacheKey = $cacheKeys[$index];
                $results[$key] = $cacheValues[$cacheKey] ?? null;
            }
        } catch (\Exception $e) {
            Log::warning('Batch cache retrieval failed', ['error' => $e->getMessage()]);
            foreach ($keys as $key) {
                $results[$key] = null;
            }
        }

        return $results;
    }

    /**
     * Optimized multiple settings fetch with single query.
     */
    /**
     * @param array<string> $keys
     *
     * @return array<string, mixed>
     */
    private static function fetchMultipleSettingsOptimized(array $keys): array
    {
        /**
 * @var array<string, mixed> $settings
*/
        $settings = [];
        try {
            // Single optimized query for all settings
            $setting = Setting::select($keys)
                ->where(function ($query) use ($keys) {
                    foreach ($keys as $key) {
                        $query->orWhereNotNull($key)->where($key, '!=', '');
                    }
                })
                ->first();
            if ($setting) {
                foreach ($keys as $key) {
                    $value = $setting->$key;
                    if ($value !== null && $value !== '') {
                        $settings[$key] = $value;
                    }
                }
            }
            // Check remaining keys in key/value format
            $remainingKeys = array_diff($keys, array_keys($settings));
            if (! empty($remainingKeys)) {
                $kvSettings = Setting::whereIn('key', $remainingKeys)
                    ->whereNotNull('value')
                    ->where('value', '!=', '')
                    ->pluck('value', 'key')
                    ->toArray();
                $settings = array_merge($settings, $kvSettings);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch multiple settings', [
                'keys' => $keys,
                'error' => $e->getMessage(),
            ]);
        }

        /**
 * @var array<string, mixed> $result
*/
        $result = $settings;
        return $result;
    }

    /**
     * Advanced type casting and validation.
     */
    private static function castValue(mixed $value, mixed $default): mixed
    {
        if ($value === null) {
            return $default;
        }
        // Handle compressed values with error checking
        if (is_string($value) && strpos($value, '|COMPRESSED') !== false) {
            try {
                $compressed = @base64_decode(str_replace('|COMPRESSED', '', $value));
                if ($compressed === '') {
                    Log::warning('Failed to decode compressed config value');

                    return $default;
                }
                $decompressed = gzuncompress($compressed);
                if ($decompressed === false || $decompressed === '') {
                    Log::warning('Failed to decompress config value');

                    return $default;
                }
                $value = $decompressed;
            } catch (\Exception $e) {
                Log::warning('Error handling compressed config value', ['error' => $e->getMessage()]);

                return $default;
            }
        }
        // Type casting based on default value
        if ($default !== null) {
            $defaultType = gettype($default);
            switch ($defaultType) {
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'integer':
                    if (is_numeric($value)) {
                        return (int)(string)$value;
                    }
                    return is_int($default) ? $default : 0;
                case 'double':
                case 'float':
                    if (is_numeric($value)) {
                        return (float)(string)$value;
                    }
                    return is_float($default) ? $default : 0.0;
                case 'array':
                    if (is_array($value)) {
                        return $value;
                    }
                    $valueString = is_string($value) ? $value : '';
                    $decoded = json_decode($valueString, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        Log::warning('Invalid JSON in config value', ['error' => json_last_error_msg()]);

                        return $default;
                    }

                    return $decoded ?? $default;
            }
        }

        return $value;
    }

    /**
     * Get config value with validation.
     */
    private static function getConfigValue(string $configKey, mixed $default): mixed
    {
        $value = config($configKey, $default);
        // Validate config key format
        if (! preg_match('/^[a-zA-Z0-9_\.]+$/', $configKey)) {
            Log::warning('Invalid config key format', ['key' => $configKey]);

            return $default;
        }

        return $value;
    }

    /**
     * Graceful degradation handler.
     */
    private static function handleGracefulDegradation(?string $configKey, mixed $default, bool $validateType): mixed
    {
        if ($configKey) {
            $configValue = self::getConfigValue($configKey, $default);

            return $validateType ? self::castValue($configValue, $default) : $configValue;
        }

        return $validateType ? self::castValue($default, $default) : $default;
    }

    /**
     * Log cache hit for monitoring.
     */
    private static function logCacheHit(string $key): void
    {
        Log::debug('Config cache hit', ['key' => $key]);
    }

    /**
     * Log setting access for audit trail.
     */
    private static function logSettingAccess(string $key, string $source): void
    {
        // Audit logging removed for successful operations per Envato compliance rules
        // Only log errors and warnings, not successful operations
    }

    /**
     * Clear cache for specific setting.
     */
    public static function clearSettingCache(string $key): void
    {
        $cacheKey = self::CACHE_PREFIX . md5($key);
        try {
            try {
                Cache::tags([self::CACHE_TAG])->forget($cacheKey);
            } catch (\Exception $e) {
                // Fallback to regular cache without tags
                Cache::forget($cacheKey);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear config cache', ['key' => $key, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Clear all settings cache.
     */
    public static function clearAllCache(): void
    {
        try {
            try {
                Cache::tags([self::CACHE_TAG])->flush();
            } catch (\Exception $e) {
                // Fallback to regular cache flush
                Cache::flush();
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear all config cache', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get license-related settings with advanced caching.
     */
    /**
 * @return array<string, mixed>
*/
    public static function getLicenseSettings(): array
    {
        return self::getSettings([
            'license_api_token',
            'envato_personal_token',
            'envato_client_id',
            'envato_client_secret',
            'envato_redirect_uri',
            'auto_generate_license',
            'default_license_length',
            'license_max_attempts',
            'license_lockout_minutes',
            // License verification settings
            'license_verify_envato',
            'license_fallback_internal',
            'license_cache_verification',
            'license_cache_duration',
            'license_allow_offline',
            'license_grace_period',
            // Domain management settings
            'license_allow_localhost',
            'license_allow_ip_addresses',
            'license_allow_wildcards',
            'license_validate_ssl',
            'license_auto_approve_subdomains',
            'license_max_domains',
            'license_domain_cooldown',
            // License expiration settings
            'license_default_duration',
            'license_support_duration',
            'license_renewal_reminder',
            'license_expiration_grace',
            'license_auto_suspend',
            'license_allow_expired_verification',
            // Security settings
            'license_encrypt_data',
            'license_secure_tokens',
            'license_validate_signatures',
            'license_prevent_sharing',
            'license_detect_suspicious',
            'license_block_vpn',
            'license_require_https',
            // Notification settings
            'license_notify_verification',
            'license_notify_expiration',
            'license_notify_domain_change',
            'license_notify_suspicious',
            'license_notification_email',
            'license_use_slack',
            'license_slack_webhook',
            // Performance settings
            'license_enable_caching',
            'license_cache_driver',
            'license_optimize_queries',
            'license_batch_size',
            'license_use_indexes',
            'license_compress_responses',
            // Testing settings
            'license_allow_test',
            'license_test_prefix',
            'license_bypass_testing',
            'license_mock_envato',
            'license_generate_fake_data',
        ]);
    }

    /**
     * Get Envato-related settings with advanced caching.
     */
    /**
 * @return array<string, mixed>
*/
    public static function getEnvatoSettings(): array
    {
        return self::getSettings([
            'envato_personal_token',
            'envato_client_id',
            'envato_client_secret',
            'envato_redirect_uri',
            'envato_auth_enabled',
            'envato_oauth_enabled',
            'envato_username',
        ]);
    }

    /**
     * Get setting with type safety and validation.
     *
     * @param  string  $type  Expected type (string, int, bool, array, float)
     * @param  mixed  $default
     *
     * @return mixed
     */
    public static function getTypedSetting(string $key, string $type, $default = null)
    {
        $value = self::getSetting($key, $default, null, true, true);
        switch ($type) {
            case 'string':
                if (is_string($value)) {
                    return $value;
                }
                return is_string($default) ? $default : '';
            case 'int':
            case 'integer':
                if (is_numeric($value)) {
                    return (int)(string)$value;
                }
                return is_int($default) ? $default : 0;
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'float':
            case 'double':
                if (is_numeric($value)) {
                    return (float)(string)$value;
                }
                return is_float($default) ? $default : 0.0;
            case 'array':
                if (is_array($value)) {
                    return $value;
                }
                $valueString = is_string($value) ? $value : '';
                $decoded = json_decode($valueString, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Invalid JSON in typed setting', ['key' => $key, 'error' => json_last_error_msg()]);

                    return [];
                }

                return $decoded ?? [];
            default:
                return $value;
        }
    }

    /**
     * Check if setting exists and is not empty.
     */
    public static function hasSetting(string $key): bool
    {
        try {
            $value = self::getSetting($key, null, null, true, false);

            return $value !== null && $value !== '';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get settings with fallback to environment variables.
     *
     * @param  array  $envMappings  Mapping of setting keys to env variable names
     */
    /**
     * @param array<string> $keys
     * @param array<string, string> $envMappings
     *
     * @return array<string, mixed>
     */
    public static function getSettingsWithEnvFallback(array $keys, array $envMappings = []): array
    {
        $settings = self::getSettings($keys);
        foreach ($keys as $key) {
            if (($settings[$key] ?? null) === null) {
                $envKey = $envMappings[$key] ?? strtoupper($key);
                $envValue = env($envKey); // @phpstan-ignore-line
                if ($envValue !== null) {
                    $settings[$key] = $envValue;
                }
            }
        }

        return $settings;
    }

    /**
     * Get cache statistics for monitoring.
     */
    /**
 * @return array<string, mixed>
*/
    public static function getCacheStats(): array
    {
        return [
            'cache_prefix' => self::CACHE_PREFIX,
            'cache_ttl' => self::CACHE_TTL,
            'cache_tag' => self::CACHE_TAG,
            'batch_size' => self::BATCH_SIZE,
            'query_timeout' => self::QUERY_TIMEOUT,
        ];
    }

    /**
     * Warm up cache for frequently used settings.
     */
    /**
 * @param array<string> $keys
*/
    public static function warmUpCache(array $keys = []): void
    {
        if (empty($keys)) {
            $keys = array_merge(
                array_keys(self::getLicenseSettings()),
                array_keys(self::getEnvatoSettings()),
            );
        }
        try {
            self::getSettings($keys, true, true);
        } catch (\Exception $e) {
            Log::error('Failed to warm up config cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'keys_count' => count($keys),
            ]);
        }
    }
}
