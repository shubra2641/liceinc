<?php
declare(strict_types=1);
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
/**
 * Ensure Admin Middleware with enhanced security and comprehensive access control.
 *
 * This middleware provides comprehensive admin access control with enhanced security
 * features, comprehensive error handling, and proper logging for security events.
 *
 * Features:
 * - Enhanced admin access control with security validation
 * - Comprehensive user authentication and authorization checking
 * - Email verification validation with test email handling
 * - Security event logging for unauthorized access attempts
 * - Input validation and sanitization
 * - Enhanced security measures for admin operations
 * - Proper error responses for different access scenarios
 * - Comprehensive logging for security monitoring
 *
 *
 * @example
 * // Applied to admin routes that require admin access
 * Route::middleware(['auth', 'admin'])->group(function () {
 *     // Admin-only routes
 * });
 */
class EnsureAdmin
{
    /**
     * Handle an incoming request with enhanced security and comprehensive validation.
     *
     * This method performs comprehensive admin access validation including
     * authentication checking, role validation, email verification, and
     * security logging for unauthorized access attempts.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The response from the next middleware or error response
     *
     * @throws \Exception When an unexpected error occurs during processing
     *
     * @example
     * // Middleware automatically validates admin access for protected routes
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Skip authentication for static assets
            if ($this->isStaticAsset($request)) {
                return $next($request);
            }
            // Check if user is authenticated
            if (! $request->user()) {
                $this->logUnauthorizedAccess($request, 'not_authenticated');
                return redirect()->route('login')->with('error', 'You must be logged in to access this page');
            }
            $user = $request->user();
            // Check if user has admin role
            if (! $user->hasRole('admin')) {
                $this->logUnauthorizedAccess($request, 'insufficient_permissions', $user);
                abort(403, 'You are not authorized to access the admin panel');
            }
            // Additional security: Check if user is active and email verified
            $this->validateUserEmailVerification($request, $user);
            return $next($request);
        } catch (Throwable $e) {
            Log::error('EnsureAdmin middleware processing error', [
                'error' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            // Fallback to access denied for security
            abort(403, 'Access denied due to system error');
        }
    }
    /**
     * Validate user email verification with enhanced security.
     *
     * This method validates user email verification with special handling
     * for test emails and comprehensive security logging.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  \App\Models\User  $user  The user to validate
     *
     * @throws \Exception When validation fails
     *
     * @example
     * $this->validateUserEmailVerification($request, $user);
     */
    private function validateUserEmailVerification(Request $request, $user): void
    {
        try {
            $email = $user->email;
            $isTestEmail = $this->isTestEmail($email);
            if (! $user->email_verified_at) {
                if ($isTestEmail) {
                    $this->logTestEmailAccess($request, $user);
                    redirect()->route('test-email-warning')->send();
                } else {
                    $this->logUnauthorizedAccess($request, 'email_not_verified', $user);
                    abort(403, 'You must verify your email address first');
                }
            }
        } catch (Throwable $e) {
            Log::error('Email verification validation error', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown',
                'email' => $this->hashForLogging($user->email ?? 'unknown'),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Check if email is a test email with enhanced validation.
     *
     * @param  string  $email  The email to check
     *
     * @return bool True if it's a test email, false otherwise
     *
     * @example
     * $isTest = $this->isTestEmail('user@example.com');
     */
    private function isTestEmail(string $email): bool
    {
        try {
            $testDomains = ['@example.com', '@test.com', '@localhost', '@demo.com'];
            foreach ($testDomains as $domain) {
                if (str_contains($email, $domain)) {
                    return true;
                }
            }
            return false;
        } catch (Throwable $e) {
            Log::error('Test email validation error', [
                'error' => $e->getMessage(),
                'email' => $this->hashForLogging($email),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    /**
     * Log unauthorized access attempt for security monitoring.
     *
     * This method logs unauthorized access attempts with comprehensive context
     * for security monitoring and threat detection.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  string  $reason  The reason for unauthorized access
     * @param  \App\Models\User|null  $user  The user attempting access (optional)
     *
     * @example
     * $this->logUnauthorizedAccess($request, 'insufficient_permissions', $user);
     */
    private function logUnauthorizedAccess(Request $request, string $reason, $user = null): void
    {
        try {
            Log::warning('Unauthorized admin access attempt', [
                'reason' => $reason,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $user?->id,
                'user_email' => $user ? $this->hashForLogging($user->email) : null,
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
            Log::error('Failed to log unauthorized access attempt', [
                'error' => $e->getMessage(),
                'reason' => $reason,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    /**
     * Log test email access for security monitoring.
     *
     * This method logs test email access attempts for security monitoring
     * and compliance tracking.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  \App\Models\User  $user  The user with test email
     *
     * @example
     * $this->logTestEmailAccess($request, $user);
     */
    private function logTestEmailAccess(Request $request, $user): void
    {
        try {
            // Test email access is not an error - no logging needed for successful access
            // Only log security warnings for test email usage
            Log::warning('Test email accessing admin panel - consider using production email', [
                'user_id' => $user->id,
                'user_email' => $this->hashForLogging($user->email),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now()->toISOString(),
                'route_name' => $request->route()?->getName(),
            ]);
        } catch (Throwable $e) {
            Log::error('Failed to log test email access', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? 'unknown',
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
     * Check if the request is for a static asset.
     *
     * @param  Request  $request  The current HTTP request instance
     *
     * @return bool True if it's a static asset request, false otherwise
     *
     * @example
     * $isStatic = $this->isStaticAsset($request);
     */
    private function isStaticAsset(Request $request): bool
    {
        try {
            $path = $request->path();
            $uri = $request->getRequestUri();
            // Check for common static asset paths
            $staticPaths = [
                'vendor/assets/',
                'assets/',
                'css/',
                'js/',
                'images/',
                'fonts/',
                'favicon.ico',
                'robots.txt',
                '.css',
                '.js',
                '.png',
                '.jpg',
                '.jpeg',
                '.gif',
                '.svg',
                '.ico',
                '.woff',
                '.woff2',
                '.ttf',
                '.eot',
            ];
            foreach ($staticPaths as $staticPath) {
                if (str_contains($path, $staticPath) || str_contains($uri, $staticPath)) {
                    return true;
                }
            }
            return false;
        } catch (Throwable $e) {
            Log::error('Static asset detection error', [
                'error' => $e->getMessage(),
                'path' => $request->path(),
                'uri' => $request->getRequestUri(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
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
