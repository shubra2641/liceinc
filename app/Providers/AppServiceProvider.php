<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\ViewComposers\LayoutComposer;
use App\Services\Email\EmailServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services
     */
    public function register(): void
    {
        try {
            // Register Email Service Provider
            $this->app->register(EmailServiceProvider::class);

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
                app('log')->error('Failed to register service providers: ' . $e->getMessage());
            }
            // Re-throw if it's a critical error
            if ($e instanceof \InvalidArgumentException) {
                throw $e;
            }
        }
    }
    /**
     * Bootstrap application services
     */
    public function boot(): void
    {
        $this->configureAppUrl();
        $this->registerViewComposers();
        $this->configurePagination();
        $this->configureRateLimiters();
    }

    /**
     * Configure application URL
     */
    private function configureAppUrl(): void
    {
        if (!app()->runningInConsole()) {
            $currentHost = request()->getHost();
            $currentScheme = request()->getScheme();
            $appUrl = config('app.url');
            $parsedAppUrl = parse_url($appUrl);
            
            if ($currentHost !== ($parsedAppUrl['host'] ?? 'localhost') || 
                $currentScheme !== ($parsedAppUrl['scheme'] ?? 'http')) {
                $path = trim(dirname(request()->getScriptName()), '/');
                $baseUrl = $currentScheme . '://' . $currentHost;
                if ($path && $path !== '.') {
                    $baseUrl .= '/' . $path;
                }
                config(['app.url' => $baseUrl]);
                app('url')->useOrigin($baseUrl);
            }
        }
    }

    /**
     * Register view composers
     */
    private function registerViewComposers(): void
    {
        View::composer('*', LayoutComposer::class);
    }
    /**
     * Configure pagination
     */
    private function configurePagination(): void
    {
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');
    }
    /**
     * Configure rate limiters
     */
    private function configureRateLimiters(): void
    {
        RateLimiter::for('auth', function ($request) {
            return Limit::perMinute(100)->by($request->ip());
        });

        RateLimiter::for('api', function ($request) {
            $user = $request->user();
            return Limit::perMinute(300)->by($user?->id ?: $request->ip());
        });

        RateLimiter::for('web', function ($request) {
            return Limit::perMinute(200)->by($request->ip());
        });
    }
}
