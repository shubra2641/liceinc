<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Version Helper with Enhanced Security.
 *
 * This helper class provides comprehensive version management functionality
 * including version checking, updating, validation, and history tracking.
 *
 * Features:
 * - Version retrieval and comparison with caching
 * - Secure version updates with database transactions
 * - Version validation and format checking
 * - Version history tracking and management
 * - Update availability checking
 * - Enhanced security measures and input validation
 * - Comprehensive error handling and logging
 */
class VersionHelper
{
    /**
     * Get current version from database with enhanced security.
     *
     * Retrieves the current application version from the database
     * with caching for improved performance.
     *
     * @return string The current version string
     *
     * @throws \Exception When database operations fail
     */
    public static function getCurrentVersion(): string
    {
        try {
            return Cache::remember('app_version', 3600, function () {
                DB::beginTransaction();
                try {
                    $setting = Setting::where('key', 'site_name')->first() ?? Setting::first();
                    $version = $setting->version ?? '1.0.1';
                    DB::commit();

                    return $version;
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to get current version from database', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return '1.0.1';
                }
            });
        } catch (\Exception $e) {
            Log::error('Failed to get current version from cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return '1.0.1';
        }
    }

    /**
     * Get latest version from version.json file with enhanced security.
     *
     * Reads the latest version information from the version.json file
     * with proper file validation and security measures.
     *
     * @return string The latest version string
     *
     * @throws \Exception When file operations fail
     */
    public static function getLatestVersion(): string
    {
        try {
            $versionFile = storage_path('version.json');
            // Validate file path and existence
            if (! file_exists($versionFile) || ! is_readable($versionFile)) {
                Log::warning('Version file not found or not readable', [
                    'file' => $versionFile,
                ]);

                return '1.0.1';
            }
            // Read and validate file content
            $fileContent = file_get_contents($versionFile);
            if ($fileContent === false) {
                Log::error('Failed to read version file content', [
                    'file' => $versionFile,
                ]);

                return '1.0.1';
            }
            $versionData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in version file', [
                    'file' => $versionFile,
                    'json_error' => json_last_error_msg(),
                ]);

                return '1.0.1';
            }
            $version = (is_array($versionData) && isset($versionData['current_version'])) ? $versionData['current_version'] : '1.0.1';
            // Validate version format
            if (! self::isValidVersion($version)) {
                Log::error('Invalid version format in version file', [
                    'file' => $versionFile,
                    'version' => $version,
                ]);

                return '1.0.1';
            }

