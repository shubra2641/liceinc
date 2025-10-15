<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VersionHelper
{
    public static function getCurrentVersion(): string
    {
        try {
            return Cache::remember('app_version', 3600, function () {
                $setting = Setting::where('key', 'site_name')->first() ?? Setting::first();
                return $setting->version ?? '1.0.1';
            });
        } catch (\Exception $e) {
            Log::error('Failed to get current version: ' . $e->getMessage());
            return '1.0.1';
        }
    }

    public static function getLatestVersion(): string
    {
        try {
            $versionFile = storage_path('version.json');
            if (!file_exists($versionFile) || !is_readable($versionFile)) {
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
            Log::error('Failed to get latest version: ' . $e->getMessage());
            return '1.0.1';
        }
    }

    public static function isUpdateAvailable(): bool
    {
        try {
            $currentVersion = self::getCurrentVersion();
            $latestVersion = self::getLatestVersion();
            
            if (!self::isValidVersion($currentVersion) || !self::isValidVersion($latestVersion)) {
                return false;
            }

            return version_compare($latestVersion, $currentVersion, '>');
        } catch (\Exception $e) {
            Log::error('Failed to check update availability: ' . $e->getMessage());
            return false;
        }
    }

    public static function compareVersions(string $version1, string $version2): int
    {
        if (!self::isValidVersion($version1) || !self::isValidVersion($version2)) {
            throw new \InvalidArgumentException('Invalid version format');
        }

        return version_compare($version1, $version2);
    }

    public static function updateVersion(string $newVersion): bool
    {
        try {
            if (!self::isValidVersion($newVersion)) {
                throw new \InvalidArgumentException("Invalid version format: {$newVersion}");
            }

            $currentVersion = self::getCurrentVersion();
            if (version_compare($newVersion, $currentVersion, '<=')) {
                throw new \InvalidArgumentException('Cannot update to older or same version');
            }

            DB::beginTransaction();
            
            $setting = Setting::where('key', 'site_name')->first() ?? Setting::first();
            if (!$setting) {
                $setting = new Setting();
                $setting->key = 'site_name';
                $setting->value = config('app.name', 'License Management System');
                $setting->type = 'string';
                $setting->save();
            }

            $setting->version = $newVersion;
            $setting->last_updated_at = now();
            $setting->save();

            Cache::forget('app_version');
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update version: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getVersionInfo(?string $version = null): array
    {
        try {
            $versionFile = storage_path('version.json');
            if (!file_exists($versionFile) || !is_readable($versionFile)) {
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
                if (!self::isValidVersion($version)) {
                    return [];
                }
                return $versionData['changelog'][$version] ?? [];
            }

            return $versionData;
        } catch (\Exception $e) {
            Log::error('Failed to get version info: ' . $e->getMessage());
            return [];
        }
    }

    public static function getUpdateInstructions(string $version): array
    {
        try {
            if (!self::isValidVersion($version)) {
                throw new \InvalidArgumentException("Invalid version format: {$version}");
            }

            $versionFile = storage_path('version.json');
            if (!file_exists($versionFile) || !is_readable($versionFile)) {
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

            return $versionData['update_instructions'][$version] ?? [];
        } catch (\Exception $e) {
            Log::error('Failed to get update instructions: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function isValidVersion(string $version): bool
    {
        if (empty($version) || strlen($version) > 20) {
            return false;
        }

        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            return false;
        }

        $parts = explode('.', $version);
        foreach ($parts as $part) {
            if (intval($part) > 999) {
                return false;
            }
        }

        return true;
    }

    public static function getVersionStatus(): array
    {
        try {
            $currentVersion = self::getCurrentVersion();
            $latestVersion = self::getLatestVersion();
            $isUpdateAvailable = self::isUpdateAvailable();

            if (!self::isValidVersion($currentVersion) || !self::isValidVersion($latestVersion)) {
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
            Log::error('Failed to get version status: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function canUpdateToVersion(string $targetVersion): bool
    {
        if (!self::isValidVersion($targetVersion)) {
            throw new \InvalidArgumentException("Invalid target version format: {$targetVersion}");
        }

        $currentVersion = self::getCurrentVersion();
        if (!self::isValidVersion($currentVersion)) {
            throw new \InvalidArgumentException("Invalid current version format: {$currentVersion}");
        }

        return version_compare($targetVersion, $currentVersion, '>');
    }

    public static function getCurrentVersionFromDatabase(): string
    {
        try {
            $setting = Setting::where('key', 'current_version')->first();
            $version = $setting ? $setting->value : '1.0.0';
            
            if ($version && !self::isValidVersion($version)) {
                $version = '1.0.0';
            }

            return $version ?? '1.0.0';
        } catch (\Exception $e) {
            Log::error('Failed to get current version from database: ' . $e->getMessage());
            return '1.0.0';
        }
    }

    public static function updateCurrentVersionInDatabase(string $newVersion): bool
    {
        try {
            if (!self::isValidVersion($newVersion)) {
                throw new \InvalidArgumentException("Invalid version format: {$newVersion}");
            }

            $currentVersion = self::getCurrentVersionFromDatabase();
            if (!self::canUpdateToVersion($newVersion)) {
                throw new \InvalidArgumentException("Cannot update to older version. Current: {$currentVersion}, Target: {$newVersion}");
            }

            DB::beginTransaction();
            
            \App\Helpers\SettingHelper::updateOrCreateSetting(
                'current_version',
                $newVersion,
                'version',
                $newVersion
            );

            Cache::forget('app_version');
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update current version in database: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function getVersionHistory(): array
    {
        try {
            $versions = Setting::where('key', 'LIKE', 'version_%')
                ->orderBy('created_at', 'desc')
                ->get();

            return $versions->map(function ($setting) {
                $version = str_replace('version_', '', $setting->key ?? '');
                
                if (!self::isValidVersion($version)) {
                    return null;
                }

                return [
                    'version' => $version,
                    'updated_at' => $setting->created_at,
                    'value' => $setting->value,
                ];
            })->filter()->values()->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get version history: ' . $e->getMessage());
            return [];
        }
    }

    public static function recordVersionUpdate(string $version, string $details = ''): bool
    {
        try {
            if (!self::isValidVersion($version)) {
                throw new \InvalidArgumentException("Invalid version format: {$version}");
            }

            DB::beginTransaction();
            
            $sanitizedDetails = htmlspecialchars($details, ENT_QUOTES, 'UTF-8');
            \App\Helpers\SettingHelper::updateOrCreateSetting(
                "version_{$version}",
                $sanitizedDetails,
                'version'
            );
            
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record version update: ' . $e->getMessage());
            throw $e;
        }
    }
}
