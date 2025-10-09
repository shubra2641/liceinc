<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Check Maintenance Mode Middleware with enhanced security and error handling.
 *
 * This middleware handles maintenance mode functionality with comprehensive
 * security measures, proper error handling, and flexible access control
 * for admin areas, health endpoints, and static assets.
 *
 * Features:
 * - Maintenance mode detection from database settings
 * - Admin area access bypass during maintenance
 * - Health endpoint access for monitoring systems
 * - Static asset access for proper page rendering
 * - Comprehensive error handling and logging
 * - Security validation for all route patterns
 * - Graceful fallback when settings are unavailable
 *
 * @example
 * // Register in Kernel.php
 * protected $middleware = [
 *     \App\Http\Middleware\CheckMaintenanceMode::class,
 * ];
 */
class CheckMaintenanceMode
{
    /**
     * Handle an incoming request with comprehensive maintenance mode validation.
     *
     * Processes incoming requests and applies maintenance mode restrictions
     * with proper security validation, error handling, and access control
     * for admin areas, health endpoints, and static assets.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response|mixed The response or next middleware result
     *
     * @throws \Exception When maintenance mode processing fails
     *
     * @example
     * // Middleware automatically processes all requests
     * // Returns 503 maintenance page when maintenance mode is enabled
     * // Allows admin, health, and asset routes to function normally
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Validate request
            // Request is already validated by type hint
            // Check maintenance mode status with error handling
            $isMaintenance = $this->checkMaintenanceMode();
            if ($isMaintenance) {
                // Validate and check access permissions
                $hasAccess = $this->validateAccessPermissions($request);
                if (! $hasAccess) {
                    // Return maintenance page with proper HTTP status
                    return $this->returnMaintenancePage();
                }
            }
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error in CheckMaintenanceMode middleware', [
                'url' => $request->url() ?: 'unknown',
                'method' => $request->method() ?: 'unknown',
                'user_agent' => $request->userAgent() ?: 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // In case of error, allow request to proceed to prevent site blocking
            return $next($request);
        }
    }
    /**
     * Check maintenance mode status from database settings.
     *
     * Retrieves maintenance mode status from database with proper error handling
     * and fallback mechanisms to ensure site availability during database issues.
     *
     * @return bool True if maintenance mode is enabled, false otherwise
     */
    private function checkMaintenanceMode(): bool
    {
        try {
            $isMaintenance = (bool)Setting::get('maintenance_mode', false);
            return $isMaintenance;
        } catch (\Throwable $e) {
            // If settings table is not ready, do not block the site
            Log::warning('Settings table not available, maintenance mode disabled', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    /**
     * Validate access permissions for maintenance mode bypass.
     *
     * Checks if the current request should be allowed to bypass maintenance mode
     * restrictions based on admin access, health endpoints, and static assets.
     *
     * @param  Request  $request  The HTTP request to validate
     *
     * @return bool True if access should be allowed, false otherwise
     */
    private function validateAccessPermissions(Request $request): bool
    {
        try {
            // Allow admin area and its assets to function normally
            $isAdminPath = $this->isAdminPath($request);
            // Allow access to debug/health endpoints
            $isHealth = $this->isHealthEndpoint($request);
            // Allow access to static assets and essential files
            $isAllowlisted = $this->isAllowlistedPath($request);
            return $isAdminPath || $isHealth || $isAllowlisted;
        } catch (\Exception $e) {
            Log::error('Error validating access permissions', [
                'url' => $request->url() ?: 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // In case of error, deny access to be safe
            return false;
        }
    }
    /**
     * Check if request is for admin area.
     *
     * @param  Request  $request  The HTTP request to check
     *
     * @return bool True if admin path, false otherwise
     */
    private function isAdminPath(Request $request): bool
    {
        try {
            $isAdminPath = $request->is('admin*');
            if (! $isAdminPath && $request->route()) {
                $routeName = $request->route()->getName() ?? '';
                $isAdminPath = str_starts_with($routeName, 'admin.');
            }
            return $isAdminPath;
        } catch (\Exception $e) {
            Log::error('Error checking admin path', [
                'url' => $request->url() ?: 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    /**
     * Check if request is for health endpoints.
     *
     * @param  Request  $request  The HTTP request to check
     *
     * @return bool True if health endpoint, false otherwise
     */
    private function isHealthEndpoint(Request $request): bool
    {
        try {
            return $request->is('up') ||
                   $request->is('health') ||
                   $request->is('health/*');
        } catch (\Exception $e) {
            Log::error('Error checking health endpoint', [
                'url' => $request->url() ?: 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    /**
     * Check if request is for allowlisted static assets.
     *
     * @param  Request  $request  The HTTP request to check
     *
     * @return bool True if allowlisted path, false otherwise
     */
    private function isAllowlistedPath(Request $request): bool
    {
        try {
            $allowlist = [
                'storage/*',
                'assets/*',
                'public/*',
                'favicon.ico',
                'robots.txt',
                'sitemap.xml',
            ];
            foreach ($allowlist as $pattern) {
                if ($request->is($pattern)) {
                    return true;
                }
            }
            return false;
        } catch (\Exception $e) {
            Log::error('Error checking allowlisted path', [
                'url' => $request->url() ?: 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
    /**
     * Return maintenance page response.
     *
     * @return Response The maintenance page response with 503 status
     */
    private function returnMaintenancePage(): Response
    {
        try {
            return response()->view('maintenance', [], 503);
        } catch (\Exception $e) {
            Log::error('Error returning maintenance page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Fallback to simple text response
            return response('Service temporarily unavailable. Please try again later.', 503);
        }
    }
}
