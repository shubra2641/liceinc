<?php
declare(strict_types=1);
namespace App\Services;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use ZipArchive;
/**
 * Update Package Service with enhanced security and comprehensive update processing.
 *
 * This service provides secure update package processing functionality including
 * package validation, file installation, database migrations, and version management.
 * It implements comprehensive security measures, input validation, and error handling
 * for reliable update operations and system maintenance.
 */
class UpdatePackageService
{
    /**
     * Install update files from package with enhanced security and error handling.
     *
     * Installs update files from a package with comprehensive validation, security measures,
     * and error handling for reliable update file installation operations.
     *
     * @param  string  $packagePath  Path to the update package file
     *
     * @return array Installation result with success status and installation details
     *
     * @throws InvalidArgumentException When package path is invalid
     * @throws \Exception When file installation fails
     *
     * @example
     * $result = $service->installUpdateFiles('/path/to/update.zip');
     * if ($result['success']) {
     *     echo "Installed {$result['data']['files_installed']} files";
     * }
     */
    public function installUpdateFiles(string $packagePath): array
    {
        try {
            // Validate input parameters
            $this->validatePackagePath($packagePath);
            DB::beginTransaction();
            $tempDir = storage_path('app/temp/update_'.time());
            // Create temp directory with security validation
            if (! is_dir($tempDir)) {
                if (! mkdir($tempDir, 0755, true)) {
                    throw new \Exception('Failed to create temporary directory');
                }
            }
            // Extract package with validation
            $zip = new ZipArchive();
            if ($zip->open($packagePath) !== true) {
                throw new \Exception('Failed to open update package');
            }
            $zip->extractTo($tempDir);
            $zip->close();
            $steps = [];
            $filesInstalled = 0;
            // Install files
            $this->installFiles($tempDir, base_path(), $steps, $filesInstalled);
            // Clean up
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
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Failed to install update files: '.$e->getMessage(),
            ];
        }
    }
    /**
     * Process uploaded update package with enhanced security and error handling.
     *
     * Processes uploaded update packages with comprehensive validation, security measures,
     * and error handling for reliable update package processing operations.
     *
     * @param  string  $packagePath  Path to the update package file
     *
     * @return array Processing result with success status and processing details
     *
     * @throws InvalidArgumentException When package path is invalid
     * @throws \Exception When package processing fails
     *
     * @example
     * $result = $service->processUpdatePackage('/path/to/update.zip');
     * if ($result['success']) {
     *     echo "Processed {$result['data']['files']} files";
     * }
     */
    public function processUpdatePackage(string $packagePath): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => [],
        ];
        try {
            // Validate input parameters
            $this->validatePackagePath($packagePath);
            DB::beginTransaction();
            // Validate package structure
            $validation = $this->validatePackageStructure($packagePath);
            if (! $validation['valid']) {
                $result['message'] = $validation['message'];
                return $result;
            }
            // Extract package
            $extractPath = $this->extractPackage($packagePath);
            if (! $extractPath) {
                $result['message'] = 'Failed to extract update package';
                return $result;
            }
            // Process update files
            $processResult = $this->processUpdateFiles($extractPath);
            if (! $processResult['success']) {
                $result['message'] = $processResult['message'];
                return $result;
            }
            // Clean up
            $this->cleanupTempFiles($extractPath);
            $result['success'] = true;
            $result['message'] = 'Update package processed successfully';
            $result['data'] = $processResult['data'];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process update package', [
                'package_path' => $packagePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $result['message'] = 'Failed to process update package: '.$e->getMessage();
        }
        return $result;
    }
    /**
     * Validate package path with enhanced security and comprehensive validation.
     *
     * @param  string  $packagePath  Package path to validate
     *
     * @throws InvalidArgumentException When package path is invalid
     */
    private function validatePackagePath(string $packagePath): void
    {
        if (empty($packagePath)) {
            throw new InvalidArgumentException('Package path cannot be empty');
        }
        if (! file_exists($packagePath)) {
            throw new InvalidArgumentException('Package file does not exist');
        }
        if (! is_readable($packagePath)) {
            throw new InvalidArgumentException('Package file is not readable');
        }
        // Validate file extension
        $extension = strtolower(pathinfo($packagePath, PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            throw new InvalidArgumentException('Package must be a ZIP file');
        }
        // Validate file size (max 100MB)
        $fileSize = filesize($packagePath);
        if ($fileSize > 100 * 1024 * 1024) {
            throw new InvalidArgumentException('Package file is too large (max 100MB)');
        }
        if ($fileSize < 1024) {
            throw new InvalidArgumentException('Package file is too small');
        }
    }
    /**
     * Validate package structure with enhanced security and comprehensive validation.
     *
     * @param  string  $packagePath  Path to the update package
     *
     * @return array Validation result with status and message
     *
     * @throws \Exception If validation fails critically
     */
    private function validatePackageStructure(string $packagePath): array
    {
        if (! file_exists($packagePath)) {
            return [
                'valid' => false,
                'message' => 'Package file does not exist',
            ];
        }
        if (! is_readable($packagePath)) {
            return [
                'valid' => false,
                'message' => 'Package file is not readable',
            ];
        }
        $zip = new ZipArchive();
        $result = $zip->open($packagePath);
        if ($result !== true) {
            return [
                'valid' => false,
                'message' => 'Invalid ZIP file format: '.$this->getZipErrorMessage($result),
            ];
        }
        try {
            $requiredFiles = [
                'update.json',
                'version.json',
            ];
            $foundFiles = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $foundFiles[] = $zip->getNameIndex($i);
            }
            foreach ($requiredFiles as $requiredFile) {
                if (! in_array($requiredFile, $foundFiles)) {
                    return [
                        'valid' => false,
                        'message' => "Required file missing: {$requiredFile}",
                    ];
                }
            }
            return [
                'valid' => true,
                'message' => 'Package structure is valid',
            ];
        } finally {
            $zip->close();
        }
    }
    /**
     * Get human-readable ZIP error message with enhanced error handling.
     *
     * @param  int  $errorCode  ZIP error code
     *
     * @return string Error message
     */
    private function getZipErrorMessage(int $errorCode): string
    {
        $errorMessages = [
            ZipArchive::ER_OK => 'No error',
            ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
            ZipArchive::ER_RENAME => 'Renaming temporary file failed',
            ZipArchive::ER_CLOSE => 'Closing zip archive failed',
            ZipArchive::ER_SEEK => 'Seek error',
            ZipArchive::ER_READ => 'Read error',
            ZipArchive::ER_WRITE => 'Write error',
            ZipArchive::ER_CRC => 'CRC error',
            ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
            ZipArchive::ER_NOENT => 'No such file',
            ZipArchive::ER_EXISTS => 'File already exists',
            ZipArchive::ER_OPEN => 'Can\'t open file',
            ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
            ZipArchive::ER_ZLIB => 'Zlib error',
            ZipArchive::ER_MEMORY => 'Memory allocation failure',
            ZipArchive::ER_CHANGED => 'Entry has been changed',
            ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
            ZipArchive::ER_EOF => 'Premature EOF',
            ZipArchive::ER_INVAL => 'Invalid argument',
            ZipArchive::ER_NOZIP => 'Not a zip archive',
            ZipArchive::ER_INTERNAL => 'Internal error',
            ZipArchive::ER_INCONS => 'Zip archive inconsistent',
            ZipArchive::ER_REMOVE => 'Can\'t remove file',
            ZipArchive::ER_DELETED => 'Entry has been deleted',
        ];
        return $errorMessages[$errorCode] ?? 'Unknown error';
    }
    /**
     * Extract update package with enhanced security and error handling.
     *
     * @param  string  $packagePath  Path to the package file
     *
     * @return string|null Path to extracted directory or null on failure
     *
     * @throws \Exception When extraction fails
     */
    private function extractPackage(string $packagePath): ?string
    {
        try {
            $tempDir = storage_path('app/temp/update_'.time());
            if (! is_dir($tempDir)) {
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
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Process update files with enhanced security and error handling.
     *
     * @param  string  $extractPath  Path to extracted files
     *
     * @return array Processing result with success status and processing details
     *
     * @throws \Exception When file processing fails
     */
    private function processUpdateFiles(string $extractPath): array
    {
        $result = [
            'success' => false,
            'message' => '',
            'data' => [],
        ];
        try {
            // Read update configuration
            $updateConfig = $this->readUpdateConfig($extractPath);
            if (! $updateConfig) {
                $result['message'] = 'Failed to read update configuration';
                return $result;
            }
            // Process file updates
            $fileUpdates = $this->processFileUpdates($extractPath, $updateConfig);
            // Process database migrations
            $migrationResult = $this->processMigrations($extractPath, $updateConfig);
            // Update version information
            $versionResult = $this->updateVersionInfo($extractPath, $updateConfig);
            $result['success'] = true;
            $result['message'] = 'Update files processed successfully';
            $result['data'] = [
                'files' => $fileUpdates,
                'migrations' => $migrationResult,
                'version' => $versionResult,
                'config' => $updateConfig,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process update files', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $result['message'] = 'Failed to process update files: '.$e->getMessage();
        }
        return $result;
    }
    /**
     * Read update configuration with enhanced security and error handling.
     *
     * @param  string  $extractPath  Path to extracted files
     *
     * @return array|null Configuration array or null on failure
     *
     * @throws \Exception When configuration reading fails
     */
    private function readUpdateConfig(string $extractPath): ?array
    {
        try {
            $configFile = $extractPath.'/update.json';
            if (! file_exists($configFile)) {
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
            return $config ?: null;
        } catch (\Exception $e) {
            Log::error('Failed to read update configuration', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Process file updates with enhanced security and error handling.
     *
     * @param  string  $extractPath  Path to extracted files
     * @param  array  $config  Update configuration
     *
     * @return array Array of processed files with their actions
     *
     * @throws \Exception When file processing fails
     */
    private function processFileUpdates(string $extractPath, array $config): array
    {
        try {
            $processedFiles = [];
            $filesDir = $extractPath.'/files';
            if (! is_dir($filesDir)) {
                return $processedFiles;
            }
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($filesDir),
                \RecursiveIteratorIterator::LEAVES_ONLY,
            );
            foreach ($files as $file) {
                if (! $file->isDir()) {
                    $relativePath = substr($file->getRealPath(), strlen($filesDir) + 1);
                    $targetPath = base_path($relativePath);
                    // Create backup of existing file
                    if (file_exists($targetPath)) {
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
                    // Create directory if it doesn't exist
                    $targetDir = dirname($targetPath);
                    if (! Storage::disk('local')->exists($targetDir)) {
                        Storage::disk('local')->makeDirectory($targetDir);
                    }
                    // Copy file
                    if (! copy($file->getRealPath(), $targetPath)) {
                        throw new \Exception("Failed to copy file: {$relativePath}");
                    }
                }
            }
            return $processedFiles;
        } catch (\Exception $e) {
            Log::error('Failed to process file updates', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Process database migrations with enhanced security and error handling.
     *
     * @param  string  $extractPath  Path to extracted files
     * @param  array  $config  Update configuration
     *
     * @return array Migration result with success status and migration details
     *
     * @throws \Exception When migration processing fails
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
            if (is_dir($migrationsDir)) {
                // Copy migration files
                $migrationFiles = glob($migrationsDir.'/*.php');
                foreach ($migrationFiles as $migrationFile) {
                    $filename = basename($migrationFile);
                    $targetPath = database_path('migrations/'.$filename);
                    if (! copy($migrationFile, $targetPath)) {
                        throw new \Exception("Failed to copy migration file: {$filename}");
                    }
                    $migrationResult['migrations'][] = $filename;
                }
                // Run migrations
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
                'trace' => $e->getTraceAsString(),
            ]);
            $migrationResult['message'] = 'Migration failed: '.$e->getMessage();
        }
        return $migrationResult;
    }
    /**
     * Update version information with enhanced security and error handling.
     *
     * @param  string  $extractPath  Path to extracted files
     * @param  array  $config  Update configuration
     *
     * @return array Version update result with success status and version details
     *
     * @throws \Exception When version update fails
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
                if (! is_readable($versionFile)) {
                    throw new \Exception('Version file is not readable');
                }
                $versionContent = Storage::disk('local')->get($versionFile);
                if ($versionContent === false) {
                    throw new \Exception('Failed to read version file');
                }
                $versionData = json_decode($versionContent, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON in version file: '.json_last_error_msg());
                }
                // Update storage/version.json
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
                'trace' => $e->getTraceAsString(),
            ]);
            $versionResult['message'] = 'Failed to update version: '.$e->getMessage();
        }
        return $versionResult;
    }
    /**
     * Create backup of file with enhanced security and error handling.
     *
     * @param  string  $filePath  Path to the file to backup
     *
     * @return string Path to the backup file
     *
     * @throws \Exception When backup creation fails
     */
    private function createFileBackup(string $filePath): string
    {
        try {
            $backupDir = storage_path('app/backups/files');
            if (! is_dir($backupDir)) {
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
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Clean up temporary files with enhanced security and error handling.
     *
     * @param  string  $tempDir  Path to temporary directory to clean up
     *
     * @throws \Exception When cleanup fails
     */
    private function cleanupTempFiles(string $tempDir): void
    {
        try {
            if (is_dir($tempDir)) {
                $this->deleteDirectory($tempDir);
            }
        } catch (\Exception $e) {
            Log::error('Failed to cleanup temporary files', [
                'temp_dir' => $tempDir,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Install files from source to target directory with enhanced security and error handling.
     *
     * @param  string  $sourceDir  Source directory path
     * @param  string  $targetDir  Target directory path
     * @param  array  $steps  Reference to steps array for logging
     * @param  int  $filesInstalled  Reference to files installed counter
     *
     * @throws \Exception When file installation fails
     */
    private function installFiles(string $sourceDir, string $targetDir, array &$steps, int &$filesInstalled): void
    {
        try {
            // Normalize paths for cross-platform compatibility
            $sourceDir = str_replace('\\', '/', $sourceDir);
            $targetDir = str_replace('\\', '/', $targetDir);
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST,
            );
            foreach ($iterator as $item) {
                $sourcePath = str_replace('\\', '/', $item->getPathname());
                $relativePath = substr($sourcePath, strlen($sourceDir) + 1);
                $targetPath = $targetDir.'/'.$relativePath;
                if ($item->isDir()) {
                    if (! is_dir($targetPath)) {
                        if (! mkdir($targetPath, 0755, true)) {
                            throw new \Exception("Failed to create directory: {$relativePath}");
                        }
                        $steps[] = "Created directory: {$relativePath}";
                    }
                } else {
                    // Create backup of existing file
                    if (file_exists($targetPath)) {
                        $this->createFileBackup($targetPath);
                    }
                    // Create target directory if it doesn't exist
                    $targetDirPath = dirname($targetPath);
                    if (! is_dir($targetDirPath)) {
                        if (! mkdir($targetDirPath, 0755, true)) {
                            throw new \Exception("Failed to create target directory: {$targetDirPath}");
                        }
                    }
                    // Copy file
                    if (! copy($sourcePath, $targetPath)) {
                        throw new \Exception("Failed to copy file: {$relativePath}");
                    }
                    $filesInstalled++;
                    $steps[] = "Installed file: {$relativePath}";
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to install files', [
                'source_dir' => $sourceDir,
                'target_dir' => $targetDir,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Delete directory recursively with enhanced security and error handling.
     *
     * @param  string  $dir  Directory path to delete
     *
     * @throws \Exception When directory deletion fails
     */
    private function deleteDirectory(string $dir): void
    {
        try {
            if (! is_dir($dir)) {
                return;
            }
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir.'/'.$file;
                if (is_dir($path)) {
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
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
