<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Envato Helper with enhanced security and performance.
 *
 * This helper class provides utility methods for managing Envato API settings
 * and configuration with comprehensive security measures and caching.
 *
 * Features:
 * - Envato API configuration checking
 * - Secure settings retrieval with validation
 * - Performance optimization with caching
 * - Comprehensive error handling and logging
 * - Input validation and sanitization
 * - Security measures for sensitive data
 * - Proper type hints and return types
 *
 * @example
 * // Check if Envato is configured
 * if (EnvatoHelper::isEnvatoConfigured()) {
 *     $settings = EnvatoHelper::getEnvatoSettings();
 *     // Use settings for API calls
 * }
 */
class EnvatoHelper
{
    /**
     * Cache key for Envato configuration check.
     */
    private const CACHE_KEY_CONFIGURED = 'envato_configured';

    /**
     * Cache key for Envato settings.
     */
    private const CACHE_KEY_SETTINGS = 'envato_settings';

    /**
     * Cache duration in minutes.
     */
    private const CACHE_DURATION = 60;

    /**
     * Check if Envato API settings are configured with enhanced security.
     *
     * Validates that all required Envato API settings are present and properly
     * configured. Uses caching for performance optimization.
     *
     * @return bool True if all required settings are configured, false otherwise
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * if (EnvatoHelper::isEnvatoConfigured()) {
     *     // Proceed with Envato API operations
     * }
     */
    public static function isEnvatoConfigured(): bool
    {
        try {
            $result = Cache::remember(self::CACHE_KEY_CONFIGURED, self::CACHE_DURATION, function () {
                $setting = Setting::first();
                if (! $setting) {
                    return false;
                }
                // Validate that all required fields are present and not empty
                $requiredFields = [
                    'envato_personal_token',
                    'envato_client_id',
                    'envato_client_secret',
                ];
                foreach ($requiredFields as $field) {
                    if (empty($setting->$field)) {
                        return false;
                    }
                }

                return true;
            });

            return is_bool($result) ? $result : false;
        } catch (\Exception $e) {
            Log::error('Error checking Envato configuration', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Alias method for isEnvatoConfigured() for backward compatibility.
     *
     * This method provides backward compatibility for views that call isConfigured().
     * It simply calls the main isEnvatoConfigured() method.
     *
     * @return bool True if all required settings are configured, false otherwise
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * if (EnvatoHelper::isConfigured()) {
     *     // Proceed with Envato API operations
     * }
     */
    public static function isConfigured(): bool
    {
        return self::isEnvatoConfigured();
    }

    /**
     * Get Envato API settings with enhanced security and validation.
     *
     * Retrieves and validates Envato API settings from the database.
     * Returns null if settings are not properly configured or if an error occurs.
     * Uses caching for performance optimization.
     *
     * @return array<string, string>|null Array of Envato settings or null if not configured
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * $settings = EnvatoHelper::getEnvatoSettings();
     * if ($settings) {
     *     $token = $settings['personal_token'];
     *     $clientId = $settings['client_id'];
     *     $clientSecret = $settings['client_secret'];
     * }
     */
    public static function getEnvatoSettings(): ?array
    {
        try {
            $result = Cache::remember(self::CACHE_KEY_SETTINGS, self::CACHE_DURATION, function () {
                $setting = Setting::first();
                if (! $setting) {
                    return null;
                }
                // Validate that all required fields are present and not empty
                $requiredFields = [
                    'envato_personal_token',
                    'envato_client_id',
                    'envato_client_secret',
                ];
                foreach ($requiredFields as $field) {
                    if (empty($setting->$field)) {
                        return null;
                    }
                }

                return [
                    'personal_token' => self::sanitizeOutput($setting->envato_personal_token),
                    'client_id' => self::sanitizeOutput($setting->envato_client_id),
                    'client_secret' => self::sanitizeOutput($setting->envato_client_secret),
                ];
            });
            if (is_array($result)) {
                $sanitizedResult = [];
                foreach ($result as $key => $value) {
                    if (is_string($key) && is_string($value)) {
                        $sanitizedResult[$key] = $value;
                    }
                }

                return $sanitizedResult;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error retrieving Envato settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Clear Envato settings cache.
     *
     * Clears the cached Envato configuration and settings data.
     * Should be called when settings are updated.
     *
     * @example
     * // After updating Envato settings
     * EnvatoHelper::clearCache();
     */
    public static function clearCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY_CONFIGURED);
            Cache::forget(self::CACHE_KEY_SETTINGS);
        } catch (\Exception $e) {
            Log::error('Error clearing Envato cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Validate Envato settings format.
     *
     * Validates that the provided Envato settings have the correct format
     * and contain all required fields.
     *
     * @param  array<string, string>  $settings  The settings to validate
     *
     * @return bool True if settings are valid, false otherwise
     *
     * @example
     * $isValid = EnvatoHelper::validateSettings($settings);
     */
    public static function validateSettings(array $settings): bool
    {
        $requiredFields = ['personal_token', 'client_id', 'client_secret'];
        foreach ($requiredFields as $field) {
            if (! isset($settings[$field]) || empty(trim($settings[$field]))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sanitize output to prevent XSS attacks.
     *
     * @param  string|null  $output  The output to sanitize
     *
     * @return string The sanitized output
     */
    private static function sanitizeOutput(?string $output): string
    {
        if ($output === null) {
            return '';
        }

        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }
}
