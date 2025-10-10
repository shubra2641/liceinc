<?php

declare(strict_types=1);

namespace App\Services\Installation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Service for handling installation license verification.
 */
class InstallationLicenseService
{
    public function __construct(
        private InstallationSecurityService $securityService
    ) {}

    /**
     * Process license verification.
     *
     * @param Request $request The HTTP request
     * @return array<string, mixed> The response data
     */
    public function processLicenseVerification(Request $request): array
    {
        try {
            // Validate input
            $validator = $this->validateLicenseRequest($request);
            if ($validator->fails()) {
                return $this->createErrorResponse($validator->errors()->first('purchase_code'));
            }

            // Sanitize and extract data
            $purchaseCode = $this->securityService->sanitizeInput($request->purchase_code);
            $domain = $this->securityService->sanitizeInput($request->getHost());

            // Verify license
            $result = $this->verifyLicense($purchaseCode, $domain);

            if ($result['valid']) {
                $this->storeLicenseInSession($purchaseCode, $domain, $result);
                return $this->createSuccessResponse();
            }

            return $this->createLicenseErrorResponse($result);

        } catch (\Exception $e) {
            Log::error('License verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->createExceptionResponse($e);
        }
    }

    /**
     * Validate license request.
     *
     * @param Request $request The HTTP request
     * @return \Illuminate\Contracts\Validation\Validator The validator instance
     */
    private function validateLicenseRequest(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'purchase_code' => 'required|string|min:5|max:100',
        ], [
            'purchase_code.required' => 'Purchase code is required',
            'purchase_code.min' => 'Purchase code must be at least 5 characters long.',
            'purchase_code.max' => 'Purchase code must not exceed 100 characters.',
        ]);
    }

    /**
     * Verify license with external service.
     *
     * @param string $purchaseCode The purchase code
     * @param string $domain The domain
     * @return array<string, mixed> The verification result
     */
    private function verifyLicense(string $purchaseCode, string $domain): array
    {
        // Mock implementation - replace with actual license verification
        return [
            'valid' => true,
            'message' => 'License verified',
            'verified_at' => now()->toDateTimeString(),
            'product' => 'License Management System',
        ];
    }

    /**
     * Store license information in session.
     *
     * @param string $purchaseCode The purchase code
     * @param string $domain The domain
     * @param array<string, mixed> $result The verification result
     * @return void
     */
    private function storeLicenseInSession(string $purchaseCode, string $domain, array $result): void
    {
        session(['install.license' => [
            'purchase_code' => $purchaseCode,
            'domain' => $domain,
            'verified_at' => $result['verified_at'] ?? now()->toDateTimeString(),
            'product' => $result['product'] ?? 'Unknown Product',
        ]]);
    }

    /**
     * Create success response.
     *
     * @return array<string, mixed> The success response
     */
    private function createSuccessResponse(): array
    {
        return [
            'success' => true,
            'message' => 'License verified successfully!',
            'redirect' => route('install.requirements'),
        ];
    }

    /**
     * Create error response.
     *
     * @param string $message The error message
     * @return array<string, mixed> The error response
     */
    private function createErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    /**
     * Create license error response.
     *
     * @param array<string, mixed> $result The verification result
     * @return array<string, mixed> The error response
     */
    private function createLicenseErrorResponse(array $result): array
    {
        $humanMessage = $result['message'] ?? 'License verification failed.';
        $errorCode = $result['error_code'] ?? $this->extractCodeFromMessage($humanMessage);

        return [
            'success' => false,
            'error_code' => $errorCode,
            'message' => $humanMessage,
        ];
    }

    /**
     * Create exception response.
     *
     * @param \Exception $e The exception
     * @return array<string, mixed> The error response
     */
    private function createExceptionResponse(\Exception $e): array
    {
        return [
            'success' => false,
            'message' => 'License verification failed: ' . $e->getMessage(),
        ];
    }

    /**
     * Extract error code from message.
     *
     * @param string $message The message
     * @return string The extracted code
     */
    private function extractCodeFromMessage(string $message): string
    {
        // Simple implementation - can be enhanced
        return 'LICENSE_ERROR';
    }
}
