<?php

declare(strict_types=1);

namespace App\Services\Payment\Helpers;

/**
 * Payment Response Helper.
 * 
 * Provides standardized response building for payment operations.
 * Ensures consistent response format across all payment gateways.
 */
class ResponseHelper
{
    /**
     * Build success response.
     * 
     * @param array $data Additional data to include in response
     * @param string $message Success message
     * @return array Standardized success response
     */
    public function buildSuccess(array $data = [], string $message = 'Payment processed successfully'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Build failure response.
     * 
     * @param string $message Error message
     * @param array $data Additional error data
     * @return array Standardized failure response
     */
    public function buildFailure(string $message, array $data = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data,
        ];
    }

    /**
     * Build payment redirect response.
     * 
     * @param string $redirectUrl URL to redirect user to
     * @param array $additionalData Additional data for the payment
     * @return array Payment redirect response
     */
    public function buildRedirect(string $redirectUrl, array $additionalData = []): array
    {
        return $this->buildSuccess([
            'redirect_url' => $redirectUrl,
            'payment_url' => $redirectUrl,
            ...$additionalData
        ], 'Redirect to payment gateway');
    }

    /**
     * Build payment verification response.
     * 
     * @param bool $isValid Whether payment is valid
     * @param array $paymentData Payment details
     * @return array Payment verification response
     */
    public function buildVerification(bool $isValid, array $paymentData = []): array
    {
        if ($isValid) {
            return $this->buildSuccess($paymentData, 'Payment verified successfully');
        }

        return $this->buildFailure('Payment verification failed', $paymentData);
    }

    /**
     * Build webhook response.
     * 
     * @param bool $processed Whether webhook was processed successfully
     * @param string $message Processing message
     * @param array $data Additional webhook data
     * @return array Webhook processing response
     */
    public function buildWebhook(bool $processed, string $message, array $data = []): array
    {
        if ($processed) {
            return $this->buildSuccess($data, $message);
        }

        return $this->buildFailure($message, $data);
    }
}
