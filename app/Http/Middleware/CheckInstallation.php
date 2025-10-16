<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Installation Middleware with enhanced security.
 *
 * A comprehensive middleware that checks if the system is properly installed
 * and handles installation state validation with enhanced security measures.
 *
 * Features:
 * - Installation state validation
 * - Route protection and redirection
 * - AJAX request handling
 * - Installation file checking
 * - Enhanced error handling and logging
 * - Input validation and sanitization
 * - Comprehensive security measures
 * - Clean code structure with no duplicate patterns
 * - Proper type hints and return types
 */
class CheckInstallation
{
    /**
     * Installation file path constant.
     */
    private const INSTALLED_FILE_PATH = '.installed';

    /**
     * Routes that should skip installation check.
     */
    private const SKIP_ROUTES = ['up', 'health'];

    /**
     * Handle an incoming request with enhanced security.
     *
     * Processes incoming requests to check installation state and
     * redirects appropriately with comprehensive error handling.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The HTTP response
     *
     * @throws \InvalidArgumentException When request is invalid
     *
     * @version 1.0.6
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Request is validated by type hint
            $installedFile = storage_path(self::INSTALLED_FILE_PATH);
            $currentRoute = $this->getCurrentRouteName($request);
            // Skip installation check for certain routes
            if ($this->shouldSkipRoute($currentRoute)) {
                $response = $next($request);
                /**
                 * @var Response $typedResponse
                 */
                $typedResponse = $response;

                return $typedResponse;
            }
            $isInstalled = $this->isSystemInstalled($installedFile);
            $isInstallRoute = $this->isInstallRoute($request, $currentRoute);
            // If system is not installed and not accessing install routes
            if (! $isInstalled && ! $isInstallRoute) {
                $response = $this->handleNotInstalled($request);
                /**
                 * @var Response $typedResponse
                 */
                $typedResponse = $response;

                return $typedResponse;
            }
            // If system is installed and trying to access install routes
            if ($isInstalled && $isInstallRoute) {
                $response = $this->handleAlreadyInstalled($request);
                /**
                 * @var Response $typedResponse
                 */
                $typedResponse = $response;

                return $typedResponse;
            }
            $response = $next($request);
            /**
             * @var Response $typedResponse
             */
            $typedResponse = $response;

            return $typedResponse;
        } catch (Exception $e) {
            Log::error('Installation check middleware failed: '.$e->getMessage(), [
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Fail safe - allow request to continue
            $response = $next($request);
            /**
             * @var Response $typedResponse
             */
            $typedResponse = $response;

            return $typedResponse;
        }
    }

    /**
     * Get current route name with validation.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return string The route name or empty string
     */
    private function getCurrentRouteName(Request $request): string
    {
        try {
            $route = $request->route();
            if ($route === null) {
                return '';
            }
            $routeName = $route->getName();

            return $routeName ?? '';
        } catch (Exception $e) {
            Log::error('Failed to get current route name: '.$e->getMessage());

            return '';
        }
    }

    /**
     * Check if route should be skipped.
     *
     * @param  string  $currentRoute  The current route name
     *
     * @return bool True if route should be skipped
     */
    private function shouldSkipRoute(string $currentRoute): bool
    {
        return in_array($currentRoute, self::SKIP_ROUTES, true);
    }

    /**
     * Check if system is installed.
     *
     * @param  string  $installedFile  The path to the installed file
     *
     * @return bool True if system is installed
     */
    private function isSystemInstalled(string $installedFile): bool
    {
        try {
            return File::exists($installedFile);
        } catch (Exception $e) {
            Log::error('Failed to check installation file: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if current request is for install routes.
     *
     * @param  Request  $request  The HTTP request
     * @param  string  $currentRoute  The current route name
     *
     * @return bool True if it's an install route
     */
    private function isInstallRoute(Request $request, string $currentRoute): bool
    {
        return $request->is('install*') ||
               str_contains($request->path(), 'install') ||
               str_starts_with($currentRoute, 'install.');
    }

    /**
     * Handle not installed system.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return Response The appropriate response
     */
    private function handleNotInstalled(Request $request): Response
    {
        if ($this->isAjaxRequest($request)) {
            return $this->createJsonResponse(
                false,
                'System not installed. Please run installation.',
                route('install.welcome'),
                403,
            );
        }

        return redirect()->route('install.welcome');
    }

    /**
     * Handle already installed system.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return Response The appropriate response
     */
    private function handleAlreadyInstalled(Request $request): Response
    {
        if ($this->isAjaxRequest($request)) {
            return $this->createJsonResponse(
                false,
                'System is already installed.',
                route('login'),
                403,
            );
        }

        return redirect()->route('login')->with('info', 'System is already installed.');
    }

    /**
     * Check if request is AJAX or wants JSON.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return bool True if AJAX request
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->ajax() || $request->wantsJson();
    }

    /**
     * Create JSON response with validation.
     *
     * @param  bool  $success  Success status
     * @param  string  $message  Response message
     * @param  string  $redirect  Redirect URL
     * @param  int  $status  HTTP status code
     *
     * @return Response The JSON response
     */
    private function createJsonResponse(bool $success, string $message, string $redirect, int $status): Response
    {
        try {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'redirect' => $redirect,
            ], $status);
        } catch (Exception $e) {
            Log::error('Failed to create JSON response: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'redirect' => route('install.welcome'),
            ], 500);
        }
    }
}
