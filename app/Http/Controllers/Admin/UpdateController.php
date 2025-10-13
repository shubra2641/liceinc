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
use App\Services\UpdateService;
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

    protected UpdateService $updateService;

    public function __construct(
        LicenseServerService $licenseServerService, 
        UpdatePackageService $updatePackageService, 
        UpdateService $updateService)
    {
        $this->licenseServerService = $licenseServerService;
        $this->updatePackageService = $updatePackageService;
        $this->updateService = $updateService;
    }

    /**
     * Show update confirmation page.
     *
     * Displays a confirmation page before performing system updates,
     * showing version details and requiring explicit user confirmation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function confirmUpdate(Request $request)
    {
        $version = $request->query('version');

        if (!$version || !VersionHelper::isValidVersion($version)) {
            return redirect()->route('admin.updates.index')
                ->with('error', 'Invalid version specified.');
        }

        $currentVersion = VersionHelper::getCurrentVersion();

        if (VersionHelper::compareVersions($version, $currentVersion) <= 0) {
            return redirect()->route('admin.updates.index')
                ->with('error', 'Target version must be newer than current version.');
        }

        $versionInfo = VersionHelper::getVersionInfo($version);

        if (empty($versionInfo)) {
            return redirect()->route('admin.updates.index')
                ->with('error', 'Version information not found.');
        }

        return view('admin.updates.confirm', [
            'version' => $version,
            'currentVersion' => $currentVersion,
            'versionInfo' => $versionInfo,
        ]);
    }
    
    /**
     * Display the update management page.
     *
     * Shows current version, available updates, and update options with
     * comprehensive version information and update status.
     *
     * @return \Illuminate\View\View The update management view
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
                'user_id' => auth()->id(),
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
     * Checks for available system updates and returns appropriate flash messages
     * to inform the user about update availability or current system status.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkUpdates()
    {
        try {
            $versionStatus = VersionHelper::getVersionStatus();

            if ($versionStatus['is_update_available']) {
                $message = "Update available! Current version: {$versionStatus['current_version']}, "
                    . "Latest version: {$versionStatus['latest_version']}";

                return redirect()->back()->with('success', $message);
            }

            $message = "System is up to date. Current version: {$versionStatus['current_version']}";

            return redirect()->back()->with('info', $message);
        } catch (\Exception $e) {
            Log::error('Update check failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error',
                'Failed to check for updates: ' . $e->getMessage()
            );
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
     * @return \Illuminate\Http\RedirectResponse Redirect response with flash message
     *
     * @throws \Exception When update process fails
     */
    public function update(SystemUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $targetVersion = is_string($validated['version']) ? $validated['version'] : '';
            $currentVersion = VersionHelper::getCurrentVersion();

            // Validate update request using UpdateService
            $validationResult = $this->updateService->validateUpdateRequest($targetVersion, $currentVersion);
            if (! $validationResult['valid']) {
                DB::rollBack();

                return redirect()->back()->with('error', $validationResult['error']);
            }

            // Perform update steps using UpdateService
            $updateSteps = $this->updateService->performUpdate($targetVersion, $currentVersion);

            // Update version in database
            VersionHelper::updateVersion($targetVersion);
            DB::commit();

            return redirect()->route('admin.updates.index')->with('success',
                'System updated successfully from ' . $currentVersion
                . ' to ' . $targetVersion
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System update failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['confirm']),
            ]);

            return redirect()->back()->with('error',
                'Update failed: ' . $e->getMessage()
            );
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
                'user_id' => auth()->id(),
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
     * @return \Illuminate\Http\RedirectResponse Redirect response with flash message
     *
     * @throws \Exception When rollback process fails
     */
    public function rollback(SystemUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $targetVersion = is_string($validated['version']) ? $validated['version'] : '';
            $currentVersion = VersionHelper::getCurrentVersion();

            // Validate rollback request using UpdateService
            $validationResult = $this->updateService->validateRollbackRequest($targetVersionStr, $currentVersionStr);
            if (! $validationResult['valid']) {
                DB::rollBack();

                return redirect()->back()->with('error', $validationResult['error']);
            }

            $backupPath = $validationResult['backup_path'];

            // Perform rollback using UpdateService
            $rollbackSteps = $this->updateService->performRollback($targetVersion, $currentVersion, $backupPath);
            DB::commit();
            Log::warning('System rollback performed', [
                'user_id' => auth()->id(),
                'from_version' => $currentVersion,
                'to_version' => $targetVersion,
                'backup_used' => basename($backupPath),
            ]);

            return redirect()->route('admin.updates.index')->with('success',
                'System rolled back successfully from ' . $currentVersion . ' to ' . $targetVersion
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System rollback failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['confirm']),
            ]);

            return redirect()->back()->with('error',
                'Rollback failed: ' . $e->getMessage()
            );
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
                            'created_at' => date('Y-m-d H:i:s', (int)filemtime($file)),
                            'version' => $this->updateService->extractVersionFromBackupName(basename($file)),
                        ];
                    }
                }
                // Sort by creation date (newest first)
                usort($backups, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
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
     * @return \Illuminate\Http\RedirectResponse Redirect response with flash message
     *
     * @throws \Exception When upload or processing fails
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

                return redirect()->route('admin.updates.index')->with('success',
                    'Update package uploaded and processed successfully.'
                );
            } else {
                DB::rollBack();

                return redirect()->back()->with('error',
                    'Update package uploaded but processing failed: ' . (
                        is_string($processResult['message'] ?? null)
                            ? $processResult['message']
                            : 'Unknown error'
                    )
                );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to upload update package', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error',
                'Failed to upload update package: ' . $e->getMessage()
            );
        }
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
     *     "license_key": "abc123...",
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
            $licenseKey = $validated['license_key'];
            $currentVersion = VersionHelper::getCurrentVersion();
            $domain = parse_url(config('app.url'), PHP_URL_HOST);

            $updateData = $this->licenseServerService->checkUpdates(
                $licenseKey,
                $currentVersion,
                'the-ultimate-license-management-system',
                $domain,
            );

            if ($updateData['success']) {
                $data = $updateData['data'] ?? [];
                $isUpdateAvailable = $data['is_update_available'] ?? false;
                $nextVersion = $data['latest_version'] ?? null;
                if ($nextVersion) {
                    $updateResult = $this->performAutoUpdate(
                        $nextVersion,
                        $licenseKey,
                        'the-ultimate-license-management-system',
                        $domain
                    );

                    if ($updateResult['success']) {
                        DB::commit();
                        $message = 'Auto update completed successfully! System updated to version ' . $nextVersion;
                        return redirect()->route('admin.updates.index')->with('success', $message);
                    } else {
                        DB::rollBack();
                        return redirect()->back()
                            ->with('error', 'Auto update failed: ' . ($updateResult['message'] ?? 'Unknown error'));
                    }
                }

                DB::commit();
                return redirect()->route('admin.updates.index')
                    ->with('info', 'No updates available. System is up to date.');
            } else {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', $updateData['message'] ?? 'Failed to check for updates');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto update check failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['license_key']),
            ]);

            return redirect()->back()->with('error',
                'An error occurred while checking for updates: ' . $e->getMessage()
            );
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
                    'message' => "Cannot update to version " . $version
                        . ". Current version is " . $currentVersion . ". "
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
                    'user_id' => auth()->id(),
                    'version' => $version,
                    'error' => $downloadResult['message'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'message' => 'Download failed: ' . (
                        is_string($downloadResult['message'] ?? null)
                            ? $downloadResult['message']
                            : 'Unknown error'
                    ),
                    'error_code' => 'DOWNLOAD_FAILED',
                ];
            }
            // Install the update
            $installResult = $this->updatePackageService->installUpdateFiles(
                is_string($downloadResult['file_path'] ?? null)
                    ? $downloadResult['file_path']
                    : ''
            );
            if ($installResult['success']) {
                // Run migrations and clear caches
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');
                Artisan::call('view:clear');
                Cache::flush();

                // Update version in database
                if (! VersionHelper::updateVersion($version)) {
                    Log::error('Failed to update version in database', [
                        'version' => $version,
                        'user_id' => auth()->id(),
                    ]);

                    return [
                        'success' => false,
                        'message' => 'Update installed but failed to update version in database.',
                        'error_code' => 'VERSION_UPDATE_FAILED',
                    ];
                }

                return [
                    'success' => true,
                    'message' => 'Update installed successfully! System updated to version ' . $version,
                    'data' => [
                        'version' => $version,
                        'files_installed' => $installResult['data']['files_installed'] ?? 0,
                        'steps' => $installResult['data']['steps'] ?? [],
                    ],
                ];
            } else {
                Log::error('Failed to install update files', [
                    'user_id' => auth()->id(),
                    'version' => $version,
                    'error' => $installResult['message'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'message' => 'Installation failed: ' . ($installResult['message'] ?? 'Unknown error'),
                    'error_code' => 'INSTALL_FAILED',
                ];
            }
        } catch (\Exception $e) {
            Log::error('Auto update process failed', [
                'user_id' => auth()->id(),
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
     *     "license_key": "abc123...",
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
            $licenseKey = $validated['license_key'];
            $version = $validated['version'];
            $domain = parse_url(config('app.url'), PHP_URL_HOST);

            // Validate version
            if (! VersionHelper::isValidVersion($version)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid version format. Must be in format X.Y.Z',
                    'error_code' => 'INVALID_VERSION_FORMAT',
                ], 400);
            }

            // Check if can update to this version
            if (! VersionHelper::canUpdateToVersion($version)) {
                $currentVersion = VersionHelper::getCurrentVersion();
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update to version ' . $version . '. Current version is ' . $currentVersion,
                    'error_code' => 'VERSION_DOWNGRADE_NOT_ALLOWED',
                    'current_version' => $currentVersion,
                    'target_version' => $version,
                ], 400);
            }

            // Verify license
            $updateData = $this->licenseServerService->checkUpdates(
                $licenseKey, '1.0.0', 'the-ultimate-license-management-system', $domain
            );
        if (! $updateData['success']) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $updateData['message'] ?? 'License verification failed',
                    'error_code' => $updateData['error_code'] ?? 'LICENSE_INVALID',
                ], 403);
            }

            // Download update file
            $downloadResult = $this->licenseServerService->downloadUpdate(
                $licenseKey, $version, 'the-ultimate-license-management-system', $domain
            );
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
            $updateFilePath = $downloadResult['file_path'];
            // Extract and install update
            $installResult = $this->updatePackageService->installUpdateFiles(
                is_string($updateFilePath) ? $updateFilePath : ''
            );
            if ($installResult['success']) {
                // Clean up update file
                unlink(is_string($updateFilePath) ? $updateFilePath : '');
                // Update version in database with validation
                $versionUpdateResult = VersionHelper::updateVersion($version);
                if (! $versionUpdateResult) {
                    DB::rollBack();
                    Log::error('Failed to update version in database after successful installation', [
                        'version' => $version,
                        'user_id' => auth()->id(),
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Update installed but failed to update version '
                            . 'in database. Please check logs.',
                        'error_code' => 'VERSION_UPDATE_FAILED',
                    ], 500);
                }
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Update installed successfully '
                        . 'and version updated in database',
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
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['license_key']),
            ]);

            return redirect()->back()->with('error',
                'An error occurred while installing update: ' . $e->getMessage()
            );
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
            $currentVersion = VersionHelper::getCurrentVersion();
            $latestVersionData = $this->licenseServerService->getUpdateInfo($productSlug, $currentVersion);

        if ($latestVersionData['success']) {
                $data = $latestVersionData['data'];
                $isUpdateAvailable = $data['is_update_available'] ?? false;
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

        if ($productsData['success']) {
                $allProducts = $productsData['data']['products'] ?? [];
                $specificProduct = array_filter($allProducts, fn($product) => $product['slug'] === $productSlug);
                return array_values($specificProduct)[0] ?? [];
            }

            Log::warning('Failed to get products from central API', ['response' => $productsData]);
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
                'user_id' => auth()->id(),
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
     *     "license_key": "abc123...",
     *     "product_slug": "the-ultimate-license-management-system",
     *     "domain": "example.com"
     * }
     *
     * // Response:
     * {
     *     "success": true,
     *     "data": {
     *         "versions": [
     *             {"version": "1.0.1", "released_at": "2024-01-15", "changelog": "..."},
     *             {"version": "1.0.0", "released_at": "2024-01-01", "changelog": "..."}
     *         ]
     *     }
     * }
     */
    public function getVersionHistoryFromCentral(VersionManagementRequest $request)
    {
        try {
            $validated = $request->validated();
            $licenseKey = is_string($validated['license_key']) ? $validated['license_key'] : '';
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
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['license_key']),
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
     *     "license_key": "abc123...",
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
            $licenseKey = $validated['license_key'];
            $productSlug = $validated['product_slug'];
            $domain = $validated['domain'] ?? null;

            $latestData = $this->licenseServerService->getLatestVersion($licenseKey, $productSlug, $domain);

        if ($latestData['success']) {
                return response()->json(['success' => true, 'data' => $latestData['data']]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $latestData['message'] ?? 'Failed to get latest version',
                    'error_code' => $latestData['error_code'] ?? 'UNKNOWN_ERROR',
                ], 403);
            }
        } catch (\Exception $e) {
            Log::error('Failed to get latest version from central API', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['license_key']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while getting latest version: ' . $e->getMessage(),
                'error_code' => 'SERVER_ERROR',
            ], 500);
        }
    }
}