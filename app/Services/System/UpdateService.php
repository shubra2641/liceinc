<?php

declare(strict_types=1);

namespace App\Services\System;

use App\Helpers\VersionHelper;
use Illuminate\Support\Facades\Log;

/**
 * Update Service - Handles system update operations.
 */
class UpdateService
{
    public function __construct(
        private UpdateBackupService $backupService,
        private UpdateCacheService $cacheService,
        private UpdateValidationService $validationService,
        private UpdateProcessService $processService
    ) {
    }

    /**
     * Create system backup before update.
     */
    public function createSystemBackup(string $version): string
    {
        return $this->backupService->createSystemBackup($version);
    }

    /**
     * Perform the actual update steps.
     */
    public function performUpdate(string $targetVersion, string $currentVersion): array
    {
        try {
            Log::info('Starting update process', [
                'target_version' => $targetVersion,
                'current_version' => $currentVersion,
            ]);

            // Validate update request
            $validation = $this->validateUpdateRequest($targetVersion, $currentVersion);
            if (!$validation['valid']) {
                throw new \Exception($validation['error']);
            }

            // Create backup before update
            $backupPath = $this->backupService->createSystemBackup($currentVersion);
            Log::info('Backup created', [
                'backup_path' => $backupPath,
            ]);

            // Execute update process
            $result = $this->processService->executeUpdate($targetVersion);

            // Update system information
            $this->updateSystemInfo($targetVersion, $currentVersion);

            Log::info('Update completed successfully', [
                'target_version' => $targetVersion,
                'current_version' => $currentVersion,
            ]);

            return [
                'success' => true,
                'message' => 'Update completed successfully',
                'backup_path' => $backupPath,
                'steps' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Update failed', [
                'error' => $e->getMessage(),
                'target_version' => $targetVersion,
                'current_version' => $currentVersion,
            ]);
            throw $e;
        }
    }

    /**
     * Update system information after successful update.
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
     */
    public function findBackupForVersion(string $version): ?string
    {
        return $this->backupService->findBackupForVersion($version);
    }

    /**
     * Extract version from backup filename.
     */
    public function extractVersionFromBackupName(string $filename): ?string
    {
        return $this->backupService->extractVersionFromBackupName($filename);
    }

    /**
     * Perform rollback operations.
     */
    public function performRollback(string $targetVersion, string $currentVersion, string $backupPath): array
    {
        try {
            Log::info('Starting rollback process', [
                'target_version' => $targetVersion,
                'current_version' => $currentVersion,
                'backup_path' => $backupPath,
            ]);

            // Validate rollback request
            $validation = $this->validateRollbackRequest($targetVersion, $currentVersion);
            if (!$validation['valid']) {
                throw new \Exception($validation['error']);
            }

            // Execute rollback process
            $result = $this->processService->rollbackUpdate($backupPath);

            // Update version in database
            VersionHelper::updateVersion($targetVersion);

            Log::info('Rollback completed successfully', [
                'target_version' => $targetVersion,
                'current_version' => $currentVersion,
            ]);

            return [
                'success' => true,
                'message' => 'Rollback completed successfully',
                'steps' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('Rollback failed', [
                'error' => $e->getMessage(),
                'target_version' => $targetVersion,
                'current_version' => $currentVersion,
            ]);
            throw $e;
        }
    }

    /**
     * Validate update request data.
     */
    public function validateUpdateRequest(string $targetVersion, string $currentVersion): array
    {
        // Validate version format
        if (!VersionHelper::isValidVersion($targetVersion)) {
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
     */
    public function validateRollbackRequest(string $targetVersion, string $currentVersion): array
    {
        // Validate version format
        if (!VersionHelper::isValidVersion($targetVersion)) {
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
        if (!$backupPath) {
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

    /**
     * Get update status.
     */
    public function getUpdateStatus(): array
    {
        return $this->processService->getUpdateStatus();
    }

    /**
     * Clean up update files.
     */
    public function cleanupUpdateFiles(): array
    {
        return $this->processService->cleanupUpdateFiles();
    }

    /**
     * Check if update is available.
     */
    public function isUpdateAvailable(): bool
    {
        try {
            $currentVersion = VersionHelper::getCurrentVersion();
            $latestVersion = VersionHelper::getLatestVersion();

            return VersionHelper::compareVersions($latestVersion, $currentVersion) > 0;
        } catch (\Exception $e) {
            Log::error('Failed to check update availability', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get available updates.
     */
    public function getAvailableUpdates(): array
    {
        try {
            $currentVersion = VersionHelper::getCurrentVersion();
            $allVersions = VersionHelper::getAllVersions();

            $availableUpdates = array_filter($allVersions, function ($version) use ($currentVersion) {
                return VersionHelper::compareVersions($version, $currentVersion) > 0;
            });

            return array_values($availableUpdates);
        } catch (\Exception $e) {
            Log::error('Failed to get available updates', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get update history.
     */
    public function getUpdateHistory(): array
    {
        try {
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                return [];
            }

            $backups = glob($backupDir . '/backup_*.zip');
            $history = [];

            foreach ($backups as $backup) {
                $filename = basename($backup);
                $version = $this->extractVersionFromBackupName($filename);
                if ($version) {
                    $history[] = [
                        'version' => $version,
                        'backup_path' => $backup,
                        'created_at' => filemtime($backup),
                    ];
                }
            }

            // Sort by creation time (newest first)
            usort($history, function ($a, $b) {
                return $b['created_at'] <=> $a['created_at'];
            });

            return $history;
        } catch (\Exception $e) {
            Log::error('Failed to get update history', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }
}
