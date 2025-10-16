<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Configuration Helper - Simplified.
 */
class ConfigHelper
{
    private const CACHE_PREFIX = 'config_';

    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get setting value with caching.
     */
    public static function getSetting(string $key, $default = null, ?string $configKey = null): mixed
    {
        try {
            $cacheKey = self::CACHE_PREFIX . md5($key);

            // Try cache first
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                return self::castValue($cachedValue, $default);
            }

            // Get from database
            $value = self::getFromDatabase($key);
            if ($value !== null) {
                Cache::put($cacheKey, $value, self::CACHE_TTL);

                return self::castValue($value, $default);
            }

            // Fallback to config
            if ($configKey) {
                $configValue = config($configKey, $default);
                Cache::put($cacheKey, $configValue, self::CACHE_TTL);

                return self::castValue($configValue, $default);
            }

            return $default;
        } catch (\Exception $e) {
            Log::error('Failed to get setting', ['key' => $key, 'error' => $e->getMessage()]);

            return $configKey ? config($configKey, $default) : $default;
        }
    }

    /**
     * Get multiple settings at once.
     */
    public static function getSettings(array $keys): array
    {
        $settings = [];
        $uncachedKeys = [];

        // Check cache for each key
        foreach ($keys as $key) {
            $cacheKey = self::CACHE_PREFIX . md5($key);
            $cachedValue = Cache::get($cacheKey);
            if ($cachedValue !== null) {
                $settings[$key] = $cachedValue;
            } else {
                $uncachedKeys[] = $key;
            }
        }

        // Fetch uncached keys from database
        if (! empty($uncachedKeys)) {
            $dbSettings = self::getMultipleFromDatabase($uncachedKeys);
            foreach ($dbSettings as $key => $value) {
                $settings[$key] = $value;
                $cacheKey = self::CACHE_PREFIX . md5($key);
                Cache::put($cacheKey, $value, self::CACHE_TTL);
            }
        }

        return $settings;
    }

    /**
     * Get setting from database.
     */
    private static function getFromDatabase(string $key): mixed
    {
        try {
            // Try key-value format first
            $setting = Setting::where('key', $key)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->value('value');

            if ($setting !== null) {
                return $setting;
            }

            // Try column format
            if (\Schema::hasColumn('settings', $key)) {
                $setting = Setting::select($key)
                    ->whereNotNull($key)
                    ->where($key, '!=', '')
                    ->value($key);

                return $setting;
            }

            return null;
        } catch (\Exception $e) {
            Log::debug('Database query failed', ['key' => $key, 'error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Get multiple settings from database.
     */
    private static function getMultipleFromDatabase(array $keys): array
    {
        $settings = [];

        try {
            // Get key-value format settings
            $kvSettings = Setting::whereIn('key', $keys)
                ->whereNotNull('value')
                ->where('value', '!=', '')
                ->pluck('value', 'key')
                ->toArray();

            $settings = array_merge($settings, $kvSettings);

            // Get column format settings for remaining keys
            $remainingKeys = array_diff($keys, array_keys($settings));
            if (! empty($remainingKeys)) {
                $columnSettings = Setting::select($remainingKeys)
                    ->where(function ($query) use ($remainingKeys) {
                        foreach ($remainingKeys as $key) {
                            $query->orWhereNotNull($key)->where($key, '!=', '');
                        }
                    })
                    ->first();

                if ($columnSettings) {
                    foreach ($remainingKeys as $key) {
                        $value = $columnSettings->$key;
                        if ($value !== null && $value !== '') {
                            $settings[$key] = $value;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch multiple settings', ['keys' => $keys, 'error' => $e->getMessage()]);
        }

        return $settings;
    }

    /**
     * Cast value to appropriate type.
     */
    private static function castValue(mixed $value, mixed $default): mixed
    {
        if ($value === null) {
            return $default;
        }

        if ($default !== null) {
            $defaultType = gettype($default);
            switch ($defaultType) {
                case 'boolean':
                    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                case 'integer':
                    return is_numeric($value) ? (int)$value : (is_int($default) ? $default : 0);
                case 'double':
                case 'float':
                    return is_numeric($value) ? (float)$value : (is_float($default) ? $default : 0.0);
                case 'array':
                    if (is_array($value)) {
                        return $value;
                    }
                    if (is_string($value)) {
                        $decoded = json_decode($value, true);

                        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
                    }

                    return $default;
            }
        }

        return $value;
    }

    /**
     * Get typed setting.
     */
    public static function getTypedSetting(string $key, string $type, $default = null): mixed
    {
        $value = self::getSetting($key, $default);

        switch ($type) {
            case 'string':
                return is_string($value) ? $value : (is_string($default) ? $default : '');
            case 'int':
            case 'integer':
                return is_numeric($value) ? (int)$value : (is_int($default) ? $default : 0);
            case 'bool':
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'float':
            case 'double':
                return is_numeric($value) ? (float)$value : (is_float($default) ? $default : 0.0);
            case 'array':
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);

                    return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }

                return [];
            default:
                return $value;
        }
    }

    /**
     * Check if setting exists.
     */
    public static function hasSetting(string $key): bool
    {
        try {
            $value = self::getSetting($key);

            return $value !== null && $value !== '';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Clear setting cache.
     */
    public static function clearSettingCache(string $key): void
    {
        $cacheKey = self::CACHE_PREFIX . md5($key);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all cache.
     */
    public static function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Get license settings.
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
            'license_verify_envato',
            'license_fallback_internal',
            'license_cache_verification',
            'license_cache_duration',
            'license_allow_offline',
            'license_grace_period',
            'license_allow_localhost',
            'license_allow_ip_addresses',
            'license_allow_wildcards',
            'license_validate_ssl',
            'license_auto_approve_subdomains',
            'license_max_domains',
            'license_domain_cooldown',
            'license_default_duration',
            'license_support_duration',
            'license_renewal_reminder',
            'license_expiration_grace',
            'license_auto_suspend',
            'license_allow_expired_verification',
            'license_encrypt_data',
            'license_secure_tokens',
            'license_validate_signatures',
            'license_prevent_sharing',
            'license_detect_suspicious',
            'license_block_vpn',
            'license_require_https',
            'license_notify_verification',
            'license_notify_expiration',
            'license_notify_domain_change',
            'license_notify_suspicious',
            'license_notification_email',
            'license_use_slack',
            'license_slack_webhook',
            'license_enable_caching',
            'license_cache_driver',
            'license_optimize_queries',
            'license_batch_size',
            'license_use_indexes',
            'license_compress_responses',
            'license_allow_test',
            'license_test_prefix',
            'license_bypass_testing',
            'license_mock_envato',
            'license_generate_fake_data',
        ]);
    }

    /**
     * Get Envato settings.
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
     * Get settings with environment fallback.
     */
    public static function getSettingsWithEnvFallback(array $keys, array $envMappings = []): array
    {
        $settings = self::getSettings($keys);

        foreach ($keys as $key) {
            if (($settings[$key] ?? null) === null) {
                $envKey = $envMappings[$key] ?? strtoupper($key);
                $envValue = env($envKey);
                if ($envValue !== null) {
                    $settings[$key] = $envValue;
                }
            }
        }

        return $settings;
    }

    /**
     * Warm up cache for frequently used settings.
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
            self::getSettings($keys);
        } catch (\Exception $e) {
            Log::error('Failed to warm up cache', ['error' => $e->getMessage()]);
        }
    }
}
