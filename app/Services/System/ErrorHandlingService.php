<?php

declare(strict_types=1);

namespace App\Services\System;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Error Handling Service for centralized error management.
 *
 * This service provides centralized error handling for various operations
 * with consistent logging and user-friendly error messages.
 */
class ErrorHandlingService
{
    /**
     * Handle verification failure.
     */
    public function handleVerificationFailure(Request $request, array $data): RedirectResponse
    {
        DB::rollBack();
        Log::warning('Failed to verify purchase code', [
            'purchase_code' => $this->maskPurchaseCode($data['purchase_code']),
            'ip' => $request->ip(),
        ]);
        return back()->withErrors(['purchase_code' => 'Could not verify purchase code. Please check and try again.']);
    }

    /**
     * Handle product mismatch.
     */
    public function handleProductMismatch(Request $request, array $data): RedirectResponse
    {
        DB::rollBack();
        Log::warning('Purchase code does not match product', [
            'purchase_code' => $this->maskPurchaseCode($data['purchase_code']),
            'ip' => $request->ip(),
        ]);
        return back()->withErrors(['purchase_code' => 'Purchase code does not belong to this product.']);
    }

    /**
     * Handle validation error.
     */
    public function handleValidationError(\Illuminate\Validation\ValidationException $e, Request $request): RedirectResponse
    {
        DB::rollBack();
        Log::warning('Purchase verification validation failed', [
            'errors' => $e->errors(),
            'ip' => $request->ip(),
        ]);
        throw $e;
    }

    /**
     * Handle general error.
     */
    public function handleGeneralError(Exception $e, Request $request): RedirectResponse
    {
        DB::rollBack();
        Log::error('Purchase verification failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'ip' => $request->ip(),
        ]);
        return back()->withErrors(['purchase_code' => 'An error occurred while verifying your purchase. Please try again.']);
    }

    /**
     * Handle AJAX verification failure.
     */
    public function handleAjaxVerificationFailure(Request $request, array $data): array
    {
        DB::rollBack();
        Log::warning('AJAX purchase verification failed', [
            'user_id' => auth()->id(),
            'purchase_code' => $this->maskPurchaseCode($data['purchase_code']),
            'product_id' => $data['product_id'],
            'ip' => $request->ip(),
        ]);
        return ['valid' => false, 'message' => 'Invalid purchase code. Please check and try again.'];
    }

    /**
     * Handle AJAX product mismatch.
     */
    public function handleAjaxProductMismatch(Request $request, array $data, $product): array
    {
        DB::rollBack();
        Log::warning('AJAX purchase code does not match product', [
            'user_id' => auth()->id(),
            'purchase_code' => $this->maskPurchaseCode($data['purchase_code']),
            'product_id' => $product->id,
            'ip' => $request->ip(),
        ]);
        return ['valid' => false, 'message' => 'Purchase code does not match this product.'];
    }

    /**
     * Handle AJAX validation error.
     */
    public function handleAjaxValidationError(\Illuminate\Validation\ValidationException $e, Request $request): array
    {
        DB::rollBack();
        Log::warning('AJAX purchase verification validation failed', [
            'user_id' => auth()->id(),
            'errors' => $e->errors(),
            'ip' => $request->ip(),
        ]);
        return [
            'valid' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ];
    }

    /**
     * Handle AJAX general error.
     */
    public function handleAjaxGeneralError(Exception $e, Request $request): array
    {
        DB::rollBack();
        Log::error('AJAX purchase verification failed', [
            'user_id' => auth()->id(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'ip' => $request->ip(),
        ]);
        return ['valid' => false, 'message' => 'An error occurred while verifying your purchase. Please try again.'];
    }

    /**
     * Log rate limit exceeded.
     */
    public function logRateLimitExceeded(Request $request, array $data): void
    {
        Log::warning('Rate limit exceeded for purchase verification', [
            'ip' => $request->ip(),
            'purchase_code' => $this->maskPurchaseCode($data['purchase_code']),
        ]);
    }

    /**
     * Log AJAX rate limit exceeded.
     */
    public function logAjaxRateLimitExceeded(Request $request): void
    {
        Log::warning('Rate limit exceeded for AJAX purchase verification', [
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);
    }

    /**
     * Mask purchase code for logging.
     */
    private function maskPurchaseCode(string $purchaseCode): string
    {
        return substr($purchaseCode, 0, 8) . '...';
    }
}
