<?php

declare(strict_types=1);

namespace App\Services\Installation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Service for handling installation admin operations.
 */
class InstallationAdminService
{
    /**
     * Process admin account creation.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The response data
     */
    public function processAdminCreation(Request $request): array
    {
        try {
            // Validate admin data
            $validator = $this->validateAdminRequest($request);
            if ($validator->fails()) {
                return [
                    'success' => false,
                    'errors' => $validator->errors(),
                ];
            }

            // Store admin configuration
            session(['install.admin' => $request->all()]);

            return [
                'success' => true,
                'message' => 'Admin account configuration saved successfully!',
                'redirect' => route('install.settings'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Admin account creation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate admin request.
     *
     * @param Request $request The HTTP request
     * @return \Illuminate\Contracts\Validation\Validator The validator instance
     */
    private function validateAdminRequest(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Password confirmation does not match',
        ]);
    }

    /**
     * Get admin configuration from session.
     *
     * @return array<string, mixed>|null The admin configuration
     */
    public function getAdminConfiguration(): ?array
    {
        return session('install.admin');
    }

    /**
     * Check if admin is configured.
     *
     * @return bool True if configured, false otherwise
     */
    public function isAdminConfigured(): bool
    {
        return $this->getAdminConfiguration() !== null;
    }

    /**
     * Validate installation prerequisites.
     *
     * @return array<string, mixed> The validation result
     */
    public function validatePrerequisites(): array
    {
        $licenseConfig = session('install.license');
        $databaseConfig = session('install.database');

        if (!$licenseConfig) {
            return [
                'success' => false,
                'message' => 'Please verify your license first.',
                'redirect' => route('install.license'),
            ];
        }

        if (!$databaseConfig) {
            return [
                'success' => false,
                'message' => 'Please configure database settings first.',
                'redirect' => route('install.database'),
            ];
        }

        return [
            'success' => true,
        ];
    }
}
