<?php

declare(strict_types=1);

namespace App\Services\Payment;

use InvalidArgumentException;

/**
 * Payment Validation Service - Handles payment data validation.
 */
class PaymentValidationService
{
    /**
     * Validate order data.
     */
    public function validateOrderData(array $orderData): void
    {
        if (empty($orderData)) {
            throw new InvalidArgumentException('Order data cannot be empty');
        }

        $this->validateUserId($orderData);
        $this->validateAmount($orderData);
        $this->validateCurrency($orderData);
    }

    /**
     * Validate payment gateway.
     */
    public function validateGateway(string $gateway): void
    {
        if (!in_array($gateway, ['paypal', 'stripe'])) {
            throw new InvalidArgumentException('Unsupported payment gateway');
        }
    }

    /**
     * Validate transaction ID.
     */
    public function validateTransactionId(string $transactionId): void
    {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }

        if (strlen($transactionId) < 3) {
            throw new InvalidArgumentException('Transaction ID must be at least 3 characters');
        }
    }

    /**
     * Validate user ID.
     */
    private function validateUserId(array $orderData): void
    {
        if (!isset($orderData['user_id']) || !is_numeric($orderData['user_id']) || $orderData['user_id'] < 1) {
            throw new InvalidArgumentException('Valid user_id is required');
        }
    }

    /**
     * Validate amount.
     */
    private function validateAmount(array $orderData): void
    {
        if (!isset($orderData['amount']) || !is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new InvalidArgumentException('Valid amount is required');
        }

        if ($orderData['amount'] > 999999.99) {
            throw new InvalidArgumentException('Amount cannot exceed 999,999.99');
        }
    }

    /**
     * Validate currency.
     */
    private function validateCurrency(array $orderData): void
    {
        if (!isset($orderData['currency']) || empty($orderData['currency'])) {
            throw new InvalidArgumentException('Currency is required');
        }

        if (strlen($orderData['currency']) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-character code');
        }
    }
}
