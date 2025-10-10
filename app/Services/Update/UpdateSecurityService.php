<?php

declare(strict_types=1);

namespace App\Services\Update;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling update security operations.
 */
class UpdateSecurityService
{
    /**
     * Validate update request.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The validation result
     */
    public function validateUpdateRequest(Request $request): array
    {
        try {
            $version = $request->input('version');
            $licenseKey = $request->input('license_key');

            if (!$version) {
                return [
                    'success' => false,
                    'message' => 'Version is required',
                ];
            }

            if (!$licenseKey) {
                return [
                    'success' => false,
                    'message' => 'License key is required',
                ];
            }

            // Validate version format
            if (!$this->isValidVersionFormat($version)) {
                return [
                    'success' => false,
                    'message' => 'Invalid version format',
                ];
            }

            // Validate license key format
            if (!$this->isValidLicenseKeyFormat($licenseKey)) {
                return [
                    'success' => false,
                    'message' => 'Invalid license key format',
                ];
            }

            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Error validating update request', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate version format.
     *
     * @param string $version The version to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidVersionFormat(string $version): bool
    {
        return preg_match('/^\d+\.\d+\.\d+$/', $version) === 1;
    }

    /**
     * Validate license key format.
     *
     * @param string $licenseKey The license key to validate
     * @return bool True if valid, false otherwise
     */
    private function isValidLicenseKeyFormat(string $licenseKey): bool
    {
        return preg_match('/^[A-Za-z0-9\-]{8,}$/', $licenseKey) === 1;
    }

    /**
     * Log security event.
     *
     * @param string $event The security event
     * @param array<string, mixed> $data Additional data
     * @return void
     */
    public function logSecurityEvent(string $event, array $data = []): void
    {
        Log::warning('Update Security Event', [
            'event' => $event,
            'data' => $data,
            'timestamp' => now(),
        ]);
    }

    /**
     * Validate file upload security.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The validation result
     */
    public function validateFileUploadSecurity(Request $request): array
    {
        try {
            if (!$request->hasFile('update_package')) {
                return [
                    'success' => false,
                    'message' => 'No file uploaded',
                ];
            }

            $file = $request->file('update_package');

            // Validate file type
            $allowedTypes = ['zip', 'tar.gz'];
            $extension = $file->getClientOriginalExtension();

            if (!in_array($extension, $allowedTypes)) {
                return [
                    'success' => false,
                    'message' => 'Invalid file type. Only ZIP and TAR.GZ files are allowed.',
                ];
            }

            // Validate file size (max 100MB)
            if ($file->getSize() > 100 * 1024 * 1024) {
                return [
                    'success' => false,
                    'message' => 'File size too large. Maximum 100MB allowed.',
                ];
            }

            return [
                'success' => true,
            ];
        } catch (\Exception $e) {
            Log::error('Error validating file upload security', [
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'File validation failed: ' . $e->getMessage(),
            ];
        }
    }
}
