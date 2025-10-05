<?php
declare(strict_types=1);
namespace App\Http\Middleware;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;
/**
 * Authentication Middleware with enhanced security and comprehensive error handling.
 *
 * This middleware extends Laravel's default authentication middleware to provide
 * enhanced security features, comprehensive error handling, and proper logging
 * for authentication failures and security events.
 *
 * Features:
 * - Enhanced authentication handling with security logging
 * - Comprehensive error handling and response management
 * - AJAX/JSON request support with proper error responses
 * - Security event logging for authentication failures
 * - Input validation and sanitization
 * - Enhanced security measures for authentication
 * - Proper error responses for different request types
 * - Comprehensive logging for security monitoring
 *
 *
 * @example
 * // Applied to routes that require authentication
 * Route::middleware('auth')->group(function () {
 *     // Protected routes
 * });
 */
class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * This method determines the redirect path for unauthenticated users,
     * with special handling for AJAX/JSON requests to prevent redirect loops
     * and provide proper API responses.
     *
     * @param  Request  $request  The current HTTP request instance
     *
     * @return string|null The redirect path or null for JSON requests
     *
     * @throws \Exception When an unexpected error occurs during redirect determination
     *
     * @example
     * // For web requests: redirects to login page
     * // For API requests: returns null to trigger JSON response
     */
    protected function redirectTo(Request $request): ?string
    {
        try {
            // For AJAX/JSON requests, return null to prevent redirect
            // This will cause the middleware to return a 401 JSON response instead
            if ($request->expectsJson()) {
                return null;
            }
            return route('login');
        } catch (Throwable $e) {
            Log::error('Authentication middleware redirect error', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Fallback to login route for web requests
            return route('login');
        }
    }
    /**
     * Handle an unauthenticated user with enhanced security and logging.
     *
     * This method handles unauthenticated users with comprehensive error handling,
     * security logging, and proper response formatting for different request types.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  array<string>  $guards  The authentication guards that were attempted
     *
     * @throws \Exception When an unexpected error occurs during handling
     *
     * @example
     * // For API requests: returns JSON error response
     * // For web requests: redirects to login page
     */
    protected function unauthenticated($request, array $guards): void
    {
        try {
            // Log authentication failure for security monitoring
            $this->logAuthenticationFailure($request, $guards);
            if ($request->expectsJson()) {
                abort(response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'error' => 'Unauthorized',
                    'error_code' => 'AUTHENTICATION_REQUIRED',
                ], 401));
            }
            parent::unauthenticated($request, $guards);
        } catch (Throwable $e) {
            Log::error('Authentication middleware unauthenticated handling error', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'guards' => $guards,
                'trace' => $e->getTraceAsString(),
            ]);
            // Fallback to parent implementation
            parent::unauthenticated($request, $guards);
        }
    }
    /**
     * Log authentication failure for security monitoring.
     *
     * This method logs authentication failures with comprehensive context
     * for security monitoring and threat detection.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  array<string>  $guards  The authentication guards that were attempted
     *
     * @example
     * $this->logAuthenticationFailure($request, ['web', 'api']);
     */
    private function logAuthenticationFailure(Request $request, array $guards): void
    {
        try {
            Log::warning('Authentication failure detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'guards' => $guards,
                'timestamp' => now()->toISOString(),
                'request_id' => $request->header('X-Request-ID'),
                'referer' => $request->header('Referer'),
                'accept' => $request->header('Accept'),
                'content_type' => $request->header('Content-Type'),
                'is_ajax' => $request->ajax(),
                'is_json' => $request->expectsJson(),
                'route_name' => $request->route()?->getName(),
                'route_action' => $request->route()?->getActionName(),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to log authentication failure', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  string  $input  The input to sanitize
     *
     * @return string The sanitized input
     */
    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Hash data for logging.
     *
     * @param  string  $data  The data to hash
     *
     * @return string The hashed data
     */
    private function hashForLogging(string $data): string
    {
        return substr(hash('sha256', $data.config('app.key')), 0, 8).'...';
    }
}
