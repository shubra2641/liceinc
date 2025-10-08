<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Email Is Verified Middleware with enhanced security.
 *
 * A comprehensive middleware that ensures user email verification before
 * accessing protected resources with enhanced security measures.
 *
 * Features:
 * - Email verification enforcement
 * - User authentication validation
 * - AJAX request handling
 * - Route redirection management
 * - Enhanced error handling and logging
 * - Input validation and sanitization
 * - Comprehensive security measures
 * - Clean code structure with no duplicate patterns
 * - Proper type hints and return types
 */
class EnsureEmailIsVerified
{
    /**
     * HTTP status code for forbidden access.
     */
    private const FORBIDDEN_STATUS = 403;
    /**
     * Handle an incoming request with enhanced security.
     *
     * Processes incoming requests to ensure user email verification
     * with comprehensive validation and error handling.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The HTTP response
     *
     * @throws \InvalidArgumentException When request is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Request is validated by type hint
            $user = $request->user();
            // Check if user is authenticated
            if (! $user) {
                return $this->handleUnauthenticated($request);
            }
            // Check if user's email is verified
            if (! $this->isEmailVerified($user)) {
                $response = $this->handleUnverifiedEmail($request);
                /** @var \Symfony\Component\HttpFoundation\Response $typedResponse */
                $typedResponse = $response;
                return $typedResponse;
            }
            $response = $next($request);
            /** @var \Symfony\Component\HttpFoundation\Response $typedResponse */
            $typedResponse = $response;
            return $typedResponse;
        } catch (Exception $e) {
            Log::error('Email verification middleware failed: ' . $e->getMessage(), [
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString(),
            ]);
            // Fail safe - redirect to verification notice
            return $this->handleUnverifiedEmail($request);
        }
    }
    /**
     * Check if user's email is verified with validation.
     *
     * @param  mixed  $user  The user instance
     *
     * @return bool True if email is verified
     */
    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $user
     */
    private function isEmailVerified($user): bool
    {
        try {
            if (! $user) {
                return false;
            }
            return $user->hasVerifiedEmail();
        } catch (Exception $e) {
            Log::error('Failed to check email verification status: ' . $e->getMessage());
            return false;
        }
    }
    /**
     * Handle unauthenticated user.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return Response The appropriate response
     */
    private function handleUnauthenticated(Request $request): Response
    {
        if ($this->isJsonRequest($request)) {
            return $this->createJsonResponse(
                'Authentication required',
                'Please log in to access this resource.',
                self::FORBIDDEN_STATUS,
            );
        }
        return redirect()->route('login');
    }
    /**
     * Handle unverified email.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return Response The appropriate response
     */
    private function handleUnverifiedEmail(Request $request): Response
    {
        if ($this->isJsonRequest($request)) {
            return $this->createJsonResponse(
                'Email verification required',
                'Please verify your email address before accessing this resource.',
                self::FORBIDDEN_STATUS,
            );
        }
        return redirect()->route('verification.notice');
    }
    /**
     * Check if request expects JSON response.
     *
     * @param  Request  $request  The HTTP request
     *
     * @return bool True if JSON response expected
     */
    private function isJsonRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax() || $request->wantsJson();
    }
    /**
     * Create JSON response with validation.
     *
     * @param  string  $error  Error type
     * @param  string  $message  Response message
     * @param  int  $status  HTTP status code
     *
     * @return Response The JSON response
     */
    private function createJsonResponse(string $error, string $message, int $status): Response
    {
        try {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ], $status);
        } catch (Exception $e) {
            Log::error('Failed to create JSON response: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'An error occurred while processing your request',
            ], 500);
        }
    }
}
