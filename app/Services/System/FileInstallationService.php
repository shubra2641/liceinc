<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Log;

/**
 * File Installation Service - Handles installation of update files.
 */
class FileInstallationService
{
    /**
     * Install files from extracted package.
     */
    public function installFiles(string $extractPath, string $targetPath, array &$steps, int &$filesInstalled): void
    {
        try {
            $files = $this->getFilesToInstall($extractPath);

            foreach ($files as $file) {
                $sourcePath = $extractPath . '/' . $file;
                $targetFilePath = $targetPath . '/' . $file;

                if ($this->shouldInstallFile($sourcePath, $targetFilePath)) {
                    $this->installSingleFile($sourcePath, $targetFilePath, $steps);
                    $filesInstalled++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to install files', [
                'extract_path' => $extractPath,
                'target_path' => $targetPath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process file updates.
     */
    public function processFileUpdates(string $extractPath, array $config): array
    {
        $result = [
            'success' => true,
            'files_updated' => 0,
            'files_skipped' => 0,
            'errors' => []
        ];

        try {
            $files = $config['files'] ?? [];

            foreach ($files as $file) {
                $sourcePath = $extractPath . '/' . $file;
                $targetPath = base_path() . '/' . $file;

                if ($this->shouldUpdateFile($sourcePath, $targetPath)) {
                    if ($this->updateFile($sourcePath, $targetPath)) {
                        $result['files_updated']++;
                    } else {
                        $result['errors'][] = "Failed to update file: {$file}";
                    }
                } else {
                    $result['files_skipped']++;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to process file updates', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage()
            ]);
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Get files to install.
     */
    private function getFilesToInstall(string $extractPath): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($extractPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($extractPath . '/', '', $file->getPathname());
                $files[] = $relativePath;
            }
        }

        return $files;
    }

    /**
     * Check if file should be installed.
     */
    private function shouldInstallFile(string $sourcePath, string $targetPath): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        // Skip certain files
        $skipFiles = ['.git', '.gitignore', 'README.md', 'update.json'];
        $fileName = basename($sourcePath);

        if (in_array($fileName, $skipFiles)) {
            return false;
        }

        return true;
    }

    /**
     * Install single file.
     */
    private function installSingleFile(string $sourcePath, string $targetPath, array &$steps): void
    {
        try {
            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (copy($sourcePath, $targetPath)) {
                $steps[] = "Installed: " . basename($targetPath);
            } else {
                throw new \Exception("Failed to copy file: {$sourcePath}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to install single file', [
                'source_path' => $sourcePath,
                'target_path' => $targetPath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Check if file should be updated.
     */
    private function shouldUpdateFile(string $sourcePath, string $targetPath): bool
    {
        if (!file_exists($sourcePath)) {
            return false;
        }

        if (!file_exists($targetPath)) {
            return true;
        }

        // Compare file modification times
        return filemtime($sourcePath) > filemtime($targetPath);
    }

    /**
     * Update file.
     */
    private function updateFile(string $sourcePath, string $targetPath): bool
    {
        try {
            $targetDir = dirname($targetPath);
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            return copy($sourcePath, $targetPath);
        } catch (\Exception $e) {
            Log::error('Failed to update file', [
                'source_path' => $sourcePath,
                'target_path' => $targetPath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
