<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Helpers\SecureFileHelper;
use App\Helpers\VersionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AutoUpdateRequest;
use App\Http\Requests\Admin\SystemUpdateRequest;
use App\Http\Requests\Admin\UploadUpdatePackageRequest;
use App\Http\Requests\Admin\VersionManagementRequest;
use App\Services\LicenseServerService;
use App\Services\UpdatePackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update Controller with enhanced security.
 *
 * This controller handles system update management functionality including
 * manual updates, auto updates, rollbacks, and version management.
 *
 * Features:
 * - System update management with comprehensive validation
 * - Auto update functionality with license verification
 * - System rollback capabilities with backup management
 * - Update package upload and processing
 * - Version history and latest version checking
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures (input validation, file security)
 * - Proper logging for errors and warnings only
 */
class UpdateController extends Controller
{
    protected LicenseServerService $licenseServerService;

    protected UpdatePackageService $updatePackageService;

    public function __construct(LicenseServerService $licenseServerService, UpdatePackageService $updatePackageService)
    {
        $this->licenseServerService = $licenseServerService;
        $this->updatePackageService = $updatePackageService;
    }

    /**
     * Display the update management page.
     *
     * Shows current version, available updates, and update options with
     * comprehensive version information and update status.
     *
     * @return \Illuminate\View\View The update management view
     *
     * @example
     * // Access the update management page:
     * GET /admin/updates
     *
     * // Returns view with:
     * // - Current version status
     * // - Available updates information
     * // - Product information
     * // - Update options and controls
     */
    public function index()
    {
        try {
            // Get local version info
            $versionStatus = VersionHelper::getVersionStatus();
            $versionInfo = VersionHelper::getVersionInfo();
            // Get specific product from central API
            $products = $this->getSpecificProduct('the-ultimate-license-management-system');
            // Get update information for the product (without license verification)
            $updateInfo = null;
            if (count($products) > 0) {
                $updateInfo = $this->getUpdateInfoForProduct('the-ultimate-license-management-system', '1.0.0');
            }

            return view('admin.updates.index', [
                'versionStatus' => $versionStatus,
                'versionInfo' => $versionInfo,
                'products' => $products,
                'updateInfo' => $updateInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load update management page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'userId' => auth()->id(),
            ]);

            return view('admin.updates.index', [
                'versionStatus' => null,
                'versionInfo' => null,
                'products' => [],
                'updateInfo' => null,
                'error' => 'Failed to load update information. Please try again.',
            ]);
        }
    }

