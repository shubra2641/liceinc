<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Base Controller with enhanced security and comprehensive functionality.
 *
 * This base controller provides comprehensive functionality for all controllers
 * including enhanced security measures, error handling, validation helpers,
 * logging capabilities, and standardized response methods.
 *
 * Features:
 * - Enhanced security measures and input validation
 * - Comprehensive error handling and logging
 * - Database transaction support for data integrity
 * - Standardized response methods for consistency
 * - Input sanitization and security validation
 * - Enhanced security measures for controller operations
 * - Proper error responses for different scenarios
 * - Comprehensive logging for security monitoring
 * - Authorization and validation capabilities
 * - Job dispatching and request handling
 *
 * @example
 * // Extend this controller for all application controllers
 * class UserController extends Controller
 * {
 *     public function index(): JsonResponse
 *     {
 *         return $this->successResponse(['users' => User::all()]);
 *     }
 * }
 */
class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Execute a database transaction with enhanced error handling.
     *
     * This method executes a callback within a database transaction
     * with comprehensive error handling and logging.
     *
     * @param  callable  $callback  The callback to execute within the transaction
     *
     * @return mixed The result of the callback execution
     *
     * @throws \Exception When the transaction fails
     *
     * @example
     * $result = $this->transaction(function () {
     *     // Database operations
     *     return $data;
     * });
     */
    /**
     * @param callable(): mixed $callback
     *
     * @return mixed
     */
    protected function transaction(callable $callback)
    {
        try {
            return DB::transaction(function () use ($callback) {
                return $callback();
            });
        } catch (Throwable $e) {
            Log::error('Database transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a standardized success response.
     *
     * This method creates a standardized JSON success response
     * with consistent structure and proper HTTP status codes.
     *
     * @param  mixed  $data  The data to include in the response
     * @param  string  $message  The success message
     * @param  int  $statusCode  The HTTP status code
     *
     * @return JsonResponse The standardized success response
     *
     * @example
     * return $this->successResponse(['user' => $user], 'User created successfully');
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Create a standardized error response.
     *
     * This method creates a standardized JSON error response
     * with consistent structure and proper HTTP status codes.
     *
     * @param  string  $message  The error message
     * @param  mixed  $errors  The error details
     * @param  int  $statusCode  The HTTP status code
     *
     * @return JsonResponse The standardized error response
     *
     * @example
     * return $this->errorResponse('Validation failed', $errors, 422);
     */
    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = 400,
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    /**
     * Create a standardized redirect response with flash message.
     *
     * This method creates a standardized redirect response
     * with flash message support and proper error handling.
     *
     * @param  string  $route  The route to redirect to
     * @param  string  $message  The flash message
     * @param  string  $type  The message type (success, error, warning, info)
     * @param  array  $parameters  Route parameters
     *
     * @return RedirectResponse The standardized redirect response
     *
     * @example
     * return $this->redirectWithMessage('users.index', 'User created successfully', 'success');
     */
    /**
     * @param array<string, mixed> $parameters
     */
    protected function redirectWithMessage(
        string $route,
        string $message,
        string $type = 'success',
        array $parameters = [],
    ): RedirectResponse {
        return redirect()->route($route, $parameters)->with($type, $message);
    }

    /**
     * Sanitize input to prevent XSS attacks.
     *
     * This method sanitizes input data to prevent XSS attacks
     * with comprehensive validation and security measures.
     *
     * @param  mixed  $input  The input to sanitize
     *
     * @return mixed The sanitized input
     *
     * @example
     * $sanitized = $this->sanitizeInput($request->validated('name'));
     */
    protected function sanitizeInput(mixed $input): mixed
    {
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        return $input;
    }

    /**
     * Hash data for logging purposes.
     *
     * This method hashes sensitive data for secure logging
     * to prevent exposure of sensitive information.
     *
     * @param  string  $data  The data to hash
     *
     * @return string The hashed data
     *
     * @example
     * $hashed = $this->hashForLogging($user->email);
     */
    protected function hashForLogging(string $data): string
    {
        return substr(hash('sha256', $data.(is_string(config('app.key')) ? config('app.key') : '')), 0, 8).'...';
    }

    /**
     * Log security event with comprehensive context.
     *
     * This method logs security events with comprehensive context
     * for security monitoring and threat detection.
     *
     * @param  string  $event  The security event name
     * @param  Request  $request  The current HTTP request instance
     * @param  array  $context  Additional context for the log entry
     *
     * @example
     * $this->logSecurityEvent('unauthorized_access', $request, ['user_id' => $userId]);
     */
    /**
     * @param array<string, mixed> $context
     */
    protected function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        try {
            Log::warning('Security event: '.$event, array_merge([
                'event' => $event,
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->check() ? auth()->id() : 'guest',
                'timestamp' => now()->toISOString(),
            ], $context));
        } catch (Throwable $e) {
            Log::error('Failed to log security event', [
                'event' => $event,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Validate request with enhanced security and error handling.
     *
     * This method validates request data with enhanced security
     * measures and comprehensive error handling.
     *
     * @param  Request  $request  The current HTTP request instance
     * @param  array  $rules  The validation rules
     * @param  array  $messages  Custom validation messages
     *
     * @return array The validated data
     *
     * @throws \Illuminate\Validation\ValidationException When validation fails
     *
     * @example
     * $validated = $this->validateRequest($request, ['name' => 'required|string']);
     */
    /**
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        try {
            $validated = $request->validate($rules, $messages);
            /**
             * @var array<string, mixed> $result
             */
            $result = $validated;

            return $result;
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Request validation failed', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => auth()->check() ? auth()->id() : 'guest',
                'errors' => $e->errors(),
                'input' => $this->sanitizeInput($request->all()),
            ]);
            throw $e;
        }
    }

    /**
     * Handle controller errors with comprehensive logging.
     *
     * This method handles controller errors with comprehensive
     * logging and standardized error responses.
     *
     * @param  Throwable  $e  The exception that occurred
     * @param  Request  $request  The current HTTP request instance
     * @param  string  $context  Additional context for the error
     *
     * @return JsonResponse The standardized error response
     *
     * @example
     * return $this->handleError($exception, $request, 'User creation failed');
     */
    protected function handleError(Throwable $e, Request $request, string $context = ''): JsonResponse
    {
        Log::error('Controller error: '.$context, [
            'error' => $e->getMessage(),
            'context' => $context,
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->check() ? auth()->id() : 'guest',
            'trace' => $e->getTraceAsString(),
        ]);

        return $this->errorResponse(
            'An error occurred while processing your request.',
            null,
            500,
        );
    }

    /**
     * Check if user has permission with enhanced security.
     *
     * This method checks user permissions with enhanced security
     * measures and comprehensive logging.
     *
     * @param  string  $permission  The permission to check
     * @param  mixed  $resource  The resource to check permission for
     *
     * @return bool True if user has permission, false otherwise
     *
     * @example
     * $hasPermission = $this->hasPermission('edit', $user);
     */
    protected function hasPermission(string $permission, mixed $resource = null): bool
    {
        try {
            if (! auth()->check()) {
                return false;
            }
            $user = auth()->user();
            if ($user === null) {
                return false;
            }
            if ($resource) {
                return $user->can($permission, $resource);
            }

            return $user->can($permission);
        } catch (Throwable $e) {
            Log::error('Permission check failed', [
                'permission' => $permission,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get current user with enhanced security.
     *
     * This method gets the current authenticated user with
     * enhanced security measures and validation.
     *
     * @return \App\Models\User|null The current user or null if not authenticated
     *
     * @example
     * $user = $this->getCurrentUser();
     */
    protected function getCurrentUser(): ?\App\Models\User
    {
        try {
            return auth()->user();
        } catch (Throwable $e) {
            Log::error('Failed to get current user', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
