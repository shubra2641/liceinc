<?php

declare(strict_types=1);

namespace App\Services\License;

use App\Models\License;
use Illuminate\Support\Facades\Log;

/**
 * Registration Result Service - Handles registration result formatting and processing.
 */
class RegistrationResultService
{
    /**
     * Create success result.
     */
    public function createSuccessResult(License $license, string $message = 'License registered successfully'): array
    {
        return [
            'success' => true,
            'license' => $license,
            'message' => $message,
        ];
    }

    /**
     * Create failure result.
     */
    public function createFailureResult(string $message, ?License $license = null): array
    {
        return [
            'success' => false,
            'license' => $license,
            'message' => $message,
        ];
    }

    /**
     * Create existing license result.
     */
    public function createExistingLicenseResult(License $license): array
    {
        return [
            'success' => true,
            'license' => $license,
            'message' => 'License already exists for this user',
        ];
    }

    /**
     * Create validation error result.
     */
    public function createValidationErrorResult(array $errors): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => 'Validation failed',
            'errors' => $errors,
        ];
    }

    /**
     * Create authentication error result.
     */
    public function createAuthenticationErrorResult(): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => 'User must be authenticated',
        ];
    }

    /**
     * Create product not found result.
     */
    public function createProductNotFoundResult(): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => 'Product not found',
        ];
    }

    /**
     * Create out of stock result.
     */
    public function createOutOfStockResult(): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => 'Product is out of stock',
        ];
    }

    /**
     * Create invalid purchase code result.
     */
    public function createInvalidPurchaseCodeResult(string $message = 'Invalid purchase code'): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => $message,
        ];
    }

    /**
     * Create license limit reached result.
     */
    public function createLicenseLimitReachedResult(): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => 'User has reached maximum license limit',
        ];
    }

    /**
     * Create database error result.
     */
    public function createDatabaseErrorResult(string $message = 'Database operation failed'): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => $message,
        ];
    }

    /**
     * Create system error result.
     */
    public function createSystemErrorResult(string $message = 'System error occurred'): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => $message,
        ];
    }

    /**
     * Format license data for response.
     */
    public function formatLicenseData(License $license): array
    {
        try {
            return [
                'id' => $license->id,
                'purchase_code' => $license->purchase_code,
                'product_id' => $license->product_id,
                'user_id' => $license->user_id,
                'status' => $license->status,
                'expires_at' => $license->expires_at?->toISOString(),
                'support_expires_at' => $license->support_expires_at?->toISOString(),
                'created_at' => $license->created_at->toISOString(),
                'updated_at' => $license->updated_at->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('License data formatting failed', [
                'error' => $e->getMessage(),
                'license_id' => $license->id,
            ]);
            throw $e;
        }
    }

    /**
     * Format registration statistics.
     */
    public function formatRegistrationStatistics(array $stats): array
    {
        return [
            'total_registrations' => $stats['total_registrations'] ?? 0,
            'successful_registrations' => $stats['successful_registrations'] ?? 0,
            'failed_registrations' => $stats['failed_registrations'] ?? 0,
            'success_rate' => $stats['success_rate'] ?? 0,
            'average_registration_time' => $stats['average_registration_time'] ?? 0,
        ];
    }

    /**
     * Create bulk registration result.
     */
    public function createBulkRegistrationResult(array $results): array
    {
        $successful = array_filter($results, fn($result) => $result['success']);
        $failed = array_filter($results, fn($result) => !$result['success']);

        return [
            'success' => count($failed) === 0,
            'total' => count($results),
            'successful' => count($successful),
            'failed' => count($failed),
            'results' => $results,
        ];
    }

    /**
     * Create registration summary.
     */
    public function createRegistrationSummary(array $result): array
    {
        return [
            'success' => $result['success'],
            'message' => $result['message'],
            'license_id' => $result['license']?->id,
            'product_id' => $result['license']?->product_id,
            'user_id' => $result['license']?->user_id,
            'expires_at' => $result['license']?->expires_at?->toISOString(),
            'support_expires_at' => $result['license']?->support_expires_at?->toISOString(),
        ];
    }

    /**
     * Log registration result.
     */
    public function logRegistrationResult(array $result, string $purchaseCode, ?int $productId = null): void
    {
        try {
            $logData = [
                'success' => $result['success'],
                'message' => $result['message'],
                'purchase_code' => $purchaseCode,
                'product_id' => $productId,
                'license_id' => $result['license']?->id,
                'user_id' => $result['license']?->user_id,
            ];

            if ($result['success']) {
                Log::info('License registration successful', $logData);
            } else {
                Log::warning('License registration failed', $logData);
            }
        } catch (\Exception $e) {
            Log::error('Registration result logging failed', [
                'error' => $e->getMessage(),
                'result' => $result,
            ]);
        }
    }

    /**
     * Create error result with details.
     */
    public function createErrorResult(string $message, array $details = []): array
    {
        return [
            'success' => false,
            'license' => null,
            'message' => $message,
            'details' => $details,
        ];
    }

    /**
     * Create partial success result.
     */
    public function createPartialSuccessResult(License $license, string $message, array $warnings = []): array
    {
        return [
            'success' => true,
            'license' => $license,
            'message' => $message,
            'warnings' => $warnings,
        ];
    }
}
