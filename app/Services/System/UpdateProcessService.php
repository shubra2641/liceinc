<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Update Process Service - Handles the update process.
 */
class UpdateProcessService
{
    public function __construct(
        private UpdateBackupService $backupService,
        private UpdateCacheService $cacheService,
        private UpdateValidationService $validationService
    ) {
    }

    /**
     * Execute update process.
     */
    public function executeUpdate(string $packagePath): array
    {
        try {
            Log::info('Starting update process', [
                'package_path' => $packagePath,
            ]);

            // Validate system requirements
            $validation = $this->validationService->validateUpdateProcess();
            if (!$validation['valid']) {
                throw new \Exception('System validation failed');
            }

            // Create backup
            $backupPath = $this->backupService->createSystemBackup();
            Log::info('Backup created', [
                'backup_path' => $backupPath,
            ]);

            // Put application in maintenance mode
            $this->enableMaintenanceMode();

            try {
                // Clear caches
                $this->cacheService->clearAllCaches();

                // Extract update package
                $extractPath = $this->extractUpdatePackage($packagePath);

                // Apply updates
                $this->applyUpdates($extractPath);

                // Run migrations
                $this->runMigrations();

                // Clear caches again
                $this->cacheService->clearAllCaches();

                // Optimize application
                $this->cacheService->optimizeApplication();

                // Disable maintenance mode
                $this->disableMaintenanceMode();

                Log::info('Update process completed successfully', [
                    'package_path' => $packagePath,
                    'backup_path' => $backupPath,
                ]);

                return [
                    'success' => true,
                    'message' => 'Update completed successfully',
                    'backup_path' => $backupPath,
                ];
            } catch (\Exception $e) {
                // Restore from backup on failure
                $this->backupService->restoreFromBackup($backupPath);
                $this->disableMaintenanceMode();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Update process failed', [
                'error' => $e->getMessage(),
                'package_path' => $packagePath,
            ]);
            throw $e;
        }
    }

    /**
     * Extract update package.
     */
    private function extractUpdatePackage(string $packagePath): string
    {
        try {
            $extractPath = storage_path('app/updates/' . uniqid());
            if (!is_dir($extractPath)) {
                mkdir($extractPath, 0755, true);
            }

            $zip = new \ZipArchive();
            $result = $zip->open($packagePath);
            if ($result !== true) {
                throw new \Exception('Failed to open update package');
            }

            $zip->extractTo($extractPath);
            $zip->close();

            Log::info('Update package extracted', [
                'package_path' => $packagePath,
                'extract_path' => $extractPath,
            ]);

            return $extractPath;
        } catch (\Exception $e) {
            Log::error('Failed to extract update package', [
                'error' => $e->getMessage(),
                'package_path' => $packagePath,
            ]);
            throw $e;
        }
    }

    /**
     * Apply updates from extracted package.
     */
    private function applyUpdates(string $extractPath): void
    {
        try {
            $manifestPath = $extractPath . '/manifest.json';
            if (!file_exists($manifestPath)) {
                throw new \Exception('Update manifest not found');
            }

            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (!$manifest) {
                throw new \Exception('Invalid update manifest');
            }

            // Copy files
            if (isset($manifest['files'])) {
                foreach ($manifest['files'] as $file) {
                    $source = $extractPath . '/' . $file;
                    $destination = base_path($file);

                    if (file_exists($source)) {
                        $this->copyFile($source, $destination);
                    }
                }
            }

            Log::info('Updates applied', [
                'extract_path' => $extractPath,
                'manifest' => $manifest,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to apply updates', [
                'error' => $e->getMessage(),
                'extract_path' => $extractPath,
            ]);
            throw $e;
        }
    }

    /**
     * Copy file with proper permissions.
     */
    private function copyFile(string $source, string $destination): void
    {
        $destinationDir = dirname($destination);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        if (!copy($source, $destination)) {
            throw new \Exception("Failed to copy file: {$source} to {$destination}");
        }

        // Set proper permissions
        chmod($destination, 0644);
    }

    /**
     * Run database migrations.
     */
    private function runMigrations(): void
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            Log::info('Database migrations completed');
        } catch (\Exception $e) {
            Log::error('Failed to run migrations', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Enable maintenance mode.
     */
    private function enableMaintenanceMode(): void
    {
        try {
            Artisan::call('down');
            Log::info('Maintenance mode enabled');
        } catch (\Exception $e) {
            Log::error('Failed to enable maintenance mode', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Disable maintenance mode.
     */
    private function disableMaintenanceMode(): void
    {
        try {
            Artisan::call('up');
            Log::info('Maintenance mode disabled');
        } catch (\Exception $e) {
            Log::error('Failed to disable maintenance mode', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Rollback update.
     */
    public function rollbackUpdate(string $backupPath): array
    {
        try {
            Log::info('Starting update rollback', [
                'backup_path' => $backupPath,
            ]);

            // Enable maintenance mode
            $this->enableMaintenanceMode();

            try {
                // Restore from backup
                $this->backupService->restoreFromBackup($backupPath);

                // Clear caches
                $this->cacheService->clearAllCaches();

                // Disable maintenance mode
                $this->disableMaintenanceMode();

                Log::info('Update rollback completed successfully', [
                    'backup_path' => $backupPath,
                ]);

                return [
                    'success' => true,
                    'message' => 'Update rollback completed successfully',
                ];
            } catch (\Exception $e) {
                $this->disableMaintenanceMode();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Update rollback failed', [
                'error' => $e->getMessage(),
                'backup_path' => $backupPath,
            ]);
            throw $e;
        }
    }

    /**
     * Get update status.
     */
    public function getUpdateStatus(): array
    {
        try {
            $status = [
                'maintenance_mode' => $this->isMaintenanceMode(),
                'cache_status' => $this->cacheService->getCacheStatus(),
                'backup_available' => $this->backupService->isBackupAvailable(),
                'system_requirements' => $this->validationService->validateSystemRequirements(),
            ];

            return $status;
        } catch (\Exception $e) {
            Log::error('Failed to get update status', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Check if maintenance mode is enabled.
     */
    private function isMaintenanceMode(): bool
    {
        return file_exists(storage_path('framework/down'));
    }

    /**
     * Clean up update files.
     */
    public function cleanupUpdateFiles(): array
    {
        try {
            $steps = [];

            // Clean up extracted files
            $updatePath = storage_path('app/updates');
            if (is_dir($updatePath)) {
                $this->deleteDirectory($updatePath);
                $steps[] = 'Update files cleaned up';
            }

            // Clean up old backups
            $this->backupService->cleanupOldBackups();

            Log::info('Update files cleaned up', [
                'steps' => $steps,
            ]);

            return $steps;
        } catch (\Exception $e) {
            Log::error('Failed to cleanup update files', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete directory recursively.
     */
    private function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = array_diff(scandir($directory), ['.', '..']);
        foreach ($files as $file) {
            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    }
}
