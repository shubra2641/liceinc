<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Base Controller with enhanced security.
 *
 * This abstract controller provides common functionality and PSR-12 compliant structure
 * for all application controllers with enhanced security measures and proper error handling.
 *
 * Features:
 * - Request validation with comprehensive error handling
 * - Standardized JSON response creation
 * - Error response handling
 * - Security event logging
 * - Data sanitization for logging
 * - Exception handling with proper logging
 * - Permission checking and authorization
 * - Pagination metadata extraction
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling and logging
 * - Proper logging for errors and warnings only
 */
abstract class BaseController extends Controller
{
    /**
     * Validate request data with comprehensive error handling and enhanced security.
     *
     * Validates incoming request data against specified rules with comprehensive
     * error handling and security measures.
     *
     * @param  Request  $request  The HTTP request to validate
     * @param  array  $rules  The validation rules to apply
     * @param  array  $messages  Custom validation error messages
     *
     * @return array The validated data
     *
     * @throws ValidationException When validation fails
     *
     * @example
     * // Validate user registration data:
     * $validated = $this->validateRequest($request, [
     *     'name' => 'required|string|max:255',
     *     'email' => 'required|email|unique:users',
     *     'password' => 'required|min:8|confirmed'
     * ]);
     */
    /**
     * @param array<string, string> $rules
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     */
    protected function validateRequest(Request $request, array $rules, array $messages = []): array
    {
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $this->logValidationError($request, $validator->errors());
            throw new ValidationException($validator);
        }
        $validated = $validator->validated();

        /** @var array<string, mixed> $result */
        $result = $validated;

