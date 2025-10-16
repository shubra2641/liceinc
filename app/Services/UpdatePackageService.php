<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\SecureFileHelper;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use ZipArchive;

/**
 * Update Package Service - Simplified.
 */
class UpdatePackageService
{
    /**
     * Install update files from package.
     */
    public function installUpdateFiles(string $packagePath): array
    {
        try {
            $this->validatePackagePath($packagePath);
            DB::beginTransaction();

            $tempDir = storage_path('app/temp/update_'.time());
            if (! SecureFileHelper::isDirectory($tempDir)) {
                if (! mkdir($tempDir, 0755, true)) {
                    throw new \Exception('Failed to create temporary directory');
                }
            }

            $zip = new ZipArchive();
            if ($zip->open($packagePath) !== true) {
                throw new \Exception('Failed to open update package');
            }
            $zip->extractTo($tempDir);
            $zip->close();

            $steps = [];
            $filesInstalled = 0;
            $this->installFiles($tempDir, base_path(), $steps, $filesInstalled);
            $this->cleanupTempFiles($tempDir);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Update files installed successfully',
                'data' => [
                    'files_installed' => $filesInstalled,
                    'steps' => $steps,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to install update files', [
                'package_path' => $packagePath,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to install update files: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Process uploaded update package.
     */
    public function processUpdatePackage(string $packagePath): array
    {
        try {
            $this->validatePackagePath($packagePath);
            DB::beginTransaction();

            $validation = $this->validatePackageStructure($packagePath);
            if (! $validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'data' => [],
                ];
            }

            $extractPath = $this->extractPackage($packagePath);
            if (! $extractPath) {
                return [
                    'success' => false,
                    'message' => 'Failed to extract update package',
                    'data' => [],
                ];
            }

            $processResult = $this->processUpdateFiles($extractPath);
            if (! $processResult['success']) {
                return [
                    'success' => false,
                    'message' => $processResult['message'],
                    'data' => [],
                ];
            }

            $this->cleanupTempFiles($extractPath);
            DB::commit();

            return [
                'success' => true,
                'message' => 'Update package processed successfully',
                'data' => $processResult['data'],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process update package', [
                'package_path' => $packagePath,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process update package: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Validate package path.
     */
    private function validatePackagePath(string $packagePath): void
    {
        if (empty($packagePath)) {
            throw new InvalidArgumentException('Package path cannot be empty');
        }
        if (! SecureFileHelper::fileExists($packagePath)) {
            throw new InvalidArgumentException('Package file does not exist');
        }
        if (! is_readable($packagePath)) {
            throw new InvalidArgumentException('Package file is not readable');
        }

        $extension = strtolower(pathinfo($packagePath, PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            throw new InvalidArgumentException('Package must be a ZIP file');
        }

        $fileSize = filesize($packagePath);
        if ($fileSize > 100 * 1024 * 1024) {
            throw new InvalidArgumentException('Package file is too large (max 100MB)');
        }
        if ($fileSize < 1024) {
            throw new InvalidArgumentException('Package file is too small');
        }
    }

    /**
     * Validate package structure.
     */
    private function validatePackageStructure(string $packagePath): array
    {
        if (! SecureFileHelper::fileExists($packagePath)) {
            return ['valid' => false, 'message' => 'Package file does not exist'];
        }
        if (! is_readable($packagePath)) {
            return ['valid' => false, 'message' => 'Package file is not readable'];
        }

        $zip = new ZipArchive();
        $result = $zip->open($packagePath);
        if ($result !== true) {
            return ['valid' => false, 'message' => 'Invalid ZIP file format'];
        }

        try {
            $requiredFiles = ['update.json', 'version.json'];
            $foundFiles = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $foundFiles[] = $zip->getNameIndex($i);
            }

            foreach ($requiredFiles as $requiredFile) {
                if (! in_array($requiredFile, $foundFiles)) {
                    return ['valid' => false, 'message' => "Required file missing: {$requiredFile}"];
                }
            }

            return ['valid' => true, 'message' => 'Package structure is valid'];
        } finally {
            $zip->close();
        }
    }

    /**
     * Extract update package.
     */
    private function extractPackage(string $packagePath): ?string
    {
        try {
            $tempDir = storage_path('app/temp/update_'.time());
            if (! SecureFileHelper::isDirectory($tempDir)) {
                if (! mkdir($tempDir, 0755, true)) {
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
            ]);
            throw $e;
        }
    }

    /**
     * Process update files.
     */
    private function processUpdateFiles(string $extractPath): array
    {
        try {
            $updateConfig = $this->readUpdateConfig($extractPath);
            if (! $updateConfig) {
                return [
                    'success' => false,
                    'message' => 'Failed to read update configuration',
                ];
            }

            $fileUpdates = $this->processFileUpdates($extractPath, $updateConfig);
            $migrationResult = $this->processMigrations($extractPath, $updateConfig);
            $versionResult = $this->updateVersionInfo($extractPath, $updateConfig);

            return [
                'success' => true,
                'message' => 'Update files processed successfully',
                'data' => [
                    'files' => $fileUpdates,
                    'migrations' => $migrationResult,
                    'version' => $versionResult,
                    'config' => $updateConfig,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process update files', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process update files: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Read update configuration.
     */
    private function readUpdateConfig(string $extractPath): ?array
    {
        try {
            $configFile = $extractPath.'/update.json';
            if (! SecureFileHelper::fileExists($configFile)) {
                return null;
            }
            if (! is_readable($configFile)) {
                throw new \Exception('Configuration file is not readable');
            }

            $configContent = file_get_contents($configFile);
            if ($configContent === false) {
                throw new \Exception('Failed to read configuration file');
            }

            $config = json_decode($configContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON in configuration file: '.json_last_error_msg());
            }

            return is_array($config) ? $config : [];
        } catch (\Exception $e) {
            Log::error('Failed to read update configuration', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process file updates.
     */
    private function processFileUpdates(string $extractPath, array $config): array
    {
        try {
            $processedFiles = [];
            $filesDir = $extractPath.'/files';
            if (! SecureFileHelper::isDirectory($filesDir)) {
                return $processedFiles;
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($filesDir),
                \RecursiveIteratorIterator::LEAVES_ONLY,
            );

            foreach ($files as $file) {
                if (is_object($file) && method_exists($file, 'isDir') && ! $file->isDir()) {
                    $filePath = method_exists($file, 'getRealPath') ? $file->getRealPath() : '';
                    $relativePath = substr($filePath, strlen($filesDir) + 1);
                    $targetPath = base_path($relativePath);

                    if (SecureFileHelper::fileExists($targetPath)) {
                        $backupPath = $this->createFileBackup($targetPath);
                        $processedFiles[] = [
                            'file' => $relativePath,
                            'action' => 'updated',
                            'backup' => $backupPath,
                        ];
                    } else {
                        $processedFiles[] = [
                            'file' => $relativePath,
                            'action' => 'created',
                        ];
                    }

                    $targetDir = SecureFileHelper::getDirectoryName($targetPath);
                    if (! Storage::disk('local')->exists($targetDir)) {
                        Storage::disk('local')->makeDirectory($targetDir);
                    }

                    if (! copy($filePath, $targetPath)) {
                        throw new \Exception("Failed to copy file: {$relativePath}");
                    }
                }
            }

            return ['processed_files' => $processedFiles];
        } catch (\Exception $e) {
            Log::error('Failed to process file updates', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process database migrations.
     */
    private function processMigrations(string $extractPath, array $config): array
    {
        $migrationResult = [
            'success' => false,
            'message' => '',
            'migrations' => [],
        ];

        try {
            $migrationsDir = $extractPath.'/migrations';
            if (SecureFileHelper::isDirectory($migrationsDir)) {
                $migrationFiles = glob($migrationsDir.'/*.php') ?: [];

                foreach ($migrationFiles as $migrationFile) {
                    $filename = basename($migrationFile);
                    $targetPath = database_path('migrations/'.$filename);
                    if (! copy($migrationFile, $targetPath)) {
                        throw new \Exception("Failed to copy migration file: {$filename}");
                    }
                    $migrationResult['migrations'][] = $filename;
                }

                Artisan::call('migrate', ['--force' => true]);
                $migrationResult['success'] = true;
                $migrationResult['message'] = 'Migrations completed successfully';
            } else {
                $migrationResult['success'] = true;
                $migrationResult['message'] = 'No migrations to process';
            }
        } catch (\Exception $e) {
            Log::error('Migration processing failed', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
            ]);
            $migrationResult['message'] = 'Migration failed: '.$e->getMessage();
        }

        return $migrationResult;
    }

    /**
     * Update version information.
     */
    private function updateVersionInfo(string $extractPath, array $config): array
    {
        $versionResult = [
            'success' => false,
            'message' => '',
            'version' => null,
        ];

        try {
            $versionFile = $extractPath.'/version.json';
            if (Storage::disk('local')->exists($versionFile)) {
                if (! is_readable(storage_path('app/'.$versionFile))) {
                    throw new \Exception('Version file is not readable');
                }

                $versionContent = Storage::disk('local')->get($versionFile);
                if (! $versionContent) {
                    throw new \Exception('Failed to read version file');
                }

                $versionData = json_decode($versionContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON in version file: '.json_last_error_msg());
                }

                $targetVersionFile = storage_path('version.json');
                $jsonData = json_encode($versionData, JSON_PRETTY_PRINT);
                if (file_put_contents($targetVersionFile, $jsonData) === false) {
                    throw new \Exception('Failed to write version file');
                }

                $versionResult['success'] = true;
                $versionResult['message'] = 'Version information updated';
                $versionResult['version'] = $versionData['current_version'] ?? null;
            } else {
                $versionResult['message'] = 'No version file found';
            }
        } catch (\Exception $e) {
            Log::error('Version update failed', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
            ]);
            $versionResult['message'] = 'Failed to update version: '.$e->getMessage();
        }

        return $versionResult;
    }

    /**
     * Create backup of file.
     */
    private function createFileBackup(string $filePath): string
    {
        try {
            $backupDir = storage_path('app/backups/files');
            if (! SecureFileHelper::isDirectory($backupDir)) {
                if (! mkdir($backupDir, 0755, true)) {
                    throw new \Exception('Failed to create backup directory');
                }
            }

            $relativePath = substr($filePath, strlen(base_path()) + 1);
            $backupPath = $backupDir.'/'.str_replace('/', '_', $relativePath).'_'.time();
            if (! copy($filePath, $backupPath)) {
                throw new \Exception("Failed to create backup of file: {$filePath}");
            }

            return $backupPath;
        } catch (\Exception $e) {
            Log::error('Failed to create file backup', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Clean up temporary files.
     */
    private function cleanupTempFiles(string $tempDir): void
    {
        try {
            if (SecureFileHelper::isDirectory($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cleanup temporary files', [
                'temp_dir' => $tempDir,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Install files from source to target directory.
     */
    private function installFiles(string $sourceDir, string $targetDir, array &$steps, int &$filesInstalled): void
    {
        try {
            $sourceDir = str_replace('\\', '/', $sourceDir);
            $targetDir = str_replace('\\', '/', $targetDir);

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST,
            );

            foreach ($iterator as $item) {
                $itemPath = is_object($item) && method_exists($item, 'getPathname') ? $item->getPathname() : '';
                $sourcePath = str_replace('\\', '/', $itemPath);
                $relativePath = substr($sourcePath, strlen($sourceDir) + 1);
                $targetPath = $targetDir.'/'.$relativePath;

                if (is_object($item) && method_exists($item, 'isDir') && $item->isDir()) {
                    if (! SecureFileHelper::isDirectory($targetPath)) {
                        if (! mkdir($targetPath, 0755, true)) {
                            throw new \Exception("Failed to create directory: {$relativePath}");
                        }
                        $steps["Created directory: {$relativePath}"] = true;
                    }
                } else {
                    if (SecureFileHelper::fileExists($targetPath)) {
                        $this->createFileBackup($targetPath);
                    }

                    $targetDirPath = SecureFileHelper::getDirectoryName($targetPath);
                    if (! SecureFileHelper::isDirectory($targetDirPath)) {
                        if (! mkdir($targetDirPath, 0755, true)) {
                            throw new \Exception("Failed to create target directory: {$targetDirPath}");
                        }
                    }

                    if (! copy($sourcePath, $targetPath)) {
                        throw new \Exception("Failed to copy file: {$relativePath}");
                    }

                    $filesInstalled++;
                    $steps["Installed file: {$relativePath}"] = true;
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to install files', [
                'source_dir' => $sourceDir,
                'target_dir' => $targetDir,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Delete directory recursively.
     */
    private function deleteDirectory(string $dir): void
    {
        try {
            if (! SecureFileHelper::isDirectory($dir)) {
                return;
            }

            $scandirResult = scandir($dir);
            $files = is_array($scandirResult) ? array_diff($scandirResult, ['.', '..']) : [];

            foreach ($files as $file) {
                $path = $dir.'/'.$file;
                if (SecureFileHelper::isDirectory($path)) {
                    $this->deleteDirectory($path);
                } else {
                    if (! unlink($path)) {
                        throw new \Exception("Failed to delete file: {$path}");
                    }
                }
            }

            if (! rmdir($dir)) {
                throw new \Exception("Failed to delete directory: {$dir}");
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete directory', [
                'directory' => $dir,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
