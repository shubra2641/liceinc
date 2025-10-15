<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

/**
 * Payment Gateway Interface.
 * 
 * Defines the contract for payment gateway implementations.
 * All payment gateways must implement this interface to ensure
 * consistent behavior across different payment providers.
 */
interface GatewayInterface
{
    /**
     * Process payment with the gateway.
     * 
     * @param array $orderData Order data containing user_id, amount, currency, etc.
     * @return array Payment result with success status and redirect URL
     * @throws \InvalidArgumentException When order data is invalid
     * @throws \Exception When payment processing fails
     */
    public function processPayment(array $orderData): array;

    /**
     * Verify payment with the gateway.
     * 
     * @param string $transactionId Transaction ID to verify
     * @return array Verification result with success status and payment details
     * @throws \InvalidArgumentException When transaction ID is invalid
     * @throws \Exception When verification fails
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Handle webhook from the gateway.
     * 
     * @param array $webhookData Webhook data from the gateway
     * @return array Webhook processing result
     * @throws \Exception When webhook processing fails
     */
    public function handleWebhook(array $webhookData): array;

    /**
     * Get gateway name.
     * 
     * @return string Gateway name (e.g., 'paypal', 'stripe')
     */
    public function getGatewayName(): string;

    /**
     * Check if gateway is configured and ready.
     * 
     * @return bool True if gateway is ready, false otherwise
     */
    public function isConfigured(): bool;
}
