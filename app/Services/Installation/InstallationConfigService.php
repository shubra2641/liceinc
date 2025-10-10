<?php

declare(strict_types=1);

namespace App\Services\Installation;

/**
 * Service for managing installation configuration data.
 */
class InstallationConfigService
{
    /**
     * Get timezones configuration.
     *
     * @return array<string, string> The timezones configuration
     */
    public function getTimezones(): array
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'Eastern Time (US & Canada)',
            'America/Chicago' => 'Central Time (US & Canada)',
            'America/Denver' => 'Mountain Time (US & Canada)',
            'America/Los_Angeles' => 'Pacific Time (US & Canada)',
            'Europe/London' => 'London',
            'Europe/Paris' => 'Paris',
            'Europe/Berlin' => 'Berlin',
            'Asia/Dubai' => 'Dubai',
            'Asia/Riyadh' => 'Riyadh',
            'Asia/Kuwait' => 'Kuwait',
            'Asia/Qatar' => 'Qatar',
            'Asia/Bahrain' => 'Bahrain',
            'Africa/Cairo' => 'Cairo',
            'Asia/Tokyo' => 'Tokyo',
            'Australia/Sydney' => 'Sydney',
        ];
    }

    /**
     * Get supported languages.
     *
     * @return array<string, string> The supported languages
     */
    public function getSupportedLanguages(): array
    {
        return [
            'en' => 'English',
            'ar' => 'العربية',
        ];
    }

    /**
     * Get default system settings.
     *
     * @return array<string, mixed> The default system settings
     */
    public function getDefaultSettings(): array
    {
        return [
            'app_name' => 'License Management System',
            'app_url' => url('/'),
            'timezone' => 'UTC',
            'locale' => 'en',
            'currency' => 'USD',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i:s',
        ];
    }
}
