<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Helpers\VersionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AutoUpdateRequest;
use App\Http\Requests\Admin\SystemUpdateRequest;
use App\Services\LicenseServerService;
use App\Services\UpdatePackageService;
use App\Services\UpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Simplified Update Controller.
 */
class UpdateController extends Controller
{
    public function __construct(
        private LicenseServerService $licenseServerService,
        private UpdatePackageService $updatePackageService,
        private UpdateService $updateService,
    ) {
    }

    /**
     * Show update management page.
     */
    public function index()
    {
        try {
            $versionStatus = VersionHelper::getVersionStatus();
            $versionInfo = VersionHelper::getVersionInfo();
            $products = $this->getProducts();
            $updateInfo = $this->getUpdateInfo();

            return view('admin.updates.index', [
                'versionStatus' => $versionStatus,
                'versionInfo' => $versionInfo,
                'products' => $products,
                'updateInfo' => $updateInfo,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load update page', ['error' => $e->getMessage()]);

            return view('admin.updates.index', [
                'versionStatus' => null,
                'versionInfo' => null,
                'products' => [],
                'updateInfo' => null,
                'error' => 'Failed to load update information.',
            ]);
        }
    }

    /**
     * Show update confirmation.
     */
    public function confirmUpdate(Request $request)
    {
        $version = $request->query('version');

        if (! $version || ! VersionHelper::isValidVersion($version)) {
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
     * Check for updates.
     */
    public function checkUpdates()
    {
        try {
            $versionStatus = VersionHelper::getVersionStatus();

            if ($versionStatus['is_update_available']) {
                $message = "Update available! Current: {$versionStatus['current_version']}, Latest: {$versionStatus['latest_version']}";

                return redirect()->back()->with('success', $message);
            }

            $message = "System is up to date. Current version: {$versionStatus['current_version']}";

            return redirect()->back()->with('info', $message);
        } catch (\Exception $e) {
            Log::error('Update check failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to check for updates: ' . $e->getMessage());
        }
    }

    /**
     * Perform system update.
     */
    public function update(SystemUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $targetVersion = $validated['version'];
            $currentVersion = VersionHelper::getCurrentVersion();

            $validationResult = $this->updateService->validateUpdateRequest($targetVersion, $currentVersion);
            if (! $validationResult['valid']) {
                DB::rollBack();

                return redirect()->back()->with('error', $validationResult['error']);
            }

            $this->updateService->performUpdate($targetVersion, $currentVersion);
            VersionHelper::updateVersion($targetVersion);
            DB::commit();

            return redirect()->route('admin.updates.index')->with(
                'success',
                'System updated successfully from ' . $currentVersion . ' to ' . $targetVersion,
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System update failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    /**
     * Rollback to previous version.
     */
    public function rollback(SystemUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $targetVersion = $validated['version'];
            $currentVersion = VersionHelper::getCurrentVersion();

            $validationResult = $this->updateService->validateRollbackRequest($targetVersion, $currentVersion);
            if (! $validationResult['valid']) {
                DB::rollBack();

                return redirect()->back()->with('error', $validationResult['error']);
            }

            $backupPath = $validationResult['backup_path'];
            $this->updateService->performRollback($targetVersion, $currentVersion, $backupPath);
            DB::commit();

            Log::warning('System rollback performed', [
                'from_version' => $currentVersion,
                'to_version' => $targetVersion,
            ]);

            return redirect()->route('admin.updates.index')->with(
                'success',
                'System rolled back successfully from ' . $currentVersion . ' to ' . $targetVersion,
            );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('System rollback failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }

    /**
     * Get version information.
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
            Log::error('Failed to get version info', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get version information: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of available backups.
     */
    public function getBackups()
    {
        try {
            $backupDir = storage_path('app/backups');
            $backups = [];

            if (is_dir($backupDir)) {
                $files = glob($backupDir . '/backup_*.zip');
                if ($files !== false) {
                    foreach ($files as $file) {
                        $backups[] = [
                            'filename' => basename($file),
                            'path' => $file,
                            'size' => filesize($file),
                            'created_at' => date('Y-m-d H:i:s', filemtime($file)),
                            'version' => $this->extractVersionFromBackupName(basename($file)),
                        ];
                    }
                }

                usort($backups, function ($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
            }

            return response()->json([
                'success' => true,
                'data' => $backups,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get backups', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get backups: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check for auto updates.
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
                        $domain,
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
            Log::error('Auto update check failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'An error occurred while checking for updates: ' . $e->getMessage());
        }
    }

    /**
     * Install auto update.
     */
    public function installAutoUpdate(AutoUpdateRequest $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validated();
            $licenseKey = $validated['license_key'];
            $version = $validated['version'];
            $domain = parse_url(config('app.url'), PHP_URL_HOST);

            if (! VersionHelper::isValidVersion($version)) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid version format. Must be in format X.Y.Z',
                    'error_code' => 'INVALID_VERSION_FORMAT',
                ], 400);
            }

            if (! VersionHelper::canUpdateToVersion($version)) {
                $currentVersion = VersionHelper::getCurrentVersion();
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update to version ' . $version . '. Current version is ' . $currentVersion,
                    'error_code' => 'VERSION_DOWNGRADE_NOT_ALLOWED',
                ], 400);
            }

            $updateData = $this->licenseServerService->checkUpdates(
                $licenseKey,
                '1.0.0',
                'the-ultimate-license-management-system',
                $domain,
            );

            if (! $updateData['success']) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $updateData['message'] ?? 'License verification failed',
                    'error_code' => $updateData['error_code'] ?? 'LICENSE_INVALID',
                ], 403);
            }

            $downloadResult = $this->licenseServerService->downloadUpdate(
                $licenseKey,
                $version,
                'the-ultimate-license-management-system',
                $domain,
            );

            if (! $downloadResult['success']) {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $downloadResult['message'] ?? 'Failed to download update',
                    'error_code' => $downloadResult['error_code'] ?? 'DOWNLOAD_FAILED',
                ], 500);
            }

            $updateFilePath = $downloadResult['file_path'];
            $installResult = $this->updatePackageService->installUpdateFiles($updateFilePath);

            if ($installResult['success']) {
                unlink($updateFilePath);

                if (! VersionHelper::updateVersion($version)) {
                    DB::rollBack();
                    Log::error('Failed to update version in database after successful installation');

                    return response()->json([
                        'success' => false,
                        'message' => 'Update installed but failed to update version in database.',
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
            Log::error('Auto update installation failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'An error occurred while installing update: ' . $e->getMessage());
        }
    }

    /**
     * Get current version from database.
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
            Log::error('Failed to get current version from database', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get current version: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Perform auto update.
     */
    private function performAutoUpdate(string $version, string $licenseKey, string $productSlug, ?string $domain = null): array
    {
        try {
            if (! VersionHelper::isValidVersion($version)) {
                return [
                    'success' => false,
                    'message' => 'Invalid version format. Must be in format X.Y.Z',
                    'error_code' => 'INVALID_VERSION_FORMAT',
                ];
            }

            if (! VersionHelper::canUpdateToVersion($version)) {
                $currentVersion = VersionHelper::getCurrentVersion();

                return [
                    'success' => false,
                    'message' => 'Cannot update to version ' . $version . '. Current version is ' . $currentVersion,
                    'error_code' => 'VERSION_DOWNGRADE_NOT_ALLOWED',
                ];
            }

            $downloadResult = $this->licenseServerService->downloadUpdate($licenseKey, $version, $productSlug, $domain);
            if (! $downloadResult['success']) {
                Log::error('Failed to download update package', [
                    'version' => $version,
                    'error' => $downloadResult['message'] ?? 'Unknown error',
                ]);

                return [
                    'success' => false,
                    'message' => 'Download failed: ' . ($downloadResult['message'] ?? 'Unknown error'),
                    'error_code' => 'DOWNLOAD_FAILED',
                ];
            }

            $installResult = $this->updatePackageService->installUpdateFiles($downloadResult['file_path'] ?? '');
            if ($installResult['success']) {
                $this->clearCaches();
                $this->runMigrations();

                if (! VersionHelper::updateVersion($version)) {
                    Log::error('Failed to update version in database', ['version' => $version]);

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
                'version' => $version,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Auto update failed: ' . $e->getMessage(),
                'error_code' => 'PROCESS_FAILED',
            ];
        }
    }

    /**
     * Get products.
     */
    private function getProducts(): array
    {
        try {
            $productsData = $this->licenseServerService->getProducts();
            if ($productsData['success']) {
                $allProducts = $productsData['data']['products'] ?? [];
                $specificProduct = array_filter($allProducts, fn ($product) => $product['slug'] === 'the-ultimate-license-management-system');

                return array_values($specificProduct)[0] ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('Error getting products', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get update info.
     */
    private function getUpdateInfo(): ?array
    {
        try {
            $currentVersion = VersionHelper::getCurrentVersion();
            $latestVersionData = $this->licenseServerService->getUpdateInfo('the-ultimate-license-management-system', $currentVersion);

            if ($latestVersionData['success']) {
                $data = $latestVersionData['data'];
                $isUpdateAvailable = $data['is_update_available'] ?? false;
                $nextVersion = $data['next_version'] ?? null;

                if ($isUpdateAvailable && $nextVersion) {
                    $versionComparison = VersionHelper::compareVersions($nextVersion, $currentVersion);
                    if ($versionComparison <= 0) {
                        $isUpdateAvailable = false;
                    }
                }

                return [
                    'is_update_available' => $isUpdateAvailable,
                    'current_version' => $currentVersion,
                    'next_version' => $nextVersion,
                    'update_info' => $data['update_info'] ?? [],
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error getting update info', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Extract version from backup name.
     */
    private function extractVersionFromBackupName(string $filename): string
    {
        if (preg_match('/backup_(\d+\.\d+\.\d+)_/', $filename, $matches)) {
            return $matches[1];
        }

        return 'unknown';
    }

    /**
     * Clear all caches.
     */
    private function clearCaches(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Cache::flush();
    }

    /**
     * Run migrations.
     */
    private function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }
}
