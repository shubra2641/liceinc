<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\LicenseStatusRequest;
use App\Models\License;
use App\Models\LicenseDomain;
use App\Models\Setting;
use App\Services\Envato\EnvatoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

/**
 * License Status Controller with enhanced security and comprehensive license verification.
 *
 * This controller provides comprehensive license status checking functionality including
 * license verification, status checking, history tracking, and enhanced security measures
 * with comprehensive error handling and logging.
 *
 * Features:
 * - Enhanced license status checking and verification
 * - Rate limiting protection against abuse
 * - Envato API integration for purchase verification
 * - License history and log tracking
 * - Comprehensive error handling and logging
 * - Input validation and sanitization
 * - Enhanced security measures for license operations
 * - Database transaction support for data integrity
 * - Proper error responses for different scenarios
 * - Comprehensive logging for security monitoring
 *
 * @example
 * // Check license status
 * POST /license-status/check
 * {
 *     "license_key": "ABC123-DEF456-GHI789",
 *     "email": "user@example.com"
 * }
 */
class LicenseStatusController extends Controller
{
    /**
     * Display the license status check page with enhanced security.
     *
     * This method displays the license status check page with
     * enhanced security measures and proper error handling.
     *
     * @return View The license status check page view
     *
     * @throws \Exception When view rendering fails
     *
     * @example
     * // Display license status page
     * $view = $licenseStatusController->index();
     */
    public function index(): View
    {
        try {
            return view('license-status');
        } catch (Throwable $e) {
            Log::error('Failed to load license status page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            abort(500, 'Failed to load license status page. Please try again.');
        }
    }

    /**
     * Display license status results in Blade template.
     *
     * @param  LicenseStatusRequest  $request  The current HTTP request instance
     *
     * @return View The license status results view
     */
    public function showResults(LicenseStatusRequest $request): View
    {
        try {
            // Check rate limiting
            if ($this->isRateLimited($request)) {
                return $this->showError(
                    __('license_status.verification_error') . ': Too many attempts. Please try again later.'
                );
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'license_key' => 'required|string|max:255',
                'email' => 'required|email|max:255',
            ]);

            if ($validator->fails()) {
                return $this->showError('Please enter all required data correctly.', $validator->errors());
            }

            // Find license
            $license = $this->findLicenseByCodeAndEmail(
                $request->validated('license_key'),
                $request->validated('email')
            );

            if (!$license) {
                return $this->showError(__('license_status.license_not_found'));
            }

            // Build license details
            $licenseDetails = $this->buildLicenseDetails($license);

            return view('license-status', [
                'licenseData' => $licenseDetails,
                'error' => null,
                'validationErrors' => null,
                'success' => true,
            ]);
        } catch (Throwable $e) {
            Log::error('License check error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->showError(__('license_status.unexpected_error'));
        }
    }

    /**
     * Check if request is rate limited.
     */
    private function isRateLimited(Request $request): bool
    {
        $ip = $request->ip();
        $settings = Setting::first();
        $maxAttempts = $settings->license_max_attempts ?? 5;
        $decayMinutes = $settings->license_lockout_minutes ?? 15;
        $key = 'license_check_' . md5($ip ?? '');
        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            return true;
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        return false;
    }

    /**
     * Show error view.
     */
    private function showError(string $error, $validationErrors = null): View
    {
        return view('license-status', [
            'licenseData' => null,
            'error' => $error,
            'validationErrors' => $validationErrors,
            'success' => false,
        ]);
    }

    /**
     * Build license details array.
     */
    private function buildLicenseDetails(License $license): array
    {
        $status = $this->getLicenseStatus($license);
        $licenseType = $this->determineLicenseType($license);

        return [
            'license_key' => $license->license_key,
            'purchase_code' => $license->purchase_code,
            'email' => $license->user?->email,
            'status' => $status,
            'license_type' => $licenseType,
            'product_name' => $license->product?->name ?? 'Not specified',
            'created_at' => $license->created_at?->format('Y-m-d H:i:s'),
            'expires_at' => $license->license_expires_at?->format('Y-m-d H:i:s'),
            'is_expired' => $license->license_expires_at?->isPast() ?? false,
            'days_remaining' => $license->license_expires_at
                ? max(0, now()->diffInDays($license->license_expires_at, false))
                : null,
            'domains' => $license->domains->toArray(),
            'max_domains' => $license->max_domains ?? 1,
            'used_domains' => $license->domains->count(),
        ];
    }
    /**
     * Check license status with enhanced security and comprehensive validation.
     *
     * This method checks license status with comprehensive validation,
     * rate limiting, and Envato API integration for enhanced security.
     *
     * @param  LicenseStatusRequest  $request  The current HTTP request instance
     *
     * @return JsonResponse JSON response with license status information
     *
     * @throws \Exception When license checking fails
     *
     * @example
     * // Check license status
     * $response = $licenseStatusController->check($request);
     */
    public function check(LicenseStatusRequest $request): JsonResponse
    {
        try {
            $result = $this->transaction(function () use ($request) {
                // Rate limiting
                $ip = $request->ip();
                $settings = Setting::first();
                $maxAttempts = $settings->license_max_attempts ?? 5;
                $decayMinutes = $settings->license_lockout_minutes ?? 15;
                $key = 'license_check_' . md5($ip ?? '');
                $attempts = Cache::get($key, 0);
                if ($attempts >= $maxAttempts) {
                    Log::warning('License check rate limit exceeded', [
                        'ip' => $ip,
                        'attempts' => $attempts,
                        'max_attempts' => $maxAttempts,
                        'user_agent' => $request->userAgent(),
                    ]);
                    return $this->errorResponse(
                        __('license_status.verification_error') . ': Too many attempts. Please try again later.',
                        null,
                        429,
                    );
                }
                Cache::put($key, (is_numeric($attempts) ? (int)$attempts : 0) + 1, now()->addMinutes($decayMinutes));
                $validator = Validator::make($request->all(), [
                    'license_key' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                ]);
                if ($validator->fails()) {
                    Log::warning('License check validation failed', [
                        'ip' => $ip,
                        'errors' => $validator->errors(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    return $this->errorResponse(
                        'Please enter all required data correctly.',
                        $validator->errors(),
                        422,
                    );
                }
                $licenseCode = $this->sanitizeInput($request->validated('license_key'));
                $email = $this->sanitizeInput($request->validated('email'));
                // Search for license by code and email
                $license = $this->findLicenseByCodeAndEmail(
                    is_string($licenseCode) ? $licenseCode : '',
                    is_string($email) ? $email : ''
                );
                if (! $license) {
                    // Log failed verification
                    \App\Services\LicenseVerificationLogger::log(
                        purchaseCode: is_string($licenseCode) ? $licenseCode : 'unknown',
                        domain: 'website_check',
                        isValid: false,
                        message: 'License not found',
                        source: 'website'
                    );
                    return $this->errorResponse(
                        __('license_status.license_not_found'),
                        null,
                        404,
                    );
                }
                // Determine license type
                $licenseType = $this->determineLicenseType($license);
                // For Envato licenses, verify with Envato API (optional additional check)
                $envatoData = null;
                if ($licenseType === __('license_status.envato') && $license->purchase_code) {
                    $envatoData = $this->verifyWithEnvato($license->purchase_code);
                }
                // Check license status
                $status = $this->getLicenseStatus($license);
                // Get license details
                $licenseDetails = $this->buildLicenseDetails(
                    $license,
                    $status,
                    $licenseType,
                    $envatoData,
                    is_string($email) ? $email : ''
                );
                // Reset attempts on success
                Cache::forget($key);

                // Log successful verification
                \App\Services\LicenseVerificationLogger::log(
                    purchaseCode: $license->purchase_code ?? 'unknown',
                    domain: 'website_check',
                    isValid: true,
                    message: 'License status checked successfully',
                    source: 'website'
                );
                return $this->successResponse(
                    $licenseDetails,
                    __('license_status.license_found_success'),
                    200,
                    'license',
                );
            });
        } catch (Throwable $e) {
            // Log failed verification
            \App\Services\LicenseVerificationLogger::log(
                purchaseCode: is_string($request->validated('license_key'))
                    ? $request->validated('license_key') : 'unknown',
                domain: 'website_check',
                isValid: false,
                message: 'License check error: ' . $e->getMessage(),
                source: 'website',
                errorDetails: $e->getMessage()
            );
            return $this->errorResponse(
                __('license_status.unexpected_error'),
                null,
                500,
            );
        }
        return $result instanceof JsonResponse ? $result : $this->errorResponse('Unexpected error', null, 500);
    }
    /**
     * Get license history/logs with enhanced security and comprehensive validation.
     *
     * This method retrieves license history and logs with comprehensive
     * validation and enhanced security measures.
     *
     * @param  LicenseStatusRequest  $request  The current HTTP request instance
     *
     * @return JsonResponse JSON response with license history
     *
     * @throws \Exception When license history retrieval fails
     *
     * @example
     * // Get license history
     * $response = $licenseStatusController->history($request);
     */
    public function history(LicenseStatusRequest $request): JsonResponse
    {
        try {
            $result = $this->transaction(function () use ($request) {
                $validator = Validator::make($request->all(), [
                    'license_key' => 'required|string|max:255',
                    'email' => 'required|email|max:255',
                ]);
                if ($validator->fails()) {
                    Log::warning('License history validation failed', [
                        'ip' => $request->ip(),
                        'errors' => $validator->errors(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    return $this->errorResponse(
                        __('license_status.validation_error'),
                        $validator->errors(),
                        422,
                    );
                }
                $licenseCode = $this->sanitizeInput($request->validated('license_key'));
                $email = $this->sanitizeInput($request->validated('email'));
                $license = $this->findLicenseByCodeAndEmail(
                    is_string($licenseCode) ? $licenseCode : '',
                    is_string($email) ? $email : ''
                );
                if (! $license) {
                    Log::warning('License not found for history request', [
                        'license_key' => $this->hashForLogging(is_string($licenseCode) ? $licenseCode : ''),
                        'email' => $this->hashForLogging(is_string($email) ? $email : ''),
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                    return $this->errorResponse(
                        'No license found with this data.',
                        null,
                        404,
                    );
                }
                $logs = $license->logs()
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get()
                    ->map(function ($log): array {
                        return [
                            'action' => $log->action,
                            'status' => $log->status,
                            'ip_address' => $log->ip_address,
                            'user_agent' => $log->user_agent,
                            'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                            'message' => $log->message,
                        ];
                    });
                Log::debug('License history retrieved successfully', [
                    'license_id' => $license->id,
                    'license_key' => $this->hashForLogging(is_string($licenseCode) ? $licenseCode : ''),
                    'email' => $this->hashForLogging(is_string($email) ? $email : ''),
                    'logs_count' => $logs->count(),
                    'ip' => $request->ip(),
                ]);
                return $this->successResponse($logs, 'License history retrieved successfully');
            });
        } catch (Throwable $e) {
            Log::error('License history error', [
                'error' => $e->getMessage(),
                'license_key' => $this->hashForLogging(
                    is_string($request->validated('license_key')) ? $request->validated('license_key') : ''
                ),
                'email' => $this->hashForLogging(
                    is_string($request->validated('email')) ? $request->validated('email') : ''
                ),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->errorResponse(
                'An error occurred while retrieving license history.',
                null,
                500,
            );
        }
        return $result instanceof JsonResponse ? $result : $this->errorResponse('Unexpected error', null, 500);
    }
    /**
     * Find license by code and email.
     */
    private function findLicenseByCodeAndEmail(string $licenseCode, string $email): ?License
    {
        // Search by license key first
        $license = License::where('license_key', $licenseCode)
            ->whereHas('user', function ($query) use ($email) {
                $query->where('email', $email);
            })
            ->with(['product', 'user', 'domains'])
            ->first();

        // If not found, try purchase code
        if (!$license) {
            $license = License::where('purchase_code', $licenseCode)
                ->whereHas('user', function ($query) use ($email) {
                    $query->where('email', $email);
                })
                ->with(['product', 'user', 'domains'])
                ->first();
        }

        return $license;
    }
    /**
     * Determine license type.
     */
    private function determineLicenseType(License $license): string
    {
        return $license->purchase_code && strlen($license->purchase_code) > 10
            ? __('license_status.envato')
            : __('license_status.custom');
    }
    /**
     * Get license status.
     */
    private function getLicenseStatus(License $license): string
    {
        if ($license->status === 'active') {
            return $license->license_expires_at?->isPast()
                ? __('license_status.expired')
                : __('license_status.active');
        }

        return match ($license->status) {
            'inactive' => __('license_status.inactive'),
            'suspended' => __('license_status.revoked'),
            'expired' => __('license_status.expired'),
            default => 'Unknown'
        };
    }
    /**
     * Create a standardized success response with custom data key.
     *
     * @param  mixed  $data  The data to include in the response
     * @param  string  $message  The success message
     * @param  int  $statusCode  The HTTP status code
     * @param  string  $dataKey  The key for the data in the response
     *
     * @return JsonResponse The standardized success response
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200,
        string $dataKey = 'data',
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => now()->toISOString(),
        ];
        $response[$dataKey] = $data;
        return response()->json($response, $statusCode);
    }
}
