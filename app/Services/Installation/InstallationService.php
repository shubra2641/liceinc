<?php

declare(strict_types=1);

namespace App\Services\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Installation Service.
 *
 * Handles complex installation operations to reduce controller complexity.
 */
class InstallationService
{
    /**
     * Run database migrations.
     */
    public function runMigrations(): bool
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            Log::info('Database migrations completed successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Migration failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Run database seeders.
     */
    public function runSeeders(): bool
    {
        $seeders = [
            'DatabaseSeeder',
            'AdminSeeder',
            'EmailTemplateSeeder',
            'PaymentSettingsSeeder',
            'ProductUpdateSeeder',
            'ProgrammingLanguageSeeder',
            'RolesAndPermissionsSeeder',
            'RoleSeeder',
        ];

        $success = true;
        foreach ($seeders as $seeder) {
            try {
                Artisan::call('db:seed', [
                    '--class' => $seeder,
                    '--force' => true,
                ]);
                Log::info("Seeder {$seeder} executed successfully");
            } catch (\Exception $seederError) {
                Log::warning('Seeder execution failed', [
                    'seeder' => $seeder,
                    'error' => $seederError->getMessage(),
                ]);
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Create storage link.
     */
    public function createStorageLink(): bool
    {
        try {
            Artisan::call('storage:link');
            Log::info('Storage link created successfully');

            return true;
        } catch (\Exception $e) {
            Log::info('Storage link creation skipped', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Create installed file.
     */
    public function createInstalledFile(): bool
    {
        try {
            File::put(storage_path('.installed'), now()->toDateTimeString());
            Log::info('Installation completed successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create installed file', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Update session and cache drivers to database.
     */
    public function updateSessionDrivers(): bool
    {
        try {
            $configPath = config_path('session.php');
            $cachePath = config_path('cache.php');

            if (File::exists($configPath)) {
                $sessionConfig = File::get($configPath);
                $sessionConfig = str_replace(
                    "'driver' => env('SESSION_DRIVER', 'file')",
                    "'driver' => env('SESSION_DRIVER', 'database')",
                    $sessionConfig,
                );
                File::put($configPath, $sessionConfig);
            }

            if (File::exists($cachePath)) {
                $cacheConfig = File::get($cachePath);
                $cacheConfig = str_replace(
                    "'default' => env('CACHE_DRIVER', 'file')",
                    "'default' => env('CACHE_DRIVER', 'database')",
                    $cacheConfig,
                );
                File::put($cachePath, $cacheConfig);
            }

            Log::info('Session and cache drivers updated to database');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update session drivers', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Store license information in database.
     *
     * @param array<string, mixed> $licenseConfig
     */
    public function storeLicenseInformation(array $licenseConfig): bool
    {
        try {
            DB::table('settings')->updateOrInsert(
                ['key' => 'license_config'],
                [
                    'value' => json_encode($licenseConfig),
                    'updated_at' => now(),
                ],
            );
            Log::info('License information stored successfully');

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store license information', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
