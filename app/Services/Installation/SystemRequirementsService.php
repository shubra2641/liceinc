<?php

declare(strict_types=1);

namespace App\Services\Installation;

/**
 * System Requirements Service.
 *
 * Handles system requirements checking and validation.
 *
 * @version 1.0.0
 */
class SystemRequirementsService
{
    /**
     * Check system requirements.
     *
     * @return array<string, mixed> System requirements status
     */
    public function checkRequirements(): array
    {
        return [
            'php_version' => $this->checkPhpVersion(),
            'extensions' => $this->checkExtensions(),
            'permissions' => $this->checkPermissions(),
            'database' => $this->checkDatabaseConnection(),
        ];
    }

    /**
     * Check PHP version requirement.
     *
     * @return array<string, mixed>
     */
    private function checkPhpVersion(): array
    {
        $required = '8.1.0';
        $current = PHP_VERSION;
        $satisfied = version_compare($current, $required, '>=');
        
        return [
            'required' => $required,
            'current' => $current,
            'satisfied' => $satisfied,
        ];
    }

    /**
     * Check required PHP extensions.
     *
     * @return array<string, array<string, mixed>>
     */
    private function checkExtensions(): array
    {
        $required = [
            'openssl',
            'pdo',
            'mbstring',
            'tokenizer',
            'xml',
            'ctype',
            'json',
            'bcmath',
            'fileinfo',
            'curl',
        ];
        
        $results = [];
        foreach ($required as $extension) {
            $results[$extension] = [
                'required' => true,
                'loaded' => extension_loaded($extension),
            ];
        }
        
        return $results;
    }

    /**
     * Check file permissions.
     *
     * @return array<string, array<string, mixed>>
     */
    private function checkPermissions(): array
    {
        $directories = [
            storage_path(),
            base_path('bootstrap/cache'),
            public_path('storage'),
        ];
        
        $results = [];
        foreach ($directories as $directory) {
            $results[$directory] = [
                'path' => $directory,
                'writable' => is_writable($directory),
            ];
        }
        
        return $results;
    }

    /**
     * Check database connection.
     *
     * @return array<string, mixed>
     */
    private function checkDatabaseConnection(): array
    {
        try {
            \DB::connection()->getPdo();
            return [
                'connected' => true,
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }
}