        return $result;
    }

    /**
     * Create standardized JSON response with enhanced security.
     *
     * Creates a standardized JSON response with consistent structure
     * and proper error handling.
     *
     * @param  mixed  $data  The response data
     * @param  string  $message  The response message
     * @param  int  $statusCode  The HTTP status code
     * @param  array  $meta  Additional metadata
     *
     * @return JsonResponse The JSON response
     *
     * @example
     * // Success response:
     * return $this->jsonResponse($user, 'User created successfully', 201);
     *
     * // Error response:
     * return $this->jsonResponse(null, 'User not found', 404);
     */
    /**
     * @param array<string, mixed> $meta
     */
    protected function jsonResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = Response::HTTP_OK,
        array $meta = [],
    ): JsonResponse {
        $response = [
            'success' => $statusCode < 400,
            'message' => $this->sanitizeInput($message),
            'data' => $data,
        ];
        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create error response with enhanced security.
     *
     * Creates a standardized error response with consistent structure
     * and proper error handling.
     *
     * @param  string  $message  The error message
     * @param  int  $statusCode  The HTTP status code
     * @param  array  $errors  Additional error details
     *
     * @return JsonResponse The error response
     *
     * @example
     * // Validation error:
     * return $this->errorResponse('Validation failed', 422, $validationErrors);
     *
     * // Server error:
     * return $this->errorResponse('Internal server error', 500);
     */
    /**
     * @param array<string, mixed>|null $errors
     */
    protected function errorResponse(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = 400,
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $this->sanitizeInput($message),
        ];
        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Log validation errors with enhanced security.
     *
     * Logs validation errors with comprehensive context and sanitized data
     * to prevent sensitive information exposure.
     *
     * @param  Request  $request  The HTTP request
     * @param  mixed  $errors  The validation errors
     *
     * @example
     * // Log validation errors:
     * $this->logValidationError($request, $validator->errors());
     */
    protected function logValidationError(Request $request, mixed $errors): void
    {
        Log::warning('Validation failed', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::id(),
            'errors' => $errors,
            'input' => $this->sanitizeLogData($request->all()),
        ]);
    }

    /**
     * Log security events with enhanced context.
     *
     * Logs security-related events with comprehensive context and
     * proper sanitization of sensitive data.
     *
     * @param  string  $event  The security event description
     * @param  Request  $request  The HTTP request
     * @param  array  $context  Additional context data
     *
     * @example
     * // Log unauthorized access attempt:
     * $this->logSecurityEvent('Unauthorized access attempt', $request, [
     *     'attempted_action' => 'admin_panel_access'
     * ]);
     */
    /**
     * @param array<string, mixed> $context
     */
    protected function logSecurityEvent(string $event, Request $request, array $context = []): void
    {
        Log::warning('Security event: '.$event, array_merge([
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::id(),
        ], $context));
    }

    /**
     * Sanitize data for logging with enhanced security.
     *
     * Removes or masks sensitive information from data before logging
     * to prevent exposure of sensitive data in logs.
     *
     * @param  array  $data  The data to sanitize
     *
     * @return array The sanitized data
     *
     * @example
     * // Sanitize user input for logging:
     * $sanitized = $this->sanitizeLogData($request->all());
     */
    /**
     * @param array<mixed, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function sanitizeLogData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'api_token',
            'license_key',
            'purchase_code',
            'credit_card',
            'ssn',
            'token',
            'secret',
            'key',
            'auth_token',
            'access_token',
            'refresh_token',
        ];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }
        /** @var array<string, mixed> $result */
        $result = $data;

        return $result;
    }

    /**
     * Handle exceptions with proper logging and response.
     *
     * Handles exceptions with comprehensive logging and appropriate
     * error responses based on exception type.
     *
     * @param  \Exception  $exception  The exception to handle
     * @param  Request  $request  The HTTP request
     * @param  string  $context  Additional context for logging
     *
     * @return JsonResponse The error response
     *
     * @example
     * // Handle database exception:
     * try {
     *     // Database operation
     * } catch (\Exception $e) {
     *     return $this->handleException($e, $request, 'Database operation failed');
     * }
     */
    protected function handleException(\Exception $exception, Request $request, string $context = ''): JsonResponse
    {
        $message = 'An unexpected error occurred';
        $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($exception instanceof ValidationException) {
            $message = 'Validation failed';
            $statusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        }
        Log::error('Controller exception: '.$context, [
            'exception' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_id' => Auth::id(),
        ]);

        return $this->errorResponse($message, null, $statusCode);
    }

    /**
     * Check if user has permission with enhanced validation.
     *
     * Checks if the authenticated user has the specified permission
     * with proper authentication validation.
     *
     * @param  string  $permission  The permission to check
     *
     * @return bool True if user has permission, false otherwise
     *
     * @example
     * // Check if user can edit posts:
     * if ($this->hasPermission('edit-posts')) {
     *     // Allow editing
     * }
     */
    protected function hasPermission(string $permission, mixed $resource = null): bool
    {
        $user = Auth::user();

        return Auth::check() && $user !== null && $user->can($permission);
    }

    /**
     * Require permission or abort with enhanced security.
     *
     * Requires the specified permission or aborts the request
     * with proper error handling and logging.
     *
     * @param  string  $permission  The permission to require
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException When permission is denied
     *
     * @example
     * // Require admin permission:
     * $this->requirePermission('admin-access');
     */
    protected function requirePermission(string $permission): void
    {
        if (! $this->hasPermission($permission)) {
            Log::warning('Permission denied', [
                'permission' => $permission,
                'user_id' => Auth::id(),
                'ip' => request()->ip(),
            ]);
            abort(Response::HTTP_FORBIDDEN, 'Insufficient permissions');
        }
    }

    /**
     * Get pagination metadata with enhanced validation.
     *
     * Extracts pagination metadata from a paginator instance
     * with proper validation and error handling.
     *
     * @param  mixed  $paginator  The paginator instance
     *
     * @return array The pagination metadata
     *
     * @example
     * // Get pagination metadata:
     * $meta = $this->getPaginationMeta($users);
     * return $this->jsonResponse($users, 'Users retrieved', 200, $meta);
     */
    /**
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, mixed> $paginator
     *
     * @return array<string, mixed>
     */
    protected function getPaginationMeta(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        try {
            return [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get pagination metadata', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [];
        }
    }

    /**
     * Sanitize input to prevent XSS attacks.
     */
    protected function sanitizeInput(mixed $input): mixed
    {
        if ($input === null) {
            return null;
        }
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        if (is_object($input)) {
            return $input;
        }

        return $input;
    }
}
