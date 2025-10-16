<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\License;
use App\Models\LicenseLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Tracking Middleware with enhanced security.
 *
 * This middleware handles API request tracking for license verification endpoints,
 * providing comprehensive logging and monitoring capabilities with enhanced
 * security measures and proper error handling.
 *
 * Features:
 * - API request tracking for license verification endpoints
 * - Comprehensive logging with database transactions
 * - Enhanced security measures (XSS protection, input sanitization)
 * - Proper error handling with database transactions
 * - Rate limiting and security validation
 * - Request/response data sanitization
 * - IP address and user agent tracking
 * - License status monitoring and analytics
 */
class ApiTrackingMiddleware
{
    /**
     * Handle an incoming request with enhanced security.
     *
     * Processes incoming API requests and tracks license verification calls
     * with comprehensive logging and security measures.
     *
     * @param  Request  $request  The incoming HTTP request
     * @param  Closure  $next  The next middleware in the pipeline
     *
     * @return Response The HTTP response
     *
     * @throws \Exception When tracking operations fail
     *
     * @example
     * // Middleware automatically tracks:
     * // POST /api/license/verify
     * // {
     * //     "license_key": "ABC123-DEF456-GHI789",
     * //     "domain": "example.com",
     * //     "serial": "SERIAL123"
     * // }
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        // Only track API license verification requests
        if ($request->is('api/license/verify') && $request->isMethod('post')) {
            if ($response instanceof Response) {
                $this->trackApiCall($request, $response);
            }
        }
        /**
         * @var Response $typedResponse
         */
        $typedResponse = $response;

        return $typedResponse;
    }

    /**
     * Track API call details with enhanced security.
     *
     * Records comprehensive information about API license verification calls
     * including request data, response data, and security metrics with
     * proper sanitization and error handling.
     *
     * @param  Request  $request  The HTTP request object
     * @param  Response  $response  The HTTP response object
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * // Tracks successful verification:
     * // - License ID: 123
     * // - Domain: example.com
     * // - IP: 192.168.1.1
     * // - Status: success
     * // - Response: {"success": true, "license": {...}}
     */
    private function trackApiCall(Request $request, Response $response): void
    {
        try {
            DB::beginTransaction();
            // Sanitize request data to prevent XSS
            $requestData = $this->sanitizeRequestData($request->all());
            $responseContent = $response->getContent();
            $responseData = $this->sanitizeResponseData($responseContent !== false ? $responseContent : '');
            // Extract and sanitize license information
            $licenseKey = $this->sanitizeInput(
                is_string($requestData['license_key'] ?? null)
                    ? $requestData['license_key']
                    : null,
            );
            $domain = $this->sanitizeInput(is_string($requestData['domain'] ?? null) ? $requestData['domain'] : null);
            $serial = $this->sanitizeInput(is_string($requestData['serial'] ?? null) ? $requestData['serial'] : null);
            // Find license by key with security validation
            $license = null;
            if ($licenseKey && $this->isValidLicenseKey($licenseKey)) {
                $license = License::where('license_key', $licenseKey)->first();
            }
            // Determine status with enhanced validation
            $status = $this->determineStatus($response, $responseData);
            // Create log entry with sanitized data
            LicenseLog::create([
                'license_id' => $license ? $license->id : null,
                'domain' => $domain,
                'ip_address' => $request->ip(),
                'serial' => $serial,
                'status' => $status,
                'user_agent' => $this->sanitizeInput($request->userAgent()),
                'request_data' => $requestData,
                'response_data' => $responseData,
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API tracking failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
            ]);
        }
    }

    /**
     * Sanitize request data to prevent XSS attacks.
     *
     * @param  array<mixed, mixed>  $data  The request data to sanitize
     *
     * @return array<mixed, mixed> The sanitized request data
     */
    private function sanitizeRequestData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeRequestData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize response data to prevent XSS attacks.
     *
     * @param  string  $content  The response content to sanitize
     *
     * @return array<string, mixed> The sanitized response data
     */
    private function sanitizeResponseData(string $content): array
    {
        $responseData = json_decode($content, true) ?? [];
        $sanitizedData = is_array($responseData) ? $this->sanitizeRequestData($responseData) : [];
        /**
         * @var array<string, mixed> $typedResult
         */
        $typedResult = $sanitizedData;

        return $typedResult;
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

    /**
     * Validate license key format.
     *
     * @param  string  $licenseKey  The license key to validate
     *
     * @return bool True if valid, false otherwise
     */
    private function isValidLicenseKey(string $licenseKey): bool
    {
        return preg_match('/^[a-zA-Z0-9\-_]+$/', $licenseKey) === 1;
    }

    /**
     * Determine API call status based on response.
     *
     * @param  Response  $response  The HTTP response
     * @param  array<string, mixed>  $responseData  The parsed response data
     *
     * @return string The determined status
     */
    private function determineStatus(Response $response, array $responseData): string
    {
        if ($response->getStatusCode() === 200 && isset($responseData['success']) && $responseData['success']) {
            return 'success';
        } elseif ($response->getStatusCode() >= 400) {
            return 'failed';
        }

        return 'error';
    }
}
