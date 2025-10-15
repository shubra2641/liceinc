<?php

declare(strict_types=1);

namespace App\Services\Payment\Validators;

use InvalidArgumentException;

/**
 * Payment Validation Helper.
 * 
 * Provides comprehensive validation for payment-related data.
 * Ensures data integrity and security for payment operations.
 */
class PaymentValidator
{
    /**
     * Validate order data.
     * 
     * @param array $orderData Order data to validate
     * @throws InvalidArgumentException When validation fails
     */
    public function validateOrderData(array $orderData): void
    {
        if (empty($orderData)) {
            throw new InvalidArgumentException('Order data is required');
        }

        $this->validateUserId($orderData);
        $this->validateAmount($orderData);
        $this->validateCurrency($orderData);
    }

    /**
     * Validate payment gateway.
     * 
     * @param string $gateway Gateway name to validate
     * @throws InvalidArgumentException When gateway is invalid
     */
    public function validateGateway(string $gateway): void
    {
        if (empty($gateway)) {
            throw new InvalidArgumentException('Payment gateway is required');
        }

        if (!in_array($gateway, ['paypal', 'stripe'])) {
            throw new InvalidArgumentException('Unsupported payment gateway');
        }
    }

    /**
     * Validate transaction ID.
     * 
     * @param string $transactionId Transaction ID to validate
     * @throws InvalidArgumentException When transaction ID is invalid
     */
    public function validateTransactionId(string $transactionId): void
    {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('Transaction ID is required');
        }

        if (strlen($transactionId) < 10) {
            throw new InvalidArgumentException('Transaction ID is too short');
        }

        if (strlen($transactionId) > 255) {
            throw new InvalidArgumentException('Transaction ID is too long');
        }
    }

    /**
     * Validate PayPal credentials.
     * 
     * @param array|null $credentials PayPal credentials
     * @throws InvalidArgumentException When credentials are invalid
     */
    public function validatePayPalCredentials(?array $credentials): void
    {
        if (empty($credentials)) {
            throw new InvalidArgumentException('PayPal credentials are required');
        }

        if (empty($credentials['client_id'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_id is required');
        }

        if (empty($credentials['client_secret'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_secret is required');
        }
    }

    /**
     * Validate Stripe credentials.
     * 
     * @param array|null $credentials Stripe credentials
     * @throws InvalidArgumentException When credentials are invalid
     */
    public function validateStripeCredentials(?array $credentials): void
    {
        if (empty($credentials)) {
            throw new InvalidArgumentException('Stripe credentials are required');
        }

        if (empty($credentials['secret_key'] ?? '')) {
            throw new InvalidArgumentException('Stripe secret_key is required');
        }

        if (empty($credentials['publishable_key'] ?? '')) {
            throw new InvalidArgumentException('Stripe publishable_key is required');
        }
    }

    /**
     * Validate user ID.
     * 
     * @param array $orderData Order data containing user_id
     * @throws InvalidArgumentException When user ID is invalid
     */
    private function validateUserId(array $orderData): void
    {
        if (!isset($orderData['user_id'])) {
            throw new InvalidArgumentException('User ID is required');
        }

        if (!is_numeric($orderData['user_id'])) {
            throw new InvalidArgumentException('User ID must be numeric');
        }

        if ($orderData['user_id'] < 1) {
            throw new InvalidArgumentException('User ID must be positive');
        }
    }

    /**
     * Validate amount.
     * 
     * @param array $orderData Order data containing amount
     * @throws InvalidArgumentException When amount is invalid
     */
    private function validateAmount(array $orderData): void
    {
        if (!isset($orderData['amount'])) {
            throw new InvalidArgumentException('Amount is required');
        }

        if (!is_numeric($orderData['amount'])) {
            throw new InvalidArgumentException('Amount must be numeric');
        }

        if ($orderData['amount'] <= 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }

        if ($orderData['amount'] > 999999.99) {
            throw new InvalidArgumentException('Amount cannot exceed 999,999.99');
        }
    }

    /**
     * Validate currency.
     * 
     * @param array $orderData Order data containing currency
     * @throws InvalidArgumentException When currency is invalid
     */
    private function validateCurrency(array $orderData): void
    {
        if (!isset($orderData['currency'])) {
            throw new InvalidArgumentException('Currency is required');
        }

        if (empty($orderData['currency'])) {
            throw new InvalidArgumentException('Currency cannot be empty');
        }

        if (strlen($orderData['currency']) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-character code');
        }

        if (!ctype_alpha($orderData['currency'])) {
            throw new InvalidArgumentException('Currency must contain only letters');
        }
    }
}
