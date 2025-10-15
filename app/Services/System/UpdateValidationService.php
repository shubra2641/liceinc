<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Log;

/**
 * Update Validation Service - Handles validation during updates.
 */
class UpdateValidationService
{
    /**
     * Validate system requirements for update.
     */
    public function validateSystemRequirements(): array
    {
        try {
            $requirements = [
                'php_version' => $this->validatePhpVersion(),
                'memory_limit' => $this->validateMemoryLimit(),
                'disk_space' => $this->validateDiskSpace(),
                'permissions' => $this->validatePermissions(),
                'dependencies' => $this->validateDependencies(),
            ];

            $allValid = array_reduce($requirements, function ($carry, $requirement) {
                return $carry && $requirement['valid'];
            }, true);

            Log::info('System requirements validated', [
                'requirements' => $requirements,
                'all_valid' => $allValid,
            ]);

            return [
                'valid' => $allValid,
                'requirements' => $requirements,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to validate system requirements', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate PHP version.
     */
    private function validatePhpVersion(): array
    {
        $requiredVersion = '8.1.0';
        $currentVersion = PHP_VERSION;
        $valid = version_compare($currentVersion, $requiredVersion, '>=');

        return [
            'valid' => $valid,
            'required' => $requiredVersion,
            'current' => $currentVersion,
            'message' => $valid ? 'PHP version is compatible' : 'PHP version is too old',
        ];
    }

    /**
     * Validate memory limit.
     */
    private function validateMemoryLimit(): array
    {
        $requiredMemory = '256M';
        $currentMemory = ini_get('memory_limit');
        $valid = $this->compareMemoryLimits($currentMemory, $requiredMemory);

        return [
            'valid' => $valid,
            'required' => $requiredMemory,
            'current' => $currentMemory,
            'message' => $valid ? 'Memory limit is sufficient' : 'Memory limit is too low',
        ];
    }

    /**
     * Validate disk space.
     */
    private function validateDiskSpace(): array
    {
        $requiredSpace = 100 * 1024 * 1024; // 100MB
        $freeSpace = disk_free_space(base_path());
        $valid = $freeSpace >= $requiredSpace;

        return [
            'valid' => $valid,
            'required' => $this->formatBytes($requiredSpace),
            'available' => $this->formatBytes($freeSpace),
            'message' => $valid ? 'Sufficient disk space available' : 'Insufficient disk space',
        ];
    }

    /**
     * Validate file permissions.
     */
    private function validatePermissions(): array
    {
        $directories = [
            'storage',
            'bootstrap/cache',
            'public',
        ];

        $permissions = [];
        $allValid = true;

        foreach ($directories as $directory) {
            $path = base_path($directory);
            $writable = is_writable($path);
            $permissions[$directory] = $writable;
            if (!$writable) {
                $allValid = false;
            }
        }

        return [
            'valid' => $allValid,
            'permissions' => $permissions,
            'message' => $allValid ? 'All directories are writable' : 'Some directories are not writable',
        ];
    }

    /**
     * Validate dependencies.
     */
    private function validateDependencies(): array
    {
        $requiredExtensions = [
            'openssl',
            'pdo',
            'mbstring',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'bcmath',
        ];

        $extensions = [];
        $allValid = true;

        foreach ($requiredExtensions as $extension) {
            $loaded = extension_loaded($extension);
            $extensions[$extension] = $loaded;
            if (!$loaded) {
                $allValid = false;
            }
        }

        return [
            'valid' => $allValid,
            'extensions' => $extensions,
            'message' => $allValid ? 'All required extensions are loaded' : 'Some required extensions are missing',
        ];
    }

    /**
     * Validate update package.
     */
    public function validateUpdatePackage(string $packagePath): array
    {
        try {
            if (!file_exists($packagePath)) {
                throw new \InvalidArgumentException('Update package not found');
            }

            $validation = [
                'file_exists' => file_exists($packagePath),
                'is_readable' => is_readable($packagePath),
                'file_size' => filesize($packagePath),
                'is_zip' => $this->isZipFile($packagePath),
                'has_manifest' => $this->hasManifest($packagePath),
            ];

            $valid = array_reduce($validation, function ($carry, $check) {
                return $carry && $check;
            }, true);

            Log::info('Update package validated', [
                'package_path' => $packagePath,
                'validation' => $validation,
                'valid' => $valid,
            ]);

            return [
                'valid' => $valid,
                'validation' => $validation,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to validate update package', [
                'error' => $e->getMessage(),
                'package_path' => $packagePath,
            ]);
            throw $e;
        }
    }

    /**
     * Check if file is a valid ZIP.
     */
    private function isZipFile(string $filePath): bool
    {
        try {
            $zip = new \ZipArchive();
            $result = $zip->open($filePath);
            if ($result === true) {
                $zip->close();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check ZIP file', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
            ]);
            return false;
        }
    }

    /**
     * Check if package has manifest.
     */
    private function hasManifest(string $filePath): bool
    {
        try {
            $zip = new \ZipArchive();
            $result = $zip->open($filePath);
            if ($result === true) {
                $hasManifest = $zip->locateName('manifest.json') !== false;
                $zip->close();
                return $hasManifest;
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Failed to check manifest', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
            ]);
            return false;
        }
    }

    /**
     * Compare memory limits.
     */
    private function compareMemoryLimits(string $current, string $required): bool
    {
        $currentBytes = $this->convertToBytes($current);
        $requiredBytes = $this->convertToBytes($required);
        return $currentBytes >= $requiredBytes;
    }

    /**
     * Convert memory limit to bytes.
     */
    private function convertToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Validate update process.
     */
    public function validateUpdateProcess(): array
    {
        try {
            $validation = [
                'system_requirements' => $this->validateSystemRequirements(),
                'backup_available' => $this->validateBackupAvailable(),
                'maintenance_mode' => $this->validateMaintenanceMode(),
            ];

            $allValid = array_reduce($validation, function ($carry, $check) {
                return $carry && $check['valid'];
            }, true);

            Log::info('Update process validated', [
                'validation' => $validation,
                'all_valid' => $allValid,
            ]);

            return [
                'valid' => $allValid,
                'validation' => $validation,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to validate update process', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate backup availability.
     */
    private function validateBackupAvailable(): array
    {
        $backupPath = storage_path('app/backups');
        $backupExists = is_dir($backupPath) && count(scandir($backupPath)) > 2;

        return [
            'valid' => $backupExists,
            'backup_path' => $backupPath,
            'message' => $backupExists ? 'Backup is available' : 'No backup found',
        ];
    }

    /**
     * Validate maintenance mode.
     */
    private function validateMaintenanceMode(): array
    {
        $maintenanceFile = storage_path('framework/down');
        $inMaintenance = file_exists($maintenanceFile);

        return [
            'valid' => $inMaintenance,
            'maintenance_file' => $maintenanceFile,
            'message' => $inMaintenance ? 'Application is in maintenance mode' : 'Application is not in maintenance mode',
        ];
    }
}