            return $version;
        } catch (\Exception $e) {
            Log::error('Failed to read version file', [
                'error' => $e->getMessage(),
                'file' => storage_path('version.json'),
                'trace' => $e->getTraceAsString(),
            ]);

            return '1.0.1';
        }
    }

    /**
     * Check if update is available with enhanced validation.
     *
     * Compares the current version with the latest available version
     * to determine if an update is available.
     *
     * @return bool True if update is available, false otherwise
     *
     * @throws \Exception When version comparison fails
     */
    public static function isUpdateAvailable(): bool
    {
        try {
            $currentVersion = self::getCurrentVersion();
            $latestVersion = self::getLatestVersion();
            // Validate both versions before comparison
            if (! self::isValidVersion($currentVersion) || ! self::isValidVersion($latestVersion)) {
                Log::error('Invalid version format during update check', [
                    'current_version' => $currentVersion,
                    'latest_version' => $latestVersion,
                ]);

                return false;
            }

            return version_compare($latestVersion, $currentVersion, '>');
        } catch (\Exception $e) {
            Log::error('Failed to check update availability', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Compare two version numbers with enhanced validation.
     *
     * Compares two semantic version strings and returns the comparison result.
     * Supports standard semantic versioning format (e.g., 1.0.0, 2.1.3).
     *
     * @param  string  $version1  The first version to compare
     * @param  string  $version2  The second version to compare
     *
     * @return int Returns 1 if version1 > version2, -1 if version1 < version2, 0 if equal
     *
     * @throws \InvalidArgumentException When version format is invalid
     *
     * @example
     * $result = VersionHelper::compareVersions('1.2.0', '1.1.0'); // Returns 1
     * $result = VersionHelper::compareVersions('1.0.0', '1.0.0'); // Returns 0
     * $result = VersionHelper::compareVersions('1.0.0', '1.1.0'); // Returns -1
     */
    public static function compareVersions(string $version1, string $version2): int
    {
        try {
            // Validate version formats
            if (! self::isValidVersion($version1)) {
                throw new \InvalidArgumentException("Invalid version format: {$version1}");
            }
            if (! self::isValidVersion($version2)) {
                throw new \InvalidArgumentException("Invalid version format: {$version2}");
            }

            return version_compare($version1, $version2);
        } catch (\Exception $e) {
            Log::error('Failed to compare versions', [
                'error' => $e->getMessage(),
                'version1' => $version1,
                'version2' => $version2,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Update application version in database with enhanced security and validation.
     *
     * Updates the application version in the database with comprehensive
     * validation, security checks, and database transaction support.
     *
     * @param  string  $newVersion  The new version to set
     *
     * @return bool True if update was successful, false otherwise
     *
     * @throws \InvalidArgumentException When version format is invalid
     * @throws \Exception When database operations fail
     */
    public static function updateVersion(string $newVersion): bool
    {
        try {
            DB::beginTransaction();
            // Validate version format
            if (! self::isValidVersion($newVersion)) {
                DB::rollBack();
                Log::error('Invalid version format', [
                    'new_version' => $newVersion,
                    'updated_by' => auth()->id() ?? 'system',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
                throw new \InvalidArgumentException("Invalid version format: {$newVersion}");
            }
            // Get current version
            $currentVersion = self::getCurrentVersion();
            // Check if can update to this version (prevents downgrading)
            if (! self::canUpdateToVersion($newVersion)) {
                DB::rollBack();
                Log::error('Cannot update to older or same version', [
                    'current_version' => $currentVersion,
                    'new_version' => $newVersion,
                    'updated_by' => auth()->id() ?? 'system',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
                throw new \InvalidArgumentException('Cannot update to older or same version. '
                    ."Current: {$currentVersion}, Target: {$newVersion}");
            }
            // Update existing setting or create new one
            $setting = Setting::where('key', 'site_name')->first() ?? Setting::first();
            if (! $setting) {
                $setting = new Setting();
                $setting->key = 'site_name';
                $setting->value = config('app.name', 'License Management System');
                $setting->type = 'string';
                $setting->save();
            }
            // Update the existing record
            $setting->version = $newVersion;
            $setting->last_updated_at = now();
            $setting->save();
            // Record version update in history
            self::recordVersionUpdate($newVersion, "Auto update from {$currentVersion}");
            // Clear version cache
            Cache::forget('app_version');
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update version', [
                'error' => $e->getMessage(),
                'new_version' => $newVersion,
                'current_version' => self::getCurrentVersion(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get version information from version.json with enhanced security.
     *
     * Retrieves version information from the version.json file with
     * proper validation and security measures.
     *
     * @param  string|null  $version  The specific version to get info for
     *
     * @return array Version information array
     *
     * @throws \Exception When file operations fail
     */
    /** @return array<string, mixed> */
    public static function getVersionInfo(?string $version = null): array
    {
        try {
            $versionFile = storage_path('version.json');
            // Validate file path and existence
            if (! file_exists($versionFile) || ! is_readable($versionFile)) {
                Log::warning('Version file not found or not readable', [
                    'file' => $versionFile,
                ]);

                return [];
            }
            // Read and validate file content
            $fileContent = file_get_contents($versionFile);
            if ($fileContent === false) {
                Log::error('Failed to read version file content', [
                    'file' => $versionFile,
                ]);

                return [];
            }
            $versionData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in version file', [
                    'file' => $versionFile,
                    'json_error' => json_last_error_msg(),
                ]);

                return [];
            }
            if ($version) {
                // Validate version format if provided
                if (! self::isValidVersion($version)) {
                    Log::error('Invalid version format requested', [
                        'version' => $version,
                    ]);

                    return [];
                }

                return (is_array($versionData) && isset($versionData['changelog']) && is_array($versionData['changelog']) && isset($versionData['changelog'][$version])) ? $versionData['changelog'][$version] : [];
            }

            return $versionData;
        } catch (\Exception $e) {
            Log::error('Failed to get version info', [
                'error' => $e->getMessage(),
                'version' => $version,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Get update instructions for a version with enhanced security.
     *
     * Retrieves update instructions for a specific version from the
     * version.json file with proper validation.
     *
     * @param  string  $version  The version to get instructions for
     *
     * @return array Update instructions array
     *
     * @throws \InvalidArgumentException When version format is invalid
     * @throws \Exception When file operations fail
     */
    /** @return array<string, mixed> */
    public static function getUpdateInstructions(string $version): array
    {
        try {
            // Validate version format
            if (! self::isValidVersion($version)) {
                Log::error('Invalid version format for update instructions', [
                    'version' => $version,
                ]);
                throw new \InvalidArgumentException("Invalid version format: {$version}");
            }
            $versionFile = storage_path('version.json');
            // Validate file path and existence
            if (! file_exists($versionFile) || ! is_readable($versionFile)) {
                Log::warning('Version file not found or not readable', [
                    'file' => $versionFile,
                ]);

                return [];
            }
            // Read and validate file content
            $fileContent = file_get_contents($versionFile);
            if ($fileContent === false) {
                Log::error('Failed to read version file content', [
                    'file' => $versionFile,
                ]);

                return [];
            }
            $versionData = json_decode($fileContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in version file', [
                    'file' => $versionFile,
                    'json_error' => json_last_error_msg(),
                ]);

                return [];
            }

            return (is_array($versionData) && isset($versionData['update_instructions']) && is_array($versionData['update_instructions']) && isset($versionData['update_instructions'][$version])) ? $versionData['update_instructions'][$version] : [];
        } catch (\Exception $e) {
            Log::error('Failed to get update instructions', [
                'error' => $e->getMessage(),
                'version' => $version,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if version is valid format with enhanced validation.
     *
     * Validates that a version string follows semantic versioning format
     * (e.g., 1.0.0, 2.1.3) with additional security checks.
     *
     * @param  string  $version  The version string to validate
     *
     * @return bool True if version format is valid, false otherwise
     */
    public static function isValidVersion(string $version): bool
    {
        try {
            // Check if version is not empty and within reasonable length
            if (empty($version) || strlen($version) > 20) {
                return false;
            }
            // Check for semantic versioning format (X.Y.Z)
            $isValid = preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
            if ($isValid) {
                // Additional validation: check if version numbers are reasonable
                $parts = explode('.', $version);
                foreach ($parts as $part) {
                    if (intval($part) > 999) {
                        return false;
                    }
                }
            }

            return $isValid;
        } catch (\Exception $e) {
            Log::error('Error validating version format', [
                'error' => $e->getMessage(),
                'version' => $version,
            ]);

            return false;
        }
    }

    /**
     * Get version status for admin dashboard with enhanced security.
     *
     * Retrieves comprehensive version status information including
     * current version, latest version, and update availability.
     *
     * @return array Version status information
     *
     * @throws \Exception When version operations fail
     */
    /** @return array<string, mixed> */
    public static function getVersionStatus(): array
    {
        try {
            $currentVersion = self::getCurrentVersion();
            $latestVersion = self::getLatestVersion();
            $isUpdateAvailable = self::isUpdateAvailable();
            // Validate versions before returning
            if (! self::isValidVersion($currentVersion) || ! self::isValidVersion($latestVersion)) {
                Log::error('Invalid version format in version status', [
                    'current_version' => $currentVersion,
                    'latest_version' => $latestVersion,
                ]);
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
            Log::error('Failed to get version status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if target version is newer than current version with enhanced validation.
     *
     * Prevents downgrading to older versions and validates version formats
     * before performing comparison.
     *
     * @param  string  $targetVersion  The target version to check
     *
     * @return bool True if target version is newer, false otherwise
     *
     * @throws \InvalidArgumentException When version format is invalid
     * @throws \Exception When version comparison fails
     */
    public static function canUpdateToVersion(string $targetVersion): bool
    {
        try {
            $currentVersion = self::getCurrentVersion();
            // Check if target version is valid format
            if (! self::isValidVersion($targetVersion)) {
                Log::error('Invalid target version format', [
                    'target_version' => $targetVersion,
                ]);
                throw new \InvalidArgumentException("Invalid target version format: {$targetVersion}");
            }
            // Check if current version is valid format
            if (! self::isValidVersion($currentVersion)) {
                Log::error('Invalid current version format', [
                    'current_version' => $currentVersion,
                ]);
                throw new \InvalidArgumentException("Invalid current version format: {$currentVersion}");
            }

            // Check if target version is newer than current
            return version_compare($targetVersion, $currentVersion, '>');
        } catch (\Exception $e) {
            Log::error('Failed to check if can update to version', [
                'error' => $e->getMessage(),
                'target_version' => $targetVersion,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get current version from database settings with enhanced security.
     *
     * Retrieves the current version from database settings with
     * proper validation and error handling.
     *
     * @return string The current version from database
     *
     * @throws \Exception When database operations fail
     */
    public static function getCurrentVersionFromDatabase(): string
    {
        try {
            DB::beginTransaction();
            try {
                $setting = Setting::where('key', 'current_version')->first();
                $version = $setting ? $setting->value : '1.0.0';
                // Validate version format
                if (! self::isValidVersion($version)) {
                    Log::error('Invalid version format in database', [
                        'version' => $version,
                    ]);
                    $version = '1.0.0';
                }
                DB::commit();

                return $version;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get current version from database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return '1.0.0';
        }
    }

    /**
     * Update current version in database settings with enhanced security.
     *
     * Updates the current version in database settings with comprehensive
     * validation, security checks, and database transaction support.
     *
     * @param  string  $newVersion  The new version to set
     *
     * @return bool True if update was successful, false otherwise
     *
     * @throws \InvalidArgumentException When version format is invalid
     * @throws \Exception When database operations fail
     */
    public static function updateCurrentVersionInDatabase(string $newVersion): bool
    {
        try {
            DB::beginTransaction();
            // Validate version format
            if (! self::isValidVersion($newVersion)) {
                DB::rollBack();
                Log::error('Invalid version format for database update', [
                    'new_version' => $newVersion,
                    'updated_by' => auth()->id() ?? 'system',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
                throw new \InvalidArgumentException("Invalid version format: {$newVersion}");
            }
            // Check if version is newer than current
            $currentVersion = self::getCurrentVersionFromDatabase();
            if (! self::canUpdateToVersion($newVersion)) {
                DB::rollBack();
                Log::error('Cannot update to older version in database', [
                    'current_version' => $currentVersion,
                    'new_version' => $newVersion,
                    'updated_by' => auth()->id() ?? 'system',
                    'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
                ]);
                throw new \InvalidArgumentException("Cannot update to older version. Current: {$currentVersion}, "
                    ."Target: {$newVersion}");
            }
            // Update or create setting
            $setting = Setting::updateOrCreate(
                ['key' => 'current_version'],
                [
                    'value' => $newVersion,
                    'updated_at' => now(),
                ],
            );
            // Clear cache
            Cache::forget('app_version');
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update current version in database', [
                'error' => $e->getMessage(),
                'new_version' => $newVersion,
                'current_version' => self::getCurrentVersionFromDatabase(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Get version history from database with enhanced security.
     *
     * Retrieves the complete version history from database settings
     * with proper validation and error handling.
     *
     * @return array Version history array
     *
     * @throws \Exception When database operations fail
     */
    /** @return array<string, mixed> */
    public static function getVersionHistory(): array
    {
        try {
            DB::beginTransaction();
            try {
                $versions = Setting::where('key', 'LIKE', 'version_%')
                    ->orderBy('created_at', 'desc')
                    ->get();
                $history = $versions->map(function ($setting) {
                    $version = str_replace('version_', '', $setting->key);
                    // Validate version format
                    if (! self::isValidVersion($version)) {
                        Log::warning('Invalid version format in history', [
                            'version' => $version,
                            'setting_key' => $setting->key,
                        ]);

                        return null;
                    }

                    return [
                        'version' => $version,
                        'updated_at' => $setting->created_at,
                        'value' => $setting->value,
                    ];
                })->filter()->values()->toArray();
                DB::commit();

                return $history;
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get version history', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Record version update in history with enhanced security.
     *
     * Records a version update in the database history with
     * proper validation and database transaction support.
     *
     * @param  string  $version  The version to record
     * @param  string  $details  The update details
     *
     * @return bool True if recording was successful, false otherwise
     *
     * @throws \InvalidArgumentException When version format is invalid
     * @throws \Exception When database operations fail
     */
    public static function recordVersionUpdate(string $version, string $details = ''): bool
    {
        try {
            DB::beginTransaction();
            // Validate version format
            if (! self::isValidVersion($version)) {
                DB::rollBack();
                Log::error('Invalid version format for history recording', [
                    'version' => $version,
                    'details' => $details,
                ]);
                throw new \InvalidArgumentException("Invalid version format: {$version}");
            }
            // Sanitize details to prevent XSS
            $sanitizedDetails = htmlspecialchars($details, ENT_QUOTES, 'UTF-8');
            Setting::updateOrCreate(
                ['key' => "version_{$version}"],
                [
                    'value' => $sanitizedDetails,
                    'updated_at' => now(),
                ],
            );
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record version update', [
                'error' => $e->getMessage(),
                'version' => $version,
                'details' => $details,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
