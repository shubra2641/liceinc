<?php

declare(strict_types=1);

namespace App\Services\Installation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Service for handling installation database operations.
 */
class InstallationDatabaseService
{
    /**
     * Process database configuration.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The response data
     */
    public function processDatabaseConfiguration(Request $request): array
    {
        try {
            // Validate database configuration
            $validator = $this->validateDatabaseRequest($request);
            if ($validator->fails()) {
                return [
                    'success' => false,
                    'errors' => $validator->errors(),
                ];
            }

            // Test database connection
            $connection = $this->testDatabaseConnection($request->all());
            if (!$connection['success']) {
                return [
                    'success' => false,
                    'message' => $connection['message'],
                ];
            }

            // Store database configuration
            session(['install.database' => $request->all()]);

            return [
                'success' => true,
                'message' => 'Database configuration saved successfully!',
                'redirect' => route('install.admin'),
            ];

        } catch (\Exception $e) {
            Log::error('Database configuration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Database configuration failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate database request.
     *
     * @param Request $request The HTTP request
     * @return \Illuminate\Contracts\Validation\Validator The validator instance
     */
    private function validateDatabaseRequest(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'db_host' => 'required|string|max:255',
            'db_port' => 'required|integer|min:1|max:65535',
            'db_name' => 'required|string|max:255',
            'db_username' => 'required|string|max:255',
            'db_password' => 'nullable|string|max:255',
        ], [
            'db_host.required' => 'Database host is required',
            'db_port.required' => 'Database port is required',
            'db_name.required' => 'Database name is required',
            'db_username.required' => 'Database username is required',
        ]);
    }

    /**
     * Test database connection.
     *
     * @param array<string, mixed> $config The database configuration
     * @return array<string, mixed> The connection test result
     */
    private function testDatabaseConnection(array $config): array
    {
        try {
            // Create temporary database configuration
            $tempConfig = [
                'driver' => 'mysql',
                'host' => $config['db_host'],
                'port' => $config['db_port'],
                'database' => $config['db_name'],
                'username' => $config['db_username'],
                'password' => $config['db_password'] ?? '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ];

            // Test connection
            DB::purge('mysql');
            config(['database.connections.test' => $tempConfig]);
            
            DB::connection('test')->getPdo();

            return [
                'success' => true,
                'message' => 'Database connection successful!',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get database configuration from session.
     *
     * @return array<string, mixed>|null The database configuration
     */
    public function getDatabaseConfiguration(): ?array
    {
        return session('install.database');
    }

    /**
     * Check if database is configured.
     *
     * @return bool True if configured, false otherwise
     */
    public function isDatabaseConfigured(): bool
    {
        return $this->getDatabaseConfiguration() !== null;
    }
}
