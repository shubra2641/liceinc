<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Demo Mode Middleware with enhanced security.
 *
 * This middleware handles demo mode functionality, preventing destructive operations
 * while allowing read-only access and authentication. It provides comprehensive
 * protection for demo environments with enhanced security measures.
 *
 * Features:
 * - Demo mode detection and enforcement
 * - Destructive operation blocking
 * - Read-only AJAX request handling
 * - Authentication route bypassing
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling and logging
 * - Route-based action detection
 * - Method-based operation blocking
 */
class DemoModeMiddleware
{
    /**
     * Handle an incoming request with enhanced security.
     *
     * Processes incoming requests and blocks destructive operations in demo mode
     * while allowing read-only access and authentication functionality.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The HTTP response
     *
     * @throws \Exception When demo mode operations fail
     *
     * @example
     * // Blocks destructive operations in demo mode:
     * // POST /admin/users/1/delete -> 403 Forbidden
     * // PUT /admin/settings/update -> 403 Forbidden
     * // GET /admin/users -> 200 OK (read-only allowed)
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if demo mode is enabled
            if (! $this->isDemoModeEnabled()) {
                $response = $next($request);
                /** @var Response $typedResponse */
                $typedResponse = $response;

                return $typedResponse;
            }
            // Skip demo mode for installation routes
            if ($request->routeIs('install.*')) {
                $response = $next($request);
                /** @var Response $typedResponse */
                $typedResponse = $response;

                return $typedResponse;
            }
            // Skip demo mode for login and authentication routes
            if ($this->isAuthenticationRoute($request)) {
                $response = $next($request);
                /** @var Response $typedResponse */
                $typedResponse = $response;

                return $typedResponse;
            }
            // Skip demo mode for AJAX requests that are read-only
            if ($request->ajax() && $this->isReadOnlyAjaxRequest($request)) {
                $response = $next($request);
                /** @var Response $typedResponse */
                $typedResponse = $response;

                return $typedResponse;
            }
            // Block destructive HTTP methods (but allow login POST)
            if ($this->isDestructiveMethod($request->method()) && ! $this->isLoginPost($request)) {
                $response = $this->handleDemoModeBlock($request);
                /** @var Response $typedResponse */
                $typedResponse = $response;

                return $typedResponse;
            }
            // Block destructive form submissions
            if ($request->isMethod('POST') && $this->isDestructiveAction($request)) {
                $response = $this->handleDemoModeBlock($request);
                /** @var Response $typedResponse */
                $typedResponse = $response;

                return $typedResponse;
            }
            $response = $next($request);
            /** @var Response $typedResponse */
            $typedResponse = $response;

            return $typedResponse;
        } catch (\Exception $e) {
            Log::error('Demo mode middleware failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
            // In case of error, allow the request to proceed
            $response = $next($request);
            /** @var Response $typedResponse */
            $typedResponse = $response;

            return $typedResponse;
        }
    }

    /**
     * Check if demo mode is enabled with enhanced validation.
     *
     * @return bool True if demo mode is enabled, false otherwise
     */
    private function isDemoModeEnabled(): bool
    {
        $demoMode = config('app.demo_mode', false);

        return $demoMode === true || $demoMode === '1' || $demoMode === 'true';
    }

    /**
     * Check if the request is for authentication routes.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return bool True if authentication route, false otherwise
     */
    private function isAuthenticationRoute(Request $request): bool
    {
        $authRoutes = [
            'login', 'logout', 'password.*', 'verification.*', 'register', 'auth.*',
        ];
        $authPaths = [
            'login', 'logout', 'register', 'forgot-password', 'reset-password*',
            'verify-email*', 'confirm-password',
        ];
        // Check route names
        foreach ($authRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }
        // Check paths
        foreach ($authPaths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }
        // Check specific paths
        $specificPaths = ['login', 'logout', 'register'];
        foreach ($specificPaths as $path) {
            if ($request->path() === $path) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the HTTP method is destructive with enhanced validation.
     *
     * @param  string  $method  The HTTP method to check
     *
     * @return bool True if destructive, false otherwise
     */
    private function isDestructiveMethod(string $method): bool
    {
        $destructiveMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];

        return in_array(strtoupper($method), $destructiveMethods, true);
    }

    /**
     * Check if this is a login POST request with enhanced validation.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return bool True if login POST, false otherwise
     */
    private function isLoginPost(Request $request): bool
    {
        if (! $request->isMethod('POST')) {
            return false;
        }
        $loginPaths = ['login'];
        $loginRoutes = ['login'];
        // Check paths
        foreach ($loginPaths as $path) {
            if ($request->path() === $path) {
                return true;
            }
        }
        // Check routes
        foreach ($loginRoutes as $route) {
            if ($request->routeIs($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the request is a destructive action with enhanced validation.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return bool True if destructive, false otherwise
     */
    private function isDestructiveAction(Request $request): bool
    {
        $path = $this->sanitizeInput($request->path());
        $action = $this->sanitizeInput(is_string($request->get('_method', $request->method())) ? $request->get('_method', $request->method()) : null);
        // List of destructive actions
        $destructiveActions = [
            'create', 'store', 'edit', 'update', 'destroy', 'delete',
            'activate', 'deactivate', 'approve', 'reject', 'ban', 'unban',
            'import', 'export', 'backup', 'restore', 'migrate', 'seed',
            'install', 'uninstall', 'enable', 'disable', 'toggle',
        ];
        // Check if the action is destructive
        foreach ($destructiveActions as $actionName) {
            if (($path && str_contains($path, $actionName)) || ($action && str_contains($action, $actionName))) {
                return true;
            }
        }
        // Check for specific destructive routes
        $destructiveRoutes = [
            'admin/users/*/delete',
            'admin/settings/update',
            'admin/email-templates/*/update',
            'admin/license/*/update',
            'admin/products/*/update',
            'admin/categories/*/update',
            'admin/orders/*/update',
            'admin/customers/*/update',
        ];
        foreach ($destructiveRoutes as $route) {
            if ($request->is($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if AJAX request is read-only with enhanced validation.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return bool True if read-only, false otherwise
     */
    private function isReadOnlyAjaxRequest(Request $request): bool
    {
        $path = $this->sanitizeInput($request->path());
        // Allow read-only AJAX requests
        $readOnlyActions = [
            'search', 'filter', 'sort', 'paginate', 'load', 'fetch',
            'get', 'show', 'view', 'index', 'list', 'data',
        ];
        foreach ($readOnlyActions as $action) {
            if ($path && str_contains($path, $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle demo mode blocking with enhanced security.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return Response The blocked response
     */
    private function handleDemoModeBlock(Request $request): Response
    {
        $message = 'Demo Mode: This action is not allowed in demo mode. You can only view and browse the system.';
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'demo_mode' => true,
            ], 403);
        }

        return redirect()->back()
            ->with('error', $message)
            ->with('demo_mode', true);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  string|null  $input  The input to sanitize
     *
     * @return string|null The sanitized input
     */
    private function sanitizeInput(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
