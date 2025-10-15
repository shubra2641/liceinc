<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Update Package Service with enhanced security and comprehensive update processing.
 */
class UpdatePackageService
{
    public function __construct(
        private PackageValidationService $validationService,
        private PackageExtractionService $extractionService,
        private FileInstallationService $fileInstallationService
    ) {
    }

    /**
     * Install update files from package.
     */
    public function installUpdateFiles(string $packagePath): array
    {
        try {
            $this->validationService->validatePackagePath($packagePath);

            DB::beginTransaction();

            $tempDir = $this->extractionService->extractPackage($packagePath);
            if (!$tempDir) {
                throw new \Exception('Failed to extract package');
            }

            $steps = [];
            $filesInstalled = 0;

            $this->fileInstallationService->installFiles($tempDir, base_path(), $steps, $filesInstalled);

            $this->extractionService->cleanupTempFiles($tempDir);

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
                'message' => 'Failed to install update files: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Process update package.
     */
    public function processUpdatePackage(string $packagePath): array
    {
        try {
            $this->validationService->validatePackagePath($packagePath);

            DB::beginTransaction();

            $validation = $this->validationService->validatePackageStructure($packagePath);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }

            $extractPath = $this->extractionService->extractPackage($packagePath);
            if (!$extractPath) {
                return [
                    'success' => false,
                    'message' => 'Failed to extract update package'
                ];
            }

            $processResult = $this->processUpdateFiles($extractPath);
            if (!$processResult['success']) {
                return [
                    'success' => false,
                    'message' => $processResult['message']
                ];
            }

            $this->extractionService->cleanupTempFiles($extractPath);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Update package processed successfully',
                'data' => $processResult['data']
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process update package', [
                'package_path' => $packagePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process update package: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process update files.
     */
    private function processUpdateFiles(string $extractPath): array
    {
        try {
            $updateConfig = $this->extractionService->readUpdateConfig($extractPath);
            if (!$updateConfig) {
                return [
                    'success' => false,
                    'message' => 'Failed to read update configuration'
                ];
            }

            $configValidation = $this->validationService->validateUpdateConfig($updateConfig);
            if (!$configValidation['valid']) {
                return [
                    'success' => false,
                    'message' => $configValidation['message']
                ];
            }

            $fileUpdates = $this->fileInstallationService->processFileUpdates($extractPath, $updateConfig);
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
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process update files', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process update files: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process database migrations.
     */
    private function processMigrations(string $extractPath, array $config): array
    {
        try {
            $migrations = $config['migrations'] ?? [];
            $result = [
                'success' => true,
                'migrations_run' => 0,
                'errors' => []
            ];

            foreach ($migrations as $migration) {
                try {
                    Artisan::call('migrate', ['--path' => $migration]);
                    $result['migrations_run']++;
                } catch (\Exception $e) {
                    $result['errors'][] = "Migration failed: {$migration} - " . $e->getMessage();
                }
            }

            if (!empty($result['errors'])) {
                $result['success'] = false;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to process migrations', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'migrations_run' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Update version information.
     */
    private function updateVersionInfo(string $extractPath, array $config): array
    {
        try {
            $version = $config['version'] ?? '1.0.0';

            // Update version in config file
            $configPath = config_path('app.php');
            if (file_exists($configPath)) {
                $content = file_get_contents($configPath);
                $content = preg_replace(
                    "/'version'\s*=>\s*'[^']*'/",
                    "'version' => '{$version}'",
                    $content
                );
                file_put_contents($configPath, $content);
            }

            return [
                'success' => true,
                'version' => $version
            ];
        } catch (\Exception $e) {
            Log::error('Failed to update version info', [
                'extract_path' => $extractPath,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'version' => 'unknown'
            ];
        }
    }
}
