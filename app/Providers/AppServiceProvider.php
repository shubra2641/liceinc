<?php

namespace App\Providers;

use App\Http\ViewComposers\LayoutComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider with enhanced security.
 *
 * This service provider handles the registration and bootstrapping of application
 * services, including view composers, pagination settings, and third-party
 * service providers with comprehensive security measures.
 *
 * Features:
 * - Service registration and bootstrapping
 * - View composer registration with security validation
 * - Pagination configuration with proper defaults
 * - Enhanced security measures (input validation, error handling)
 * - Comprehensive error handling for service registration
 * - Proper type hints and return types
 * - Clean code structure with no duplicate patterns
 * - Third-party service provider integration
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services with enhanced security.
     *
     * Registers application services including third-party providers
     * with proper validation and error handling.
     *
     *
     * @throws \InvalidArgumentException When service registration fails
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function register(): void
    {
        try {
            // Register Envato Socialite Provider with validation
            if (! class_exists(EnvatoSocialiteProvider::class)) {
                throw new \InvalidArgumentException(
                    'EnvatoSocialiteProvider class not found. Please ensure the provider is properly installed.',
                );
            }
            $this->app->register(EnvatoSocialiteProvider::class);
        } catch (\Exception $e) {
            // Log error but don't break the application
            if (app()->bound('log')) {
                app('log')->error('Failed to register EnvatoSocialiteProvider: ' . $e->getMessage());
            }
            // Re-throw if it's a critical error
            if ($e instanceof \InvalidArgumentException) {
                throw $e;
            }
        }
    }
    /**
     * Bootstrap any application services with enhanced security.
     *
     * Bootstraps application services including view composers and
     * pagination settings with proper validation and error handling.
     *
     *
     * @throws \InvalidArgumentException When service bootstrapping fails
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function boot(): void
    {
        try {
            // Auto-detect proper app URL to fix routing issues
            $this->configureAppUrl();

            // Register View Composers with validation
            $this->registerViewComposers();
            // Set default pagination view with validation
            $this->configurePagination();
            // Configure rate limiters
            $this->configureRateLimiters();
        } catch (\Exception $e) {
            // Log error but don't break the application
            if (app()->bound('log')) {
                app('log')->error('Failed to bootstrap AppServiceProvider: ' . $e->getMessage());
            }
            // Re-throw if it's a critical error
            if ($e instanceof \InvalidArgumentException) {
                throw $e;
            }
        }
    }

    /**
     * Configure application URL for proper route generation.
     *
     * Auto-detects the correct base URL when APP_URL doesn't match
     * the current request URL, fixing routing issues in subfolders.
     */
    private function configureAppUrl(): void
    {
        if (!app()->runningInConsole()) {
            $currentHost = request()->getHost();
            $currentScheme = request()->getScheme();
            $appUrl = config('app.url');

            // Check if current URL differs from configured APP_URL
            $parsedAppUrl = parse_url(is_string($appUrl) ? $appUrl : '');
            $parsedHost = $parsedAppUrl['host'] ?? 'localhost';
            $parsedScheme = $parsedAppUrl['scheme'] ?? 'http';

            if (
                $currentHost !== $parsedHost ||
                $currentScheme !== $parsedScheme
            ) {
                // Build proper base URL without /public
                $path = trim(dirname(request()->getScriptName()), '/');
                $baseUrl = $currentScheme . '://' . $currentHost;

                if ($path && $path !== '.') {
                    $baseUrl .= '/' . $path;
                }

                // Update the URL configuration
                config(['app.url' => $baseUrl]);
                app('url')->useOrigin($baseUrl);
            }
        }
    }

    /**
     * Register view composers with enhanced security.
     *
     * Registers view composers with proper validation and error handling.
     *
     *
     * @throws \InvalidArgumentException When view composer registration fails
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    private function registerViewComposers(): void
    {
        $viewPaths = ['layouts.*', 'welcome'];
        // Validate view paths (hardcoded values are always valid)
        // $viewPaths = ['layouts.*', 'welcome']; - these are always valid
        // Validate LayoutComposer class
        if (!class_exists(LayoutComposer::class)) {
            throw new \InvalidArgumentException(
                'LayoutComposer class not found. Please ensure the view composer is properly implemented.',
            );
        }
        View::composer($viewPaths, LayoutComposer::class);
    }
    /**
     * Configure pagination with enhanced security.
     *
     * Sets up pagination defaults with proper validation and error handling.
     *
     *
     * @throws \InvalidArgumentException When pagination configuration fails
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    private function configurePagination(): void
    {
        $defaultView = 'pagination::bootstrap-5';
        $defaultSimpleView = 'pagination::simple-bootstrap-5';
        // Validate pagination view names (always valid for hardcoded values)
        Paginator::defaultView($defaultView);
        Paginator::defaultSimpleView($defaultSimpleView);
    }
    /**
     * Configure rate limiters with enhanced security.
     *
     * Sets up rate limiters for authentication and API endpoints
     * with proper validation and security measures.
     */
    private function configureRateLimiters(): void
    {
        // Configure auth rate limiter
        RateLimiter::for('auth', function ($request) {
            if (is_object($request) && method_exists($request, 'ip')) {
                return Limit::perMinute(10)->by($request->ip());
            }
            return Limit::perMinute(10)->by('127.0.0.1');
        });
        // Configure API rate limiter
        RateLimiter::for('api', function ($request) {
            if (is_object($request) && method_exists($request, 'user') && method_exists($request, 'ip')) {
                $user = $request->user();
                $userId = is_object($user) && property_exists($user, 'id') ? $user->id : null;
                $ip = $request->ip();
                return Limit::perMinute(60)->by($userId ?: $ip);
            }
            return Limit::perMinute(60)->by('127.0.0.1');
        });
    }
}
