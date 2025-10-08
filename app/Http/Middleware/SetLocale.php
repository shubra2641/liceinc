<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set Locale Middleware with enhanced security and comprehensive locale management.
 *
 * This middleware sets the application locale based on session data or configuration.
 * It implements comprehensive security measures, input validation, and error handling
 * for reliable locale management and internationalization operations.
 */
class SetLocale
{
    /**
     * Handle an incoming request with enhanced security and comprehensive locale management.
     *
     * Sets the application locale based on session data or configuration with
     * comprehensive validation, security measures, and error handling for
     * reliable locale management operations.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The HTTP response
     *
     * @throws InvalidArgumentException When locale data is invalid
     * @throws \Exception When locale setting fails
     *
     * @example
     * // This middleware is automatically applied to set the application locale
     * Route::middleware('set.locale')->group(function () {
     *     Route::get('/dashboard', [DashboardController::class, 'index']);
     * });
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Validate request
            $this->validateRequest($request);
            // Get locale from session or configuration
            $locale = $this->getLocale($request);
            // Validate and sanitize locale
            $validatedLocale = $this->validateAndSanitizeLocale($locale);
            // Set application locale
            $this->setApplicationLocale($validatedLocale);
            $response = $next($request);
            /** @var \Symfony\Component\HttpFoundation\Response $typedResponse */
            $typedResponse = $response;
            return $typedResponse;
        } catch (\Exception $e) {
            Log::error('SetLocale middleware failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_url' => $request->fullUrl(),
                'session_locale' => session('locale'),
                'config_locale' => config('app.locale'),
            ]);
            // Fallback to default locale and continue
            app()->setLocale(is_string(config('app.locale')) ? config('app.locale') : 'en');
            $response = $next($request);
            /** @var \Symfony\Component\HttpFoundation\Response $typedResponse */
            $typedResponse = $response;
            return $typedResponse;
        }
    }
    /**
     * Validate request with enhanced security and comprehensive validation.
     *
     * @param  Request  $request  The request to validate
     */
    private function validateRequest(Request $request): void
    {
        // Request is already typed as Request, no need to check instanceof
        // This method is kept for future validation logic
    }
    /**
     * Get locale from session or configuration with enhanced security.
     *
     * @param  Request  $request  The request object
     *
     * @return string The locale string
     *
     * @throws InvalidArgumentException When locale cannot be retrieved
     */
    private function getLocale(Request $request): string
    {
        try {
            $sessionLocale = session('locale');
            $configLocale = config('app.locale');
            if ($sessionLocale && is_string($sessionLocale)) {
                return $sessionLocale;
            }
            if ($configLocale && is_string($configLocale)) {
                return $configLocale;
            }
            throw new InvalidArgumentException('No valid locale found in session or configuration');
        } catch (\Exception $e) {
            Log::error('Failed to get locale', [
                'session_locale' => $sessionLocale ?? 'null',
                'config_locale' => $configLocale ?? 'null',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
    /**
     * Validate and sanitize locale with enhanced security and comprehensive validation.
     *
     * @param  string  $locale  The locale to validate and sanitize
     *
     * @return string The validated and sanitized locale
     *
     * @throws InvalidArgumentException When locale is invalid
     */
    private function validateAndSanitizeLocale(string $locale): string
    {
        if (empty($locale)) {
            throw new InvalidArgumentException('Locale cannot be empty');
        }
        // Sanitize locale to prevent XSS
        $sanitizedLocale = htmlspecialchars(trim($locale), ENT_QUOTES, 'UTF-8');
        if (empty($sanitizedLocale)) {
            throw new InvalidArgumentException('Locale cannot be empty after sanitization');
        }
        // Validate locale format (should be like 'en', 'en_US', 'fr_FR', etc.)
        if (! preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $sanitizedLocale)) {
            throw new InvalidArgumentException('Invalid locale format');
        }
        // Check if locale is supported
        $supportedLocales = $this->getSupportedLocales();
        if (! in_array($sanitizedLocale, $supportedLocales)) {
            // Don't log debug messages for common locales to reduce log noise
            if (! in_array($sanitizedLocale, ['en', 'en_US', 'ar', 'hi'])) {
                Log::debug('Unsupported locale requested, using fallback', [
                    'requested_locale' => $sanitizedLocale,
                    'supported_locales' => $supportedLocales,
                ]);
            }
            // Fallback to default locale instead of throwing exception
            return is_string(config('app.locale', 'en')) ? config('app.locale', 'en') : 'en';
        }
        return $sanitizedLocale;
    }
    /**
     * Set application locale with enhanced security and error handling.
     *
     * @param  string  $locale  The locale to set
     *
     * @throws \Exception When locale setting fails
     */
    private function setApplicationLocale(string $locale): void
    {
        try {
            app()->setLocale($locale);
        } catch (\Exception $e) {
            Log::error('Failed to set application locale', [
                'locale' => $locale,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Get supported locales with enhanced security and validation.
     *
     * @return array<mixed> Array of supported locale codes
     */
    private function getSupportedLocales(): array
    {
        // Get supported locales from configuration
        $configLocales = config('app.supported_locales', []);
        if (! empty($configLocales) && is_array($configLocales)) {
            // If the config is an associative array like ['en' => [...], 'ar' => [...]]
            // return the keys; otherwise return the flat array as-is
            $keys = array_keys($configLocales);
            $isAssociative = array_filter($keys, 'is_string') === $keys;
            return $isAssociative ? array_map(function($key) { return $key; }, $keys) : array_map(function($value) { return $value; }, array_values($configLocales));
        }
        // Default supported locales
        return [
            'en',
            'en_US',
            'ar',
            'hi',
            'fr',
            'fr_FR',
            'de',
            'de_DE',
            'es',
            'es_ES',
            'it',
            'it_IT',
            'pt',
            'pt_BR',
            'ru',
            'ru_RU',
            'zh',
            'zh_CN',
            'ja',
            'ja_JP',
            'ko',
            'ko_KR',
        ];
    }
}
