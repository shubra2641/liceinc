<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use ZipArchive;

/**
 * Package Validation Service - Handles validation for update packages.
 */
class PackageValidationService
{
    /**
     * Validate package path.
     */
    public function validatePackagePath(string $packagePath): void
    {
        if (empty($packagePath)) {
            throw new InvalidArgumentException('Package path is required');
        }

        if (!file_exists($packagePath)) {
            throw new InvalidArgumentException('Package file does not exist');
        }

        if (!is_readable($packagePath)) {
            throw new InvalidArgumentException('Package file is not readable');
        }

        $extension = strtolower(pathinfo($packagePath, PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            throw new InvalidArgumentException('Package must be a ZIP file');
        }

        if (filesize($packagePath) === 0) {
            throw new InvalidArgumentException('Package file is empty');
        }

        if (filesize($packagePath) > 100 * 1024 * 1024) { // 100MB limit
            throw new InvalidArgumentException('Package file is too large');
        }
    }

    /**
     * Validate package structure.
     */
    public function validatePackageStructure(string $packagePath): array
    {
        try {
            $zip = new ZipArchive();
            if ($zip->open($packagePath) !== true) {
                return [
                    'valid' => false,
                    'message' => 'Failed to open package file'
                ];
            }

            $requiredFiles = ['update.json'];
            $foundFiles = [];

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (in_array($filename, $requiredFiles)) {
                    $foundFiles[] = $filename;
                }
            }

            $zip->close();

            $missingFiles = array_diff($requiredFiles, $foundFiles);
            if (!empty($missingFiles)) {
                return [
                    'valid' => false,
                    'message' => 'Missing required files: ' . implode(', ', $missingFiles)
                ];
            }

            return [
                'valid' => true,
                'message' => 'Package structure is valid'
            ];
        } catch (\Exception $e) {
            Log::error('Package structure validation failed', [
                'package_path' => $packagePath,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'Package structure validation failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate update configuration.
     */
    public function validateUpdateConfig(array $config): array
    {
        $requiredFields = ['version', 'files', 'migrations'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!isset($config[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            return [
                'valid' => false,
                'message' => 'Missing required configuration fields: ' . implode(', ', $missingFields)
            ];
        }

        if (!is_string($config['version']) || empty($config['version'])) {
            return [
                'valid' => false,
                'message' => 'Version must be a non-empty string'
            ];
        }

        if (!is_array($config['files'])) {
            return [
                'valid' => false,
                'message' => 'Files must be an array'
            ];
        }

        if (!is_array($config['migrations'])) {
            return [
                'valid' => false,
                'message' => 'Migrations must be an array'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Configuration is valid'
        ];
    }
}
