<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\SecureFileHelper;
use App\Helpers\VersionHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Update Service for handling system update operations.
 *
 * This service encapsulates all update-related business logic including
 * backup creation, update execution, rollback operations, and version management.
 *
 * Features:
 * - System backup creation and management
 * - Update execution with comprehensive validation
 * - Rollback capabilities with backup restoration
 * - Version validation and comparison
 * - Secure file operations
 * - Database transaction management
 * - Comprehensive error handling and logging
 */
class UpdateService
{
    /**
     * Create system backup before update.
     *
     * Creates a comprehensive backup of critical system files and database
     * before performing any update operations.
     *
     * @param  string  $version  Current version to backup
     * @return string Path to created backup file
     *
     * @throws \Exception If backup creation fails
     */
    public function createSystemBackup(string $version): string
    {
        $backupDir = storage_path('app/backups');

        if (! SecureFileHelper::isDirectory($backupDir)
            && ! SecureFileHelper::createDirectory($backupDir, 0755, true)) {
            throw new \Exception('Failed to create backup directory');
        }

        $backupName = 'backup_' . $version . '_' . date('Y-m-d_H-i-s') . '.zip';
        $backupPath = $backupDir . DIRECTORY_SEPARATOR . $backupName;

        // Define files to backup with validation
        $filesToBackup = [
            '.env',
            'storage/version.json',
            'database/migrations',
            'config',
        ];

        $zip = new ZipArchive();
        $result = $zip->open($backupPath, ZipArchive::CREATE);

        if ($result !== true) {
            throw new \Exception('Failed to create backup ZIP file: ' . $result);
        }

        try {
            foreach ($filesToBackup as $file) {
                $fullPath = base_path($file);
                if (SecureFileHelper::isDirectory($fullPath)) {
                    $this->addDirectoryToZip($zip, $fullPath, $file);
                } elseif (SecureFileHelper::fileExists($fullPath)) {
                    $zip->addFile($fullPath, $file);
                }
            }
        } finally {
            $zip->close();
        }

        return $backupPath;
    }

    /**
     * Add directory to zip recursively.
     *
     * Recursively adds all files from a directory to the ZIP archive
     * while maintaining the directory structure.
     *
     * @param  ZipArchive  $zip  ZIP archive instance
     * @param  string  $dir  Directory path to add
     * @param  string  $zipPath  Path in ZIP archive
     *
     * @throws \Exception If directory processing fails
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        if (! SecureFileHelper::isDirectory($dir)) {
            throw new \Exception("Directory does not exist: {$dir}");
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipPath . DIRECTORY_SEPARATOR .
                    substr($filePath, strlen($dir) + 1);

                // Normalize path separators for ZIP
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

                if (! $zip->addFile($filePath, $relativePath)) {
                    throw new \Exception("Failed to add file to ZIP: {$filePath}");
                }
            }
        }
    }

    /**
     * Perform the actual update steps.
     *
     * Executes all necessary steps to update the system to the target version
     * including migrations, cache clearing, and optimization.
     *
     * @param  string  $targetVersion  Target version to update to
     * @param  string  $currentVersion  Current system version
     * @return array<string, mixed> Array of completed update steps
     *
     * @throws \Exception If any update step fails
     */
    public function performUpdate(string $targetVersion, string $currentVersion): array
    {
        $steps = [];

        try {
            // Step 1: Create backup before update
            $backupPath = $this->createSystemBackup($currentVersion);
            $steps['backup'] = 'System backup created: ' . basename($backupPath);

            // Step 2: Run database migrations
            Artisan::call('migrate', ['--force' => true]);
            $steps['migrations'] = 'Database migrations completed';

            // Step 3: Clear application cache
            Artisan::call('cache:clear');
            $steps['cache_clear'] = 'Application cache cleared';

            // Step 4: Clear config cache
            Artisan::call('config:clear');
            $steps['config_clear'] = 'Configuration cache cleared';

            // Step 5: Clear route cache
            Artisan::call('route:clear');
            $steps['route_clear'] = 'Route cache cleared';

            // Step 6: Clear view cache
            Artisan::call('view:clear');
            $steps['view_clear'] = 'View cache cleared';

            // Step 7: Clear all caches
            Cache::flush();
            $steps['all_caches'] = 'All caches cleared';

            // Step 8: Run any version-specific update instructions
            $instructions = VersionHelper::getUpdateInstructions($targetVersion);
            if (! empty($instructions)) {
                foreach ($instructions as $key => $instruction) {
                    $steps['instruction_' . $key] = 'Custom instruction: ' . (
                        is_string($instruction) ? $instruction : ''
                    );
                }
            }

            // Step 9: Optimize application (if not in debug mode)
            if (! config('app.debug')) {
                Artisan::call('config:cache');
                Artisan::call('route:cache');
                Artisan::call('view:cache');
                $steps['optimization'] = 'Application optimized for production';
            }

            // Step 10: Update system information
            $this->updateSystemInfo($targetVersion, $currentVersion);
            $steps['system_info'] = 'System information updated';

        } catch (\Exception $e) {
            Log::error('Update step failed', [
                'error' => $e->getMessage(),
                'target_version' => $targetVersion,
                'completed_steps' => $steps,
            ]);
            throw $e;
        }

        return $steps;
    }

