<?php

declare(strict_types=1);

namespace App\Services\Installation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Service for handling installation settings operations.
 */
class InstallationSettingsService
{
    public function __construct(
        private InstallationConfigService $configService
    ) {}

    /**
     * Process system settings.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The response data
     */
    public function processSystemSettings(Request $request): array
    {
        try {
            // Validate settings data
            $validator = $this->validateSettingsRequest($request);
            if ($validator->fails()) {
                return [
                    'success' => false,
                    'errors' => $validator->errors(),
                ];
            }

            // Store settings configuration
            session(['install.settings' => $request->all()]);

            return [
                'success' => true,
                'message' => 'System settings saved successfully!',
                'redirect' => route('install.install'),
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'System settings failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate settings request.
     *
     * @param Request $request The HTTP request
     * @return \Illuminate\Contracts\Validation\Validator The validator instance
     */
    private function validateSettingsRequest(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'admin_email' => 'required_if:enable_email,1|nullable|string|email|max:255',
            'timezone' => 'required|string',
            'locale' => 'required|string|in:en,ar',
            'enable_email' => 'nullable|boolean',
            'currency' => 'required|string|max:3',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
        ], [
            'site_name.required' => 'Site name is required',
            'admin_email.email' => 'Admin email must be a valid email address',
            'timezone.required' => 'Timezone is required',
            'locale.required' => 'Locale is required',
            'currency.required' => 'Currency is required',
            'date_format.required' => 'Date format is required',
            'time_format.required' => 'Time format is required',
        ]);
    }

    /**
     * Get settings configuration from session.
     *
     * @return array<string, mixed>|null The settings configuration
     */
    public function getSettingsConfiguration(): ?array
    {
        return session('install.settings');
    }

    /**
     * Check if settings are configured.
     *
     * @return bool True if configured, false otherwise
     */
    public function isSettingsConfigured(): bool
    {
        return $this->getSettingsConfiguration() !== null;
    }

    /**
     * Get available timezones.
     *
     * @return array<string, string> The timezones
     */
    public function getTimezones(): array
    {
        return $this->configService->getTimezones();
    }

    /**
     * Get supported languages.
     *
     * @return array<string, string> The supported languages
     */
    public function getSupportedLanguages(): array
    {
        return $this->configService->getSupportedLanguages();
    }

    /**
     * Get default settings.
     *
     * @return array<string, mixed> The default settings
     */
    public function getDefaultSettings(): array
    {
        return $this->configService->getDefaultSettings();
    }

    /**
     * Validate installation prerequisites for settings.
     *
     * @return array<string, mixed> The validation result
     */
    public function validatePrerequisites(): array
    {
        $licenseConfig = session('install.license');
        $databaseConfig = session('install.database');
        $adminConfig = session('install.admin');

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

        if (!$adminConfig) {
            return [
                'success' => false,
                'message' => 'Please create admin account first.',
                'redirect' => route('install.admin'),
            ];
        }

        return [
            'success' => true,
        ];
    }
}
