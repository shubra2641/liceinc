<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withProviders([
        App\Providers\EnvatoSocialiteProvider::class,
    ])
    ->withRouting(
        web: [__DIR__.'/../routes/web.php', __DIR__.'/../routes/auth.php', __DIR__.'/../routes/install.php'],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            App\Http\Middleware\SetLocale::class,
            // Increase post size limit for file uploads
            App\Http\Middleware\IncreasePostSizeLimit::class,
            // Check maintenance mode and show maintenance page for non-admin routes
            App\Http\Middleware\CheckMaintenanceMode::class,
            // Check installation status
            App\Http\Middleware\CheckInstallation::class,
            // Demo mode middleware - must be after installation check
            App\Http\Middleware\DemoModeMiddleware::class,
            // Check license verification
        ]);

        $middleware->api(append: [
            App\Http\Middleware\ApiTrackingMiddleware::class,
        ]);

        $middleware->alias([
            'admin' => App\Http\Middleware\EnsureAdmin::class,
            'user' => App\Http\Middleware\EnsureUser::class,
        ]);

        // Exclude API routes from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);

        // Configure rate limiters
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
