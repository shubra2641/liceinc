<?php

declare(strict_types=1);

namespace App\Services\License;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * License Response Service
 * 
 * Handles license API responses
 */
class LicenseResponseService
{
    /**
     * Create success response
     */
    public function success(array $data = [], int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Create error response
     */
    public function error(string $message, string $code = null, int $statusCode = 400): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
        ];

        if ($code) {
            $response['code'] = $code;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create validation error response
     */
    public function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 422);
    }

    /**
     * Create not found response
     */
    public function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->error($message, 'NOT_FOUND', 404);
    }

    /**
     * Create unauthorized response
     */
    public function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, 'UNAUTHORIZED', 401);
    }

    /**
     * Create forbidden response
     */
    public function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, 'FORBIDDEN', 403);
    }

    /**
     * Create server error response
     */
    public function serverError(string $message = 'Internal server error'): JsonResponse
    {
        return $this->error($message, 'SERVER_ERROR', 500);
    }
}