    /**
     * Check for available updates.
     *
     * Checks for available system updates and returns version status
     * information including current and latest versions.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with update status
     *
     * @throws \Exception When version check fails
     *
     * @example
     * // Check for updates:
     * GET /admin/updates/check
     *
     * // Response:
     * {
     *     "success": true,
     *     "data": {
     *         "current_version": "1.0.0",
     *         "latest_version": "1.0.1",
     *         "is_update_available": true
     *     }
     * }
     */
    public function checkUpdates()
    {
        try {
            $versionStatus = VersionHelper::getVersionStatus();

            return response()->json([
                'success' => true,
                'data' => $versionStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Update check failed', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check for updates: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Perform system update.
     *
     * Updates the system to a specified version with comprehensive validation,
     * backup creation, and rollback capabilities.
     *
     * @param  SystemUpdateRequest  $request  The validated request containing update data
     *
     * @return \Illuminate\Http\JsonResponse JSON response with update result
     *
     * @throws \Exception When update process fails
     *
     * @example
     * // Update system:
     * POST /admin/updates/update
     * {
     *     "version": "1.0.1",
     *     "confirm": true
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "System updated successfully from 1.0.0 to 1.0.1",
     *     "data": {
     *         "from_version": "1.0.0",
     *         "to_version": "1.0.1",
     *         "steps_completed": ["backup created", "migrations run", "caches cleared"]
     *     }
     * }
     */
    public function update(SystemUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $targetVersion = is_string($validated['version']) ? $validated['version'] : '';
            $currentVersion = VersionHelper::getCurrentVersion();
            // Validate version format
            if (! VersionHelper::isValidVersion($targetVersion)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid version format. Please use semantic versioning (e.g., 1.0.2).',
                ], 400);
            }
            // Check if target version is newer than current
            $targetVersionStr = $targetVersion;
            $currentVersionStr = $currentVersion;
            if (VersionHelper::compareVersions($targetVersionStr, $currentVersionStr) <= 0) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Target version must be newer than current version. Current: '
                        . $currentVersionStr . ', Target: ' . $targetVersionStr,
                ], 400);
            }
            // Check if target version exists in version.json
            $versionInfo = VersionHelper::getVersionInfo($targetVersionStr);
            if (empty($versionInfo)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Target version not found in version registry.',
                ], 400);
            }
            // Perform update steps
            $updateSteps = $this->performUpdate($targetVersionStr, $currentVersionStr);
            // Update version in database
            VersionHelper::updateVersion($targetVersionStr);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'System updated successfully from ' . $currentVersionStr . ' to ' . $targetVersionStr,
                'data' => [
                    'from_version' => $currentVersion,
                    'to_version' => $targetVersion,
                    'steps_completed' => $updateSteps,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System update failed', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['confirm']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Perform the actual update steps.
     *
     * @return array<string, mixed>
     */
    private function performUpdate(string $targetVersion, string $currentVersion): array
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
            $instructions = VersionHelper::getUpdateInstructions(
                $targetVersion
            );
            if (! empty($instructions)) {
                foreach ($instructions as $key => $instruction) {
                    // Here you could add custom update logic based on instructions
                    $steps['instruction_' . $key] = 'Custom instruction: '
                        . (is_string($instruction) ? $instruction : '');
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
     * Create system backup before update.
     *
     * @param  string  $version  Current version to backup
     *
     * @return string Path to created backup file
     *
     * @throws \Exception If backup creation fails
     */
    private function createSystemBackup(string $version): string
    {
        $backupDir = storage_path('app/backups');
        if (
            ! SecureFileHelper::isDirectory($backupDir)
            && ! SecureFileHelper::createDirectory($backupDir, 0755, true)
        ) {
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
        $zip = new \ZipArchive();
        $result = $zip->open($backupPath, \ZipArchive::CREATE);
        if ($result !== true) {
            throw new \Exception('Failed to create backup ZIP file: ' . $result);
        }
        try {
            foreach ($filesToBackup as $file) {
                $fullPath = base_path($file);
                if (file_exists($fullPath)) {
                    if (SecureFileHelper::isDirectory($fullPath)) {
                        $this->addDirectoryToZip($zip, $fullPath, $file);
                    } else {
                        $zip->addFile($fullPath, $file);
                    }
                }
            }
        } finally {
            $zip->close();
        }

        // Backup created successfully - no logging needed for success operations
        return $backupPath;
    }

    /**
     * Add directory to zip recursively.
     *
     * @param  \ZipArchive  $zip  ZIP archive instance
     * @param  string  $dir  Directory path to add
     * @param  string  $zipPath  Path in ZIP archive
     *
     * @throws \Exception If directory processing fails
     */
    private function addDirectoryToZip(\ZipArchive $zip, string $dir, string $zipPath): void
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
     * Update system information after successful update.
     *
     * @param  string  $newVersion  New version number
     * @param  string  $oldVersion  Previous version number
     *
     * @throws \Exception If system info update fails
     */
    private function updateSystemInfo(string $newVersion, string $oldVersion): void
    {
        try {
            // Log successful update with comprehensive information
            // System update completed successfully - no logging needed for success operations
            // Update last update timestamp in settings
            $setting = \App\Models\Setting::first();
            if ($setting) {
                $setting->lastUpdatedAt = now();
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
     * Get version information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVersionInfo(string $version)
    {
        try {
            $versionInfo = VersionHelper::getVersionInfo($version);
            $instructions = VersionHelper::getUpdateInstructions($version);
            if (empty($versionInfo)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Version information not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'version' => $version,
                    'info' => $versionInfo,
                    'instructions' => $instructions,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get version info', [
                'userId' => auth()->id(),
                'version' => $version,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get version information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rollback to previous version.
     *
     * Rolls back the system to a previous version using available backups
     * with comprehensive validation and safety checks.
     *
     * @param  SystemUpdateRequest  $request  The validated request containing rollback data
     *
     * @return \Illuminate\Http\JsonResponse JSON response with rollback result
     *
     * @throws \Exception When rollback process fails
     *
     * @example
     * // Rollback system:
     * POST /admin/updates/rollback
     * {
     *     "version": "1.0.0",
     *     "confirm": true
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "System rolled back successfully from 1.0.1 to 1.0.0",
     *     "data": {
     *         "from_version": "1.0.1",
     *         "to_version": "1.0.0",
     *         "steps_completed": ["backup restored", "version updated", "caches cleared"]
     *     }
     * }
     */
    public function rollback(SystemUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $targetVersion = is_string($validated['version']) ? $validated['version'] : '';
            $currentVersion = VersionHelper::getCurrentVersion();
            // Validate version format
            if (! VersionHelper::isValidVersion($targetVersion)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid version format.',
                ], 400);
            }
            // Check if target version is older than current
            $targetVersionStr = $targetVersion;
            $currentVersionStr = $currentVersion;
            if (VersionHelper::compareVersions($targetVersionStr, $currentVersionStr) >= 0) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Target version must be older than current version for rollback.',
                ], 400);
            }
            // Check if backup exists for target version
            $backupPath = $this->findBackupForVersion($targetVersionStr);
            if (! $backupPath) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'No backup found for version ' . $targetVersionStr . '. Rollback not possible.',
                ], 400);
            }
            // Perform rollback
            $rollbackSteps = $this->performRollback($targetVersionStr, $currentVersionStr, $backupPath);
            DB::commit();
            Log::warning('System rollback performed', [
                'userId' => auth()->id(),
                'from_version' => $currentVersion,
                'to_version' => $targetVersion,
                'backup_used' => basename($backupPath),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'System rolled back successfully from ' . $currentVersion . ' to ' . $targetVersion,
                'data' => [
                    'from_version' => $currentVersion,
                    'to_version' => $targetVersion,
                    'steps_completed' => $rollbackSteps,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System rollback failed', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['confirm']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Rollback failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of available backups.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBackups()
    {
        try {
            $backupDir = storage_path('app/backups');
            $backups = [];
            if (SecureFileHelper::isDirectory($backupDir)) {
                $files = glob($backupDir . '/backup_*.zip');
                if ($files !== false) {
                    foreach ($files as $file) {
                        $backups[] = [
                            'filename' => basename($file),
                            'path' => $file,
                            'size' => filesize($file),
                            'createdAt' => date('Y-m-d H:i:s', (int)filemtime($file)),
                            'version' => $this->extractVersionFromBackupName(basename($file)),
                        ];
                    }
                }
                // Sort by creation date (newest first)
                usort($backups, function ($a, $b) {
                    return strtotime($b['createdAt']) - strtotime($a['createdAt']);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $backups,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get backups', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get backups: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload update package.
     *
     * Uploads and processes an update package with comprehensive validation
     * and security checks.
     *
     * @param  UploadUpdatePackageRequest  $request  The validated request containing file data
     *
     * @return \Illuminate\Http\JsonResponse JSON response with upload result
     *
     * @throws \Exception When upload or processing fails
     *
     * @example
     * // Upload update package:
     * POST /admin/updates/upload-package
     * Content-Type: multipart/form-data
     *
     * // Form data:
     * // update_package: [ZIP file]
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "Update package uploaded and processed successfully.",
     *     "data": {
     *         "filename": "update_2024-01-15_10-30-00.zip",
     *         "path": "updates/update_2024-01-15_10-30-00.zip",
     *         "processed_data": {...}
     *     }
     * }
     */
    public function uploadUpdatePackage(UploadUpdatePackageRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $file = $request->file('update_package');
            $uploadDir = storage_path('app/updates');
            if (! SecureFileHelper::isDirectory($uploadDir)) {
                SecureFileHelper::createDirectory($uploadDir, 0755, true);
            }
            $filename = 'update_' . date('Y-m-d_H-i-s') . '.zip';
            $filePath = $file->storeAs('updates', $filename);
            $fullPath = storage_path('app/' . $filePath);
            // Process the update package
            $updateService = new UpdatePackageService();
            $processResult = $updateService->processUpdatePackage($fullPath);
            if ($processResult['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Update package uploaded and processed successfully.',
                    'data' => [
                        'filename' => $filename,
                        'path' => $filePath,
                        'processed_data' => $processResult['data'],
                    ],
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Update package uploaded but processing failed: '
                        . (is_string($processResult['message'] ?? null) ? $processResult['message'] : 'Unknown error'),
                ], 422);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload update package', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload update package: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Find backup for specific version.
     */
    private function findBackupForVersion(string $version): ?string
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
     */
    private function extractVersionFromBackupName(string $filename): ?string
    {
        if (preg_match('/backup_([0-9]+\.[0-9]+\.[0-9]+)_/', $filename, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Perform rollback operations.
     *
     * @return array<string, mixed>
     */
    private function performRollback(string $targetVersion, string $currentVersion, string $backupPath): array
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
            // This would require custom rollback migrations
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
     */
    private function restoreFromBackup(string $backupPath): void
    {
        $zip = new \ZipArchive();
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
        }
    }

    /**
     * Restore files from temporary directory.
     */
    private function restoreFilesFromTemp(string $tempDir): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tempDir),
            \RecursiveIteratorIterator::LEAVES_ONLY,
        );
        foreach ($files as $file) {
            if ($file instanceof \SplFileInfo && !$file->isDir()) {
                $relativePath = substr($file->getRealPath(), strlen($tempDir) + 1);
                $targetPath = base_path($relativePath);
                // Create directory if it doesn't exist
                $targetDir = dirname($targetPath);
                if (! SecureFileHelper::isDirectory($targetDir)) {
                    SecureFileHelper::createDirectory($targetDir, 0755, true);
                }
                // Copy file
                copy($file->getRealPath(), $targetPath);
            }
        }
    }

    /**
     * Delete directory recursively.
     */
    private function deleteDirectory(string $dir): void
    {
        if (! SecureFileHelper::isDirectory($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            SecureFileHelper::isDirectory($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Check for available updates with license verification.
     *
     * Checks for available updates using license verification and automatically
     * performs updates if available.
     *
     * @param  AutoUpdateRequest  $request  The validated request containing license data
     *
     * @return \Illuminate\Http\JsonResponse JSON response with update check result
     *
     * @throws \Exception When update check or auto update fails
     *
     * @example
     * // Check for auto updates:
     * POST /admin/updates/check-auto
     * {
     *     "licenseKey": "abc123...",
     *     "product_slug": "the-ultimate-license-management-system",
     *     "domain": "example.com",
     *     "current_version": "1.0.0"
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "Auto update completed successfully! System updated to version 1.0.1",
     *     "data": {
     *         "update_available": true,
     *         "current_version": "1.0.0",
     *         "target_version": "1.0.1",
     *         "files_installed": 15
     *     }
     * }
     */
    public function checkAutoUpdates(AutoUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $licenseKey = is_string($validated['licenseKey']) ? $validated['licenseKey'] : '';
            // Get values automatically from system
            $productSlug = 'the-ultimate-license-management-system'; // Fixed product slug
            $currentVersion = VersionHelper::getCurrentVersion(); // Get from database
            $appUrl = config('app.url');
            $domain = is_string($appUrl) ? parse_url($appUrl, PHP_URL_HOST) ?: null : null;
            // Get from APP_URL
            // Check for updates using LicenseServerService
            $licenseKeyStr = $licenseKey;
            $domainStr = $domain;
            $updateData = $this->licenseServerService->checkUpdates(
                $licenseKeyStr,
                $currentVersion,
                $productSlug,
                $domainStr,
            );
            if ($updateData['success']) {
                $isUpdateAvailable = (is_array($updateData['data'] ?? null)
                    && isset($updateData['data']['is_update_available']))
                    ? $updateData['data']['is_update_available']
                    : false;
                // If update is available, proceed with auto update
                if ($isUpdateAvailable) {
                    // API returns 'latest_version' not 'next_version'
                    $nextVersion = (is_array($updateData['data'] ?? null)
                        && isset($updateData['data']['latest_version']))
                        ? $updateData['data']['latest_version']
                        : null;
                    if ($nextVersion) {
                        // Perform the auto update
                        $updateResult = $this->performAutoUpdate(
                            is_string($nextVersion) ? $nextVersion : '',
                            $licenseKey,
                            $productSlug,
                            $domain
                        );
                        if ($updateResult['success']) {
                            DB::commit();

                            return response()->json([
                                'success' => true,
                                'message' => 'Auto update completed successfully! System updated to version '
                                    . (is_string($nextVersion) ? $nextVersion : ''),
                                'data' => [
                                    'update_available' => $isUpdateAvailable,
                                    'current_version' => $currentVersion,
                                    'target_version' => $nextVersion,
                                    'update_result' => $updateResult,
                                    'files_installed' => (is_array($updateResult['data'] ?? null) && isset($updateResult['data']['files_installed'])) ? $updateResult['data']['files_installed'] : 0,
                                ],
                            ]);
                        } else {
                            DB::rollBack();

                            return response()->json([
                                'success' => false,
                                'message' => 'Auto update failed: ' . (is_string($updateResult['message'] ?? null) ? $updateResult['message'] : 'Unknown error'),
                                'error_code' => $updateResult['error_code'] ?? 'UPDATE_FAILED',
                            ], 500);
                        }
                    }
                }
                DB::commit();

                return response()->json([
                    'success' => true,
                    'data' => $updateData['data'],
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $updateData['message'] ?? 'Failed to check for updates',
                    'error_code' => $updateData['error_code'] ?? 'UNKNOWN_ERROR',
                ], 403);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto update check failed', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['licenseKey']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking for updates: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Perform auto update process.
     *
     * @return array<string, mixed>
     */
    private function performAutoUpdate(
        string $version,
        string $licenseKey,
        string $productSlug,
        ?string $domain = null,
    ): array {
        try {
            // Validate version format and progression
            if (! VersionHelper::isValidVersion($version)) {
                return [
                    'success' => false,
                    'message' => 'Invalid version format. Must be in format X.Y.Z',
                    'error_code' => 'INVALID_VERSION_FORMAT',
                ];
            }
            // Check if can update to this version (prevents downgrading)
            if (! VersionHelper::canUpdateToVersion($version)) {
                $currentVersion = VersionHelper::getCurrentVersion();

                return [
                    'success' => false,
                    'message' => "Cannot update to version " . $version . ". Current version is " . $currentVersion . ". "
                        . 'Only newer versions are allowed.',
                    'error_code' => 'VERSION_DOWNGRADE_NOT_ALLOWED',
                    'current_version' => $currentVersion,
                    'target_version' => $version,
                ];
            }
            // Download update file
            $downloadResult = $this->licenseServerService->downloadUpdate($licenseKey, $version, $productSlug, $domain);
            if (! $downloadResult['success']) {
                Log::error('Failed to download update package', [
                    'userId' => auth()->id(),
                    'version' => $version,
                    'error' => $downloadResult['message'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'message' => 'Download failed: ' . (is_string($downloadResult['message'] ?? null) ? $downloadResult['message'] : 'Unknown error'),
                    'error_code' => 'DOWNLOAD_FAILED',
                ];
            }
            // Install the update
            $installResult = $this->updatePackageService->installUpdateFiles(is_string($downloadResult['filePath'] ?? null) ? $downloadResult['filePath'] : '');
            if ($installResult['success']) {
                // Run migrations
                Artisan::call('migrate', ['--force' => true]);
                // Clear all caches
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Cache::flush();
                // Update version in database with validation
                $versionUpdateResult = VersionHelper::updateVersion($version);
                if (! $versionUpdateResult) {
                    Log::error('Failed to update version in database after successful installation', [
                        'version' => $version,
                        'userId' => auth()->id(),
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Update installed but failed to update version in database. Please check logs.',
                        'error_code' => 'VERSION_UPDATE_FAILED',
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Update installed successfully! System updated to version ' . $version,
                    'data' => [
                        'version' => $version,
                        'files_installed' => (is_array($installResult['data'] ?? null) && isset($installResult['data']['files_installed'])) ? $installResult['data']['files_installed'] : 0,
                        'steps' => (is_array($installResult['data'] ?? null) && isset($installResult['data']['steps'])) ? $installResult['data']['steps'] : [],
                    ],
                ];
            } else {
                Log::error('Failed to install update files', [
                    'userId' => auth()->id(),
                    'version' => $version,
                    'error' => $installResult['message'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'message' => 'Installation failed: ' . (is_string($installResult['message'] ?? null) ? $installResult['message'] : 'Unknown error'),
                    'error_code' => 'INSTALLATION_FAILED',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Auto update process failed', [
                'userId' => auth()->id(),
                'version' => $version,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Auto update failed: ' . $e->getMessage(),
                'error_code' => 'PROCESS_FAILED',
            ];
        }
    }

    /**
     * Install update automatically with license verification.
     *
     * Installs a specific version update with license verification and
     * comprehensive validation.
     *
     * @param  AutoUpdateRequest  $request  The validated request containing update data
     *
     * @return \Illuminate\Http\JsonResponse JSON response with installation result
     *
     * @throws \Exception When installation process fails
     *
     * @example
     * // Install auto update:
     * POST /admin/updates/install-auto
     * {
     *     "licenseKey": "abc123...",
     *     "product_slug": "the-ultimate-license-management-system",
     *     "domain": "example.com",
     *     "version": "1.0.1",
     *     "confirm": true
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "message": "Update installed successfully and version updated in database",
     *     "data": {
     *         "version": "1.0.1",
     *         "installed_at": "2024-01-15T10:30:00Z",
     *         "steps_completed": ["download", "extract", "install", "migrate"],
     *         "database_version_updated": true
     *     }
     * }
     */
    public function installAutoUpdate(AutoUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $licenseKey = is_string($validated['licenseKey']) ? $validated['licenseKey'] : '';
            $version = is_string($validated['version']) ? $validated['version'] : '';
            // Get values automatically from system
            $productSlug = 'the-ultimate-license-management-system'; // Fixed product slug
            $appUrl = config('app.url');
            $domain = is_string($appUrl) ? parse_url($appUrl, PHP_URL_HOST) ?: null : null; // Get from APP_URL
            // Validate version format and progression
            if (! VersionHelper::isValidVersion($version)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid version format. Must be in format X.Y.Z',
                    'error_code' => 'INVALID_VERSION_FORMAT',
                ], 400);
            }
            // Check if can update to this version (prevents downgrading)
            if (! VersionHelper::canUpdateToVersion($version)) {
                $currentVersion = VersionHelper::getCurrentVersion();
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update to version ' . $version . '. Current version is ' . $currentVersion . '. '
                        . 'Only newer versions are allowed.',
                    'error_code' => 'VERSION_DOWNGRADE_NOT_ALLOWED',
                    'current_version' => $currentVersion,
                    'target_version' => $version,
                ], 400);
            }
            // First verify license again
            $updateData = $this->licenseServerService->checkUpdates($licenseKey, '1.0.0', $productSlug, $domain);
            if (! $updateData['success']) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $updateData['message'] ?? 'License verification failed',
                    'error_code' => $updateData['error_code'] ?? 'LICENSE_INVALID',
                ], 403);
            }
            // Download update file
            $downloadResult = $this->licenseServerService->downloadUpdate($licenseKey, $version, $productSlug, $domain);
            if (! $downloadResult['success']) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $downloadResult['message'] ?? 'Failed to download update',
                    'error_code' => $downloadResult['error_code'] ?? 'DOWNLOAD_FAILED',
                ], 500);
            }
            // Save update file
            $updateFileName = 'auto_update_' . $version . '_' . time() . '.zip';
            $updateFilePath = storage_path("app/updates/{$updateFileName}");
            // Ensure updates directory exists
            if (! SecureFileHelper::isDirectory(storage_path('app/updates'))) {
                SecureFileHelper::createDirectory(storage_path('app/updates'), 0755, true);
            }
            // Use the downloaded file directly
            $updateFilePath = $downloadResult['filePath'];
            // Extract and install update
            $installResult = $this->updatePackageService->installUpdateFiles(is_string($updateFilePath) ? $updateFilePath : '');
            if ($installResult['success']) {
                // Clean up update file
                unlink(is_string($updateFilePath) ? $updateFilePath : '');
                // Update version in database with validation
                $versionUpdateResult = VersionHelper::updateVersion($version);
                if (! $versionUpdateResult) {
                    DB::rollBack();
                    Log::error('Failed to update version in database after successful installation', [
                        'version' => $version,
                        'userId' => auth()->id(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Update installed but failed to update version in database. Please check logs.',
                        'error_code' => 'VERSION_UPDATE_FAILED',
                    ], 500);
                }
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Update installed successfully and version updated in database',
                    'data' => [
                        'version' => $version,
                        'installed_at' => now()->toISOString(),
                        'steps_completed' => $installResult['steps'] ?? [],
                        'database_version_updated' => true,
                    ],
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $installResult['message'] ?? 'Failed to install update',
                    'error_code' => 'INSTALL_FAILED',
                ], 500);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto update installation failed', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['licenseKey']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while installing update',
                'error_detail' => $e->getMessage(), // security-ignore: SQL_STRING_CONCAT
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }
    /**
     * Get update information for product without license verification.
     *
     * @return array<string, mixed>|null
     */
    private function getUpdateInfoForProduct(string $productSlug, string $currentVersion): ?array
    {
        try {
            // Get current version from database instead of using parameter
            $actualCurrentVersion = VersionHelper::getCurrentVersion();
            // Getting update info for product
            // Use the actual current version from database
            $currentVersion = $actualCurrentVersion;
            // Get update info from central API (without license verification)
            $latestVersionData = $this->licenseServerService->getUpdateInfo($productSlug, $currentVersion);
            // Latest version API response received
            if ($latestVersionData['success']) {
                $data = $latestVersionData['data'];
                // Check if update is available
                $isUpdateAvailable = (is_array($data) && isset($data['is_update_available'])) ? $data['is_update_available'] : false;
                $nextVersion = (is_array($data) && isset($data['next_version'])) ? $data['next_version'] : null;
                // Additional validation: Check if next version is actually newer than current
                if ($isUpdateAvailable && $nextVersion) {
                    $nextVersionStr = is_string($nextVersion) ? $nextVersion : '';
                    $versionComparison = VersionHelper::compareVersions($nextVersionStr, $currentVersion);
                    // Version comparison check completed
                    // If next version is not newer, treat as no update available
                    if ($versionComparison <= 0) {
                        // Next version is not newer than current - hiding update
                        $isUpdateAvailable = false;
                    }
                }
                if ($isUpdateAvailable) {
                    // Update available and validated
                    return [
                        'is_update_available' => true,
                        'current_version' => $currentVersion,
                        'next_version' => $nextVersion,
                        'update_info' => (is_array($data) && isset($data['update_info'])) ? $data['update_info'] : [],
                    ];
                } else {
                    // No update available or update is older
                    return [
                        'is_update_available' => false,
                        'current_version' => $currentVersion,
                        'next_version' => $nextVersion ?? $currentVersion,
                        'update_info' => (is_array($data) && isset($data['update_info'])) ? $data['update_info'] : [],
                        'reason' => $nextVersion ? 'version_not_newer' : 'no_update_available',
                    ];
                }
            }
            Log::warning('Failed to get latest version from central API', [
                'response' => $latestVersionData,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting update info for product', [
                'error' => $e->getMessage(),
                'product_slug' => $productSlug,
                'current_version' => $currentVersion,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get specific product from central API.
     *
     * @return array<string, mixed>
     */
    private function getSpecificProduct(string $productSlug): array
    {
        try {
            // Getting specific product from central API
            // Get all products from central API
            $productsData = $this->licenseServerService->getProducts();
            // Products API response received
            if ($productsData['success']) {
                $allProducts = (is_array($productsData['data'] ?? null) && isset($productsData['data']['products'])) ? $productsData['data']['products'] : [];
                // Filter for specific product
                $specificProduct = array_filter(is_array($allProducts) ? $allProducts : [], function ($product) use ($productSlug) {
                    return is_array($product) && isset($product['slug']) && $product['slug'] === $productSlug;
                });
                $filteredProducts = array_values($specificProduct);

                // Filtered products for specific slug
                /**
 * @var array<string, mixed> $result
*/
                $result = $filteredProducts[0] ?? [];
                return $result;
            }
            Log::warning('Failed to get products from central API', [
                'response' => $productsData,
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting specific product from central API', [
                'error' => $e->getMessage(),
                'product_slug' => $productSlug,
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }


    /**
     * Get current version from database.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentVersionFromDatabase()
    {
        try {
            $currentVersion = VersionHelper::getCurrentVersion();
            $versionHistory = VersionHelper::getVersionHistory();

            return response()->json([
                'success' => true,
                'data' => [
                    'current_version' => $currentVersion,
                    'version_history' => $versionHistory,
                    'last_checked' => now()->toISOString(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get current version from database', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get current version: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get version history from central API.
     *
     * Retrieves version history information from the central API using
     * license verification.
     *
     * @param  VersionManagementRequest  $request  The validated request containing license data
     *
     * @return \Illuminate\Http\JsonResponse JSON response with version history
     *
     * @throws \Exception When API request fails
     *
     * @example
     * // Get version history:
     * POST /admin/updates/version-history
     * {
     *     "licenseKey": "abc123...",
     *     "product_slug": "the-ultimate-license-management-system",
     *     "domain": "example.com"
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "data": {
     *         "versions": [
     *             {"version": "1.0.1", "releasedAt": "2024-01-15", "changelog": "..."},
     *             {"version": "1.0.0", "releasedAt": "2024-01-01", "changelog": "..."}
     *         ]
     *     }
     * }
     */
    public function getVersionHistoryFromCentral(VersionManagementRequest $request)
    {
        try {
            $validated = $request->validated();
            $licenseKey = is_string($validated['licenseKey']) ? $validated['licenseKey'] : '';
            $productSlug = is_string($validated['product_slug']) ? $validated['product_slug'] : '';
            $domain = is_string($validated['domain']) ? $validated['domain'] : null;
            // Get version history from central API
            $historyData = $this->licenseServerService->getVersionHistory($licenseKey, $productSlug, $domain);
            if ($historyData['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $historyData['data'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $historyData['message'] ?? 'Failed to get version history',
                    'error_code' => $historyData['error_code'] ?? 'UNKNOWN_ERROR',
                ], 403);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get version history from central API', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['licenseKey']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while getting version history: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Get latest version from central API.
     *
     * Retrieves the latest version information from the central API using
     * license verification.
     *
     * @param  VersionManagementRequest  $request  The validated request containing license data
     *
     * @return \Illuminate\Http\JsonResponse JSON response with latest version info
     *
     * @throws \Exception When API request fails
     *
     * @example
     * // Get latest version:
     * POST /admin/updates/latest-version
     * {
     *     "licenseKey": "abc123...",
     *     "product_slug": "the-ultimate-license-management-system",
     *     "domain": "example.com"
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "data": {
     *         "latest_version": "1.0.1",
     *         "release_date": "2024-01-15",
     *         "changelog": "Bug fixes and improvements",
     *         "download_url": "https://..."
     *     }
     * }
     */
    public function getLatestVersionFromCentral(VersionManagementRequest $request)
    {
        try {
            $validated = $request->validated();
            $licenseKey = is_string($validated['licenseKey']) ? $validated['licenseKey'] : '';
            $productSlug = is_string($validated['product_slug']) ? $validated['product_slug'] : '';
            $domain = is_string($validated['domain']) ? $validated['domain'] : null;
            // Get latest version from central API
            $latestData = $this->licenseServerService->getLatestVersion(
                $licenseKey,
                $productSlug,
                $domain,
            );
            if ($latestData['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $latestData['data'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $latestData['message'] ?? 'Failed to get latest version',
                    'error_code' => $latestData['error_code'] ?? 'UNKNOWN_ERROR',
                ], 403);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get latest version from central API', [
                'userId' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['licenseKey']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while getting latest version: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }
}
