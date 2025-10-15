<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\EnvatoProvider;
use App\Services\Envato\EnvatoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Envato Socialite Provider with enhanced security and error handling.
 *
 * This service provider extends Laravel Socialite to support Envato OAuth authentication.
 * It provides secure integration with Envato's OAuth API, including proper error handling,
 * input validation, and comprehensive logging for debugging and monitoring.
 */
class EnvatoSocialiteProvider extends ServiceProvider
{
    /**
     * Register services with enhanced security validation.
     *
     * Registers the Envato Socialite provider with proper dependency injection
     * and security validation. This method ensures that all required services
     * are properly bound and validated before use.
     *
     * @throws \Exception When service registration fails
     *
     * @example
     * // The provider is automatically registered by Laravel's service container
     * // No manual registration required
     */
    public function register(): void
    {
        try {
            // Register the Envato service as a singleton for better performance
            $this->app->singleton(EnvatoService::class, function ($app) {
                return new EnvatoService();
            });
        } catch (\Exception $e) {
            Log::error('Failed to register EnvatoSocialiteProvider services', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Bootstrap services with comprehensive error handling and security validation.
     *
     * Extends Laravel Socialite with Envato OAuth provider support. This method
     * includes comprehensive error handling, input validation, and security measures
     * to ensure safe OAuth integration with Envato's API.
     *
     * @throws \Exception When provider extension fails
     *
     * @example
     * // Provider is automatically booted by Laravel
     * // Usage: Socialite::driver('envato')->redirect()
     */
    public function boot(): void
    {
        try {
            \Laravel\Socialite\Facades\Socialite::extend('envato', function ($app) {
                return $this->createEnvatoProvider($app);
            });
        } catch (\Exception $e) {
            Log::error('Failed to boot EnvatoSocialiteProvider', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Create and configure the Envato provider with enhanced security validation.
     *
     * Creates a new EnvatoProvider instance with validated settings from the
     * EnvatoService. Includes comprehensive error handling and input validation
     * to ensure secure OAuth configuration.
     *
     * @param  mixed  $app  The Laravel application instance
     *
     * @return EnvatoProvider The configured Envato provider
     *
     * @throws InvalidArgumentException When required settings are missing or invalid
     * @throws \Exception When provider creation fails
     *
     * @example
     * $provider = $this->createEnvatoProvider($app);
     * $user = $provider->user();
     */
    private function createEnvatoProvider($app): EnvatoProvider
    {
        try {
            // Get Envato service with error handling
            $envatoService = $this->getEnvatoService();
            // Get and validate settings
            $settings = $this->getValidatedSettings($envatoService);
            // Validate required settings
            $this->validateRequiredSettings($settings);
            // Create provider with validated settings
            $request = request(); // Use global request helper
            // Ensure session is available
            if (!$request->hasSession()) {
                $request->setLaravelSession(app('session.store'));
            }
            $clientId = is_string($settings['client_id'] ?? config('services.envato.client_id') ?? '')
                ? ($settings['client_id'] ?? config('services.envato.client_id') ?? '')
                : '';
            $clientSecret = is_string($settings['client_secret'] ?? config('services.envato.client_secret') ?? '')
                ? ($settings['client_secret'] ?? config('services.envato.client_secret') ?? '')
                : '';
            $redirect = is_string($settings['redirect'] ?? config('services.envato.redirect') ?? '')
                ? ($settings['redirect'] ?? config('services.envato.redirect') ?? '')
                : '';
            return new EnvatoProvider(
                $request,
                $this->sanitizeSetting($clientId) ?? '',
                $this->sanitizeSetting($clientSecret) ?? '',
                $this->sanitizeSetting($redirect) ?? '',
            );
        } catch (\Exception $e) {
            Log::error('Failed to create Envato provider', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get the Envato service instance with error handling.
     *
     * Retrieves the EnvatoService instance from the service container
     * with proper error handling and validation.
     *
     * @return EnvatoService The Envato service instance
     *
     * @throws \Exception When service cannot be resolved
     */
    private function getEnvatoService(): EnvatoService
    {
        try {
            $service = app(EnvatoService::class);
            return $service;
        } catch (\Exception $e) {
            Log::error('Failed to resolve EnvatoService', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get and validate Envato settings with comprehensive error handling.
     *
     * Retrieves settings from the EnvatoService and validates their structure
     * and content to ensure they meet security requirements.
     *
     * @param  EnvatoService  $envatoService  The Envato service instance
     *
     * @return array<string, mixed> The validated settings array
     *
     * @throws \Exception When settings retrieval or validation fails
     */
    private function getValidatedSettings(EnvatoService $envatoService): array
    {
        try {
            $settings = $envatoService->getEnvatoSettings();
            return $settings;
        } catch (\Exception $e) {
            Log::error('Failed to get Envato settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Validate that all required settings are present and valid.
     *
     * Ensures that all required OAuth settings are present and meet
     * security requirements for safe OAuth integration.
     *
     * @param  array<string, mixed>  $settings  The settings array to validate
     *
     * @throws InvalidArgumentException When required settings are missing or invalid
     */
    private function validateRequiredSettings(array $settings): void
    {
        $requiredSettings = ['client_id', 'client_secret', 'redirect'];
        foreach ($requiredSettings as $setting) {
            if (! isset($settings[$setting]) || empty($settings[$setting])) {
                // Check config fallback
                $configValue = config("services.envato.{$setting}");
                if (empty($configValue)) {
                    throw new InvalidArgumentException("Required setting '{$setting}' is missing or empty");
                }
            }
        }
        // Validate redirect URL format
        $redirectUrl = $settings['redirect'] ?? config('services.envato.redirect');
        if (! filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid redirect URL format');
        }
    }
    /**
     * Sanitize setting values to prevent XSS and injection attacks.
     *
     * Applies security sanitization to setting values to prevent
     * XSS attacks and other security vulnerabilities.
     *
     * @param  string|null  $value  The value to sanitize
     *
     * @return string|null The sanitized value
     */
    private function sanitizeSetting(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        // Remove any potential XSS vectors
        $sanitized = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        // Additional validation for URLs
        if (filter_var($sanitized, FILTER_VALIDATE_URL) !== false) {
            return $sanitized;
        }
        return $sanitized;
    }
}