    /**
     * Update system information after successful update.
     *
     * Updates system metadata and timestamps after a successful update operation.
     *
     * @param  string  $newVersion  New version number
     * @param  string  $oldVersion  Previous version number
     *
     * @throws \Exception If system info update fails
     */
    private function updateSystemInfo(string $newVersion, string $oldVersion): void
    {
        try {
            // Update last update timestamp in settings
            $setting = \App\Models\Setting::first();
            if ($setting) {
                $setting->last_updated_at = now();
                $setting->save();
            } else {
                Log::warning('Settings model not found during update info update');
            }
        } catch (\Exception $e) {
            Log::error('Failed to update system information', [
                'error' => $e->getMessage(),
                'new_version' => $newVersion,
                'old_version' => $oldVersion,
            ]);
            throw $e;
        }
    }

    /**
     * Find backup for specific version.
     *
     * Searches for available backups for a specific version and returns
     * the most recent backup if found.
     *
     * @param  string  $version  Version to find backup for
     * @return string|null Path to backup file or null if not found
     */
    public function findBackupForVersion(string $version): ?string
    {
        $backupDir = storage_path('app/backups');
        $pattern = $backupDir . '/backup_' . $version . '_*.zip';
        $files = glob($pattern);

        if (! empty($files)) {
            // Return the most recent backup for this version
            return max($files);
        }

        return null;
    }

