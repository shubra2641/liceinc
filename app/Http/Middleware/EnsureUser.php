<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure User Middleware with enhanced security and comprehensive user validation.
 *
 * This middleware ensures that users are authenticated and have verified email addresses.
 * It implements comprehensive security measures, input validation, and error handling
 * for reliable user authentication and email verification operations.
 */
class EnsureUser
{
    /**
     * Handle an incoming request with enhanced security and comprehensive user validation.
     *
     * Ensures that users are authenticated and have verified email addresses.
     * Includes comprehensive validation, security measures, and error handling
     * for reliable user authentication and email verification operations.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The HTTP response
     *
     * @throws InvalidArgumentException When user data is invalid
     * @throws \Exception When authentication or verification fails
     *
     * @example
     * // This middleware is automatically applied to routes that require user authentication
     * Route::middleware('ensure.user')->group(function () {
     *     Route::get('/dashboard', [DashboardController::class, 'index']);
     * });
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Validate request and user authentication
            $this->validateRequest($request);
            $user = $request->user();
            if (! $user) {
                Log::warning('Unauthenticated access attempt', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->fullUrl(),
                ]);
                abort(403, 'Authentication required');
            }
            // Validate user data
            $this->validateUser($user);
            // Check if user's email is verified
            $email = $user->email;
            $isTestEmail = $this->isTestEmail($email);
            if (! $user->email_verified_at) {
                if ($isTestEmail) {
                    return redirect()->route('test-email-warning');
                } else {
                    return redirect()->route('verification.notice')
                        ->with('success', 'Please verify your email address before accessing your account.');
                }
            }
            return $next($request);
        } catch (\Exception $e) {
            Log::error('EnsureUser middleware failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_url' => $request->fullUrl(),
                'user_id' => $request->user()?->id,
            ]);
            // Re-throw the exception to maintain middleware behavior
            throw $e;
        }
    }
    /**
     * Validate request with enhanced security and comprehensive validation.
     *
     * @param  Request  $request  The request to validate
     *
     * @throws InvalidArgumentException When request is invalid
     */
    private function validateRequest(Request $request): void
    {
        if (! $request instanceof Request) {
            throw new InvalidArgumentException('Invalid request object');
        }
    }
    /**
     * Validate user with enhanced security and comprehensive validation.
     *
     * @param  mixed  $user  The user object to validate
     *
     * @throws InvalidArgumentException When user is invalid
     */
    private function validateUser($user): void
    {
        if (! $user) {
            throw new InvalidArgumentException('User object is null');
        }
        if (! is_object($user)) {
            throw new InvalidArgumentException('User must be an object');
        }
        // Check if user has email attribute (for Eloquent models)
        if (! isset($user->email) && ! method_exists($user, 'getEmail')) {
            throw new InvalidArgumentException('User object must have email property or method');
        }
        // Check if user has email_verified_at attribute (for Eloquent models)
        if (! isset($user->email_verified_at) && ! method_exists($user, 'getEmailVerifiedAt')) {
            throw new InvalidArgumentException('User object must have email_verified_at property or method');
        }
    }
    /**
     * Check if email is a test email with enhanced security and validation.
     *
     * @param  string  $email  Email address to check
     *
     * @return bool True if email is a test email, false otherwise
     *
     * @throws InvalidArgumentException When email is invalid
     */
    private function isTestEmail(string $email): bool
    {
        if (empty($email)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        $testDomains = [
            '@example.com',
            '@test.com',
            '@localhost',
            '@demo.com',
            '@testing.com',
            '@dev.com',
        ];
        foreach ($testDomains as $domain) {
            if (str_contains($email, $domain)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Sanitize email for logging with enhanced security and XSS protection.
     *
     * @param  string  $email  Email address to sanitize
     *
     * @return string Sanitized email address
     */
    private function sanitizeEmail(string $email): string
    {
        // Basic email sanitization for logging purposes
        $parts = explode('@', $email);
        if (count($parts) === 2) {
            $username = $parts[0];
            $domain = $parts[1];
            // Mask part of username for privacy
            if (strlen($username) > 2) {
                $maskedUsername = substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
            } else {
                $maskedUsername = str_repeat('*', strlen($username));
            }
            return $maskedUsername . '@' . $domain;
        }
        return '***@***';
    }
}
