<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Version Helper - Simplified.
 */
class VersionHelper
{
    /**
     * Get current version from database.
     */
    public static function getCurrentVersion(): string
    {
        try {
            return Cache::remember('app_version', 3600, function () {
                $setting = Setting::where('key', 'site_name')->first() ?? Setting::first();

                return $setting->version ?? '1.0.1';
            });
        } catch (\Exception $e) {
            Log::error('Failed to get current version', ['error' => $e->getMessage()]);

            return '1.0.1';
        }
    }

    /**
     * Get latest version from version.json file.
     */
    public static function getLatestVersion(): string
    {
        try {
            $versionFile = storage_path('version.json');

            if (! file_exists($versionFile) || ! is_readable($versionFile)) {
                return '1.0.1';
            }

            $fileContent = file_get_contents($versionFile);
            if ($fileContent === false) {
                return '1.0.1';
            }

            $versionData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return '1.0.1';
            }

            $version = $versionData['current_version'] ?? '1.0.1';

            return self::isValidVersion($version) ? $version : '1.0.1';
        } catch (\Exception $e) {
            Log::error('Failed to read version file', ['error' => $e->getMessage()]);

            return '1.0.1';
        }
    }

    /**
     * Check if update is available.
     */
    public static function isUpdateAvailable(): bool
    {
        try {
            $currentVersion = self::getCurrentVersion();
            $latestVersion = self::getLatestVersion();

            if (! self::isValidVersion($currentVersion) || ! self::isValidVersion($latestVersion)) {
                return false;
            }

            return version_compare($latestVersion, $currentVersion, '>');
        } catch (\Exception $e) {
            Log::error('Failed to check update availability', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Compare two version numbers.
     */
    public static function compareVersions(string $version1, string $version2): int
    {
        try {
            if (! self::isValidVersion($version1) || ! self::isValidVersion($version2)) {
                throw new \InvalidArgumentException('Invalid version format');
            }

            return version_compare($version1, $version2);
        } catch (\Exception $e) {
            Log::error('Failed to compare versions', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update application version in database.
     */
    public static function updateVersion(string $newVersion): bool
    {
        try {
            DB::beginTransaction();

            if (! self::isValidVersion($newVersion)) {
                throw new \InvalidArgumentException("Invalid version format: {$newVersion}");
            }

            $currentVersion = self::getCurrentVersion();
            if (! self::canUpdateToVersion($newVersion)) {
                throw new \InvalidArgumentException("Cannot update to older version. Current: {$currentVersion}, Target: {$newVersion}");
            }

            $setting = Setting::where('key', 'site_name')->first() ?? Setting::first();
            if (! $setting) {
                $setting = new Setting();
                $setting->key = 'site_name';
                $setting->value = config('app.name', 'License Management System');
                $setting->type = 'string';
                $setting->save();
            }

            $setting->version = $newVersion;
            $setting->last_updated_at = now();
            $setting->save();

            self::recordVersionUpdate($newVersion, "Auto update from {$currentVersion}");
            Cache::forget('app_version');

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update version', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get version information from version.json.
     */
    public static function getVersionInfo(?string $version = null): array
    {
        try {
            $versionFile = storage_path('version.json');

            if (! file_exists($versionFile) || ! is_readable($versionFile)) {
                return [];
            }

            $fileContent = file_get_contents($versionFile);
            if ($fileContent === false) {
                return [];
            }

            $versionData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            if ($version) {
                if (! self::isValidVersion($version)) {
                    return [];
                }

                $changelog = $versionData['changelog'][$version] ?? [];

                return is_array($changelog) ? $changelog : [];
            }

            return is_array($versionData) ? $versionData : [];
        } catch (\Exception $e) {
            Log::error('Failed to get version info', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get update instructions for a version.
     */
    public static function getUpdateInstructions(string $version): array
    {
        try {
            if (! self::isValidVersion($version)) {
                throw new \InvalidArgumentException("Invalid version format: {$version}");
            }

            $versionFile = storage_path('version.json');

            if (! file_exists($versionFile) || ! is_readable($versionFile)) {
                return [];
            }

            $fileContent = file_get_contents($versionFile);
            if ($fileContent === false) {
                return [];
            }

            $versionData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }

            $instructions = $versionData['update_instructions'][$version] ?? [];

            return is_array($instructions) ? $instructions : [];
        } catch (\Exception $e) {
            Log::error('Failed to get update instructions', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check if version is valid format.
     */
    public static function isValidVersion(string $version): bool
    {
        try {
            if (empty($version) || strlen($version) > 20) {
                return false;
            }

            $isValid = preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
            if ($isValid) {
                $parts = explode('.', $version);
                foreach ($parts as $part) {
                    if (intval($part) > 999) {
                        return false;
                    }
                }
            }

            return $isValid;
        } catch (\Exception $e) {
            Log::error('Error validating version format', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get version status for admin dashboard.
     */
    public static function getVersionStatus(): array
    {
        try {
            $currentVersion = self::getCurrentVersion();
            $latestVersion = self::getLatestVersion();
            $isUpdateAvailable = self::isUpdateAvailable();

            if (! self::isValidVersion($currentVersion) || ! self::isValidVersion($latestVersion)) {
                throw new \Exception('Invalid version format detected');
            }

            return [
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'is_update_available' => $isUpdateAvailable,
                'status' => $isUpdateAvailable ? 'update_available' : 'up_to_date',
                'last_checked' => now()->toISOString(),
                'version_comparison' => self::compareVersions($latestVersion, $currentVersion),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get version status', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check if target version is newer than current version.
     */
    public static function canUpdateToVersion(string $targetVersion): bool
    {
        try {
            $currentVersion = self::getCurrentVersion();

            if (! self::isValidVersion($targetVersion)) {
                throw new \InvalidArgumentException("Invalid target version format: {$targetVersion}");
            }

            if (! self::isValidVersion($currentVersion)) {
                throw new \InvalidArgumentException("Invalid current version format: {$currentVersion}");
            }

            return version_compare($targetVersion, $currentVersion, '>');
        } catch (\Exception $e) {
            Log::error('Failed to check if can update to version', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get current version from database settings.
     */
    public static function getCurrentVersionFromDatabase(): string
    {
        try {
            $setting = Setting::where('key', 'current_version')->first();
            $version = $setting ? $setting->value : '1.0.0';

            if ($version && ! self::isValidVersion($version)) {
                $version = '1.0.0';
            }

            return $version ?? '1.0.0';
        } catch (\Exception $e) {
            Log::error('Failed to get current version from database', ['error' => $e->getMessage()]);

            return '1.0.0';
        }
    }

    /**
     * Update current version in database settings.
     */
    public static function updateCurrentVersionInDatabase(string $newVersion): bool
    {
        try {
            DB::beginTransaction();

            if (! self::isValidVersion($newVersion)) {
                throw new \InvalidArgumentException("Invalid version format: {$newVersion}");
            }

            $currentVersion = self::getCurrentVersionFromDatabase();
            if (! self::canUpdateToVersion($newVersion)) {
                throw new \InvalidArgumentException("Cannot update to older version. Current: {$currentVersion}, Target: {$newVersion}");
            }

            SettingHelper::updateOrCreateSetting(
                'current_version',
                $newVersion,
                'version',
                $newVersion,
            );

            Cache::forget('app_version');
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update current version in database', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get version history from database.
     */
    public static function getVersionHistory(): array
    {
        try {
            $versions = Setting::where('key', 'LIKE', 'version_%')
                ->orderBy('created_at', 'desc')
                ->get();

            $history = $versions->map(function ($setting) {
                $version = str_replace('version_', '', $setting->key ?? '');

                if (! self::isValidVersion($version)) {
                    return null;
                }

                return [
                    'version' => $version,
                    'updated_at' => $setting->created_at,
                    'value' => $setting->value,
                ];
            })->filter()->values()->toArray();

            return $history;
        } catch (\Exception $e) {
            Log::error('Failed to get version history', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Record version update in history.
     */
    public static function recordVersionUpdate(string $version, string $details = ''): bool
    {
        try {
            DB::beginTransaction();

            if (! self::isValidVersion($version)) {
                throw new \InvalidArgumentException("Invalid version format: {$version}");
            }

            $sanitizedDetails = htmlspecialchars($details, ENT_QUOTES, 'UTF-8');
            SettingHelper::updateOrCreateSetting(
                "version_{$version}",
                $sanitizedDetails,
                'version',
            );

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record version update', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
