<?php

declare(strict_types=1);

namespace App\Services\System;

use App\Helpers\SecureFileHelper;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Update Backup Service - Handles system backup operations.
 */
class UpdateBackupService
{
    /**
     * Create system backup before update.
     */
    public function createSystemBackup(string $currentVersion): string
    {
        try {
            $backupDir = storage_path('backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $backupFileName = 'backup_' . $currentVersion . '_' . date('Y-m-d_H-i-s') . '.zip';
            $backupPath = $backupDir . '/' . $backupFileName;

            $zip = new ZipArchive();
            if ($zip->open($backupPath, ZipArchive::CREATE) !== true) {
                throw new \Exception('Cannot create backup archive');
            }

            $this->addDirectoryToZip($zip, base_path(), '');
            $zip->close();

            Log::info('System backup created', [
                'backup_path' => $backupPath,
                'version' => $currentVersion,
            ]);

            return $backupPath;
        } catch (\Exception $e) {
            Log::error('Failed to create system backup', [
                'error' => $e->getMessage(),
                'version' => $currentVersion,
            ]);
            throw $e;
        }
    }

    /**
     * Restore system from backup.
     */
    public function restoreSystemFromBackup(string $backupPath): bool
    {
        try {
            if (!file_exists($backupPath)) {
                throw new \Exception('Backup file not found');
            }

            $zip = new ZipArchive();
            if ($zip->open($backupPath) !== true) {
                throw new \Exception('Cannot open backup archive');
            }

            $zip->extractTo(base_path());
            $zip->close();

            Log::info('System restored from backup', [
                'backup_path' => $backupPath,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to restore system from backup', [
                'error' => $e->getMessage(),
                'backup_path' => $backupPath,
            ]);
            throw $e;
        }
    }

    /**
     * List available backups.
     */
    public function listAvailableBackups(): array
    {
        try {
            $backupDir = storage_path('backups');
            if (!is_dir($backupDir)) {
                return [];
            }

            $backups = [];
            $files = scandir($backupDir);

            foreach ($files as $file) {
                if (strpos($file, 'backup_') === 0 && strpos($file, '.zip') !== false) {
                    $backups[] = [
                        'filename' => $file,
                        'path' => $backupDir . '/' . $file,
                        'size' => filesize($backupDir . '/' . $file),
                        'created_at' => date('Y-m-d H:i:s', filemtime($backupDir . '/' . $file)),
                    ];
                }
            }

            // Sort by creation time (newest first)
            usort($backups, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return $backups;
        } catch (\Exception $e) {
            Log::error('Failed to list available backups', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Delete old backups.
     */
    public function deleteOldBackups(int $keepCount = 5): int
    {
        try {
            $backups = $this->listAvailableBackups();
            $deletedCount = 0;

            if (count($backups) > $keepCount) {
                $backupsToDelete = array_slice($backups, $keepCount);

                foreach ($backupsToDelete as $backup) {
                    if (unlink($backup['path'])) {
                        $deletedCount++;
                        Log::info('Old backup deleted', [
                            'backup_path' => $backup['path'],
                        ]);
                    }
                }
            }

            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Failed to delete old backups', [
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Get backup size.
     */
    public function getBackupSize(string $backupPath): int
    {
        try {
            if (!file_exists($backupPath)) {
                return 0;
            }

            return filesize($backupPath);
        } catch (\Exception $e) {
            Log::error('Failed to get backup size', [
                'error' => $e->getMessage(),
                'backup_path' => $backupPath,
            ]);
            return 0;
        }
    }

    /**
     * Validate backup integrity.
     */
    public function validateBackupIntegrity(string $backupPath): bool
    {
        try {
            if (!file_exists($backupPath)) {
                return false;
            }

            $zip = new ZipArchive();
            $result = $zip->open($backupPath);

            if ($result !== true) {
                return false;
            }

            $zip->close();
            return true;
        } catch (\Exception $e) {
            Log::error('Backup integrity validation failed', [
                'error' => $e->getMessage(),
                'backup_path' => $backupPath,
            ]);
            return false;
        }
    }

    /**
     * Add directory to ZIP archive.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipPath): void
    {
        try {
            $files = scandir($dir);

            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $filePath = $dir . '/' . $file;
                $zipFilePath = $zipPath . $file;

                if (is_dir($filePath)) {
                    $zip->addEmptyDir($zipFilePath . '/');
                    $this->addDirectoryToZip($zip, $filePath, $zipFilePath . '/');
                } else {
                    $zip->addFile($filePath, $zipFilePath);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to add directory to ZIP', [
                'error' => $e->getMessage(),
                'dir' => $dir,
                'zip_path' => $zipPath,
            ]);
            throw $e;
        }
    }
}
