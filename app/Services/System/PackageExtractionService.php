<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * Package Extraction Service - Handles extraction of update packages.
 */
class PackageExtractionService
{
    /**
     * Extract package to temporary directory.
     */
    public function extractPackage(string $packagePath): ?string
    {
        try {
            $tempDir = storage_path('app/temp/update_' . time());

            if (!is_dir($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    throw new \Exception('Failed to create temporary directory');
                }
            }

            $zip = new ZipArchive();
            if ($zip->open($packagePath) === true) {
                $zip->extractTo($tempDir);
                $zip->close();
                return $tempDir;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to extract package', [
                'package_path' => $packagePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Read update configuration from extracted files.
     */
    public function readUpdateConfig(string $extractPath): ?array
    {
        try {
            $configFile = $extractPath . '/update.json';

            if (!file_exists($configFile)) {
                return null;
            }

            $configContent = file_get_contents($configFile);
            if ($configContent === false) {
                return null;
            }

            $config = json_decode($configContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON in update configuration', [
                    'config_file' => $configFile,
                    'json_error' => json_last_error_msg()
                ]);
                return null;
            }

            return $config;
        } catch (\Exception $e) {
            Log::error('Failed to read update configuration', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Clean up temporary files.
     */
    public function cleanupTempFiles(string $tempDir): void
    {
        try {
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cleanup temporary files', [
                'temp_dir' => $tempDir,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete directory recursively.
     */
    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