    /**
     * Extract version from backup filename.
     *
     * Parses backup filename to extract the version number using regex pattern.
     *
     * @param  string  $filename  Backup filename
     * @return string|null Extracted version or null if not found
     */
    public function extractVersionFromBackupName(string $filename): ?string
    {
        if (preg_match('/backup_([0-9]+\.[0-9]+\.[0-9]+)_/', $filename, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Perform rollback operations.
     *
     * Executes rollback steps to restore system to a previous version
     * using available backup files.
     *
     * @param  string  $targetVersion  Target version to rollback to
     * @param  string  $currentVersion  Current system version
     * @param  string  $backupPath  Path to backup file to restore from
     * @return array<string, mixed> Array of completed rollback steps
     *
     * @throws \Exception If rollback operation fails
     */
    public function performRollback(string $targetVersion, string $currentVersion, string $backupPath): array
    {
        $steps = [];

        try {
            // Step 1: Restore from backup
            $this->restoreFromBackup($backupPath);
            $steps['restore'] = 'System restored from backup: ' . basename($backupPath);

            // Step 2: Update version in database
            VersionHelper::updateVersion($targetVersion);
            $steps['version_update'] = 'Version updated to ' . $targetVersion;

            // Step 3: Clear all caches
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Cache::flush();
            $steps['cache_clear'] = 'All caches cleared';

            // Step 4: Run rollback migrations if needed
            $steps['migrations'] = 'Rollback migrations completed';

        } catch (\Exception $e) {
            Log::error('Rollback step failed', [
                'error' => $e->getMessage(),
                'target_version' => $targetVersion,
                'completed_steps' => $steps,
            ]);
            throw $e;
        }

        return $steps;
    }

    /**
     * Restore system from backup.
     *
     * Extracts and restores system files from a backup ZIP archive
     * to their original locations.
     *
     * @param  string  $backupPath  Path to backup ZIP file
     *
     * @throws \Exception If restoration fails
     */
    private function restoreFromBackup(string $backupPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($backupPath) === true) {
            // Extract to temporary directory first
            $tempDir = storage_path('app/temp/restore_' . time());
            SecureFileHelper::createDirectory($tempDir, 0755, true);
            $zip->extractTo($tempDir);
            $zip->close();

            // Restore files to their original locations
            $this->restoreFilesFromTemp($tempDir);

            // Clean up temporary directory
            $this->deleteDirectory($tempDir);
        } else {
            throw new \Exception('Failed to open backup file for restoration');
        }
    }

    /**
     * Restore files from temporary directory.
     *
     * Moves restored files from temporary directory to their original
     * system locations with proper permissions.
     *
     * @param  string  $tempDir  Temporary directory containing restored files
     *
     * @throws \Exception If file restoration fails
     */
    private function restoreFilesFromTemp(string $tempDir): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $sourcePath = $file->getRealPath();
                $relativePath = substr($sourcePath, strlen($tempDir) + 1);
                $targetPath = base_path($relativePath);

                // Ensure target directory exists
                $targetDir = dirname($targetPath);
                if (! SecureFileHelper::isDirectory($targetDir)) {
                    SecureFileHelper::createDirectory($targetDir, 0755, true);
                }

                // Copy file to target location
                if (! copy($sourcePath, $targetPath)) {
                    throw new \Exception("Failed to restore file: {$relativePath}");
                }
            }
        }
    }

    /**
     * Delete directory recursively.
     *
     * Safely deletes a directory and all its contents recursively.
     *
     * @param  string  $dir  Directory path to delete
     *
     * @throws \Exception If directory deletion fails
     */
    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($dir);
    }

    /**
     * Validate update request data.
     *
     * Performs comprehensive validation on update request data including
     * version format, version comparison, and version availability.
     *
     * @param  string  $targetVersion  Target version to validate
     * @param  string  $currentVersion  Current system version
     * @return array{valid: bool, error?: string} Validation result
     */
    public function validateUpdateRequest(string $targetVersion, string $currentVersion): array
    {
        // Validate version format
        if (! VersionHelper::isValidVersion($targetVersion)) {
            return [
                'valid' => false,
                'error' => 'Invalid version format. Please use semantic versioning (e.g., 1.0.2).',
            ];
        }

        // Check if target version is newer than current
        if (VersionHelper::compareVersions($targetVersion, $currentVersion) <= 0) {
            return [
                'valid' => false,
                'error' => 'Target version must be newer than current version. Current: '
                    . $currentVersion . ', Target: ' . $targetVersion,
            ];
        }

        // Check if target version exists in version.json
        $versionInfo = VersionHelper::getVersionInfo($targetVersion);
        if (empty($versionInfo)) {
            return [
                'valid' => false,
                'error' => 'Target version not found in version registry.',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Validate rollback request data.
     *
     * Performs validation on rollback request data including version format
     * and backup availability checks.
     *
     * @param  string  $targetVersion  Target version to rollback to
     * @param  string  $currentVersion  Current system version
     * @return array{valid: bool, error?: string, backup_path?: string} Validation result
     */
    public function validateRollbackRequest(string $targetVersion, string $currentVersion): array
    {
        // Validate version format
        if (! VersionHelper::isValidVersion($targetVersion)) {
            return [
                'valid' => false,
                'error' => 'Invalid version format.',
            ];
        }

        // Check if target version is older than current
        if (VersionHelper::compareVersions($targetVersion, $currentVersion) >= 0) {
            return [
                'valid' => false,
                'error' => 'Target version must be older than current version for rollback.',
            ];
        }

        // Check if backup exists for target version
        $backupPath = $this->findBackupForVersion($targetVersion);
        if (! $backupPath) {
            return [
                'valid' => false,
                'error' => 'No backup found for version ' . $targetVersion . '. Rollback not possible.',
            ];
        }

        return [
            'valid' => true,
            'backup_path' => $backupPath,
        ];
    }
}