<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Services\Payment\Gateways\GatewayInterface;
use App\Services\Payment\Gateways\PayPalGateway;
use App\Services\Payment\Gateways\StripeGateway;
use App\Services\Payment\Processors\PaymentProcessor;
use App\Services\Payment\Validators\PaymentValidator;
use App\Services\Request as ServiceRequest;
use Illuminate\Support\Facades\Log;

/**
 * Main Payment Service.
 * 
 * Orchestrates payment processing across different gateways.
 * Provides a unified interface for payment operations while
 * maintaining separation of concerns through specialized components.
 * 
 * Features:
 * - Multi-gateway support (PayPal, Stripe)
 * - Payment processing and verification
 * - Webhook handling
 * - License and invoice creation
 * - Comprehensive error handling
 * - PSR-12 compliant code structure
 */
class PaymentService
{
    private PaymentValidator $validator;
    private PaymentProcessor $processor;
    private array $gateways;

    public function __construct(
        PaymentValidator $validator,
        PaymentProcessor $processor
    ) {
        $this->validator = $validator;
        $this->processor = $processor;
        $this->initializeGateways();
    }

    /**
     * Process payment with specified gateway.
     * 
     * @param array $orderData Order data containing user_id, amount, currency, etc.
     * @param string $gateway Payment gateway ('paypal' or 'stripe')
     * @return array Payment processing result
     * @throws \InvalidArgumentException When order data or gateway is invalid
     * @throws \Exception When payment processing fails
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        try {
            $this->validator->validateOrderData($orderData);
            $this->validator->validateGateway($gateway);

            $gatewayInstance = $this->getGateway($gateway);
            return $gatewayInstance->processPayment($orderData);
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'gateway' => $gateway,
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verify payment with specified gateway.
     * 
     * @param string $gateway Payment gateway
     * @param string $transactionId Transaction ID to verify
     * @return array Payment verification result
     * @throws \InvalidArgumentException When gateway or transaction ID is invalid
     * @throws \Exception When payment verification fails
     */
    public function verifyPayment(string $gateway, string $transactionId): array
    {
        try {
            $this->validator->validateGateway($gateway);
            $this->validator->validateTransactionId($transactionId);

            $gatewayInstance = $this->getGateway($gateway);
            return $gatewayInstance->verifyPayment($transactionId);
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create license and invoice for payment.
     * 
     * @param array $orderData Order data
     * @param string $gateway Payment gateway
     * @param string|null $transactionId Transaction ID
     * @return array License and invoice creation result
     * @throws \InvalidArgumentException When order data is invalid
     * @throws \Exception When license/invoice creation fails
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            $this->validator->validateOrderData($orderData);
            $this->validator->validateGateway($gateway);

            return $this->processor->createLicenseAndInvoice($orderData, $gateway, $transactionId);
        } catch (\Exception $e) {
            Log::error('License and invoice creation failed', [
                'order_data' => $orderData,
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Handle webhook from payment gateway.
     * 
     * @param ServiceRequest $request Webhook request
     * @param string $gateway Payment gateway
     * @return array Webhook processing result
     * @throws \InvalidArgumentException When gateway is invalid
     * @throws \Exception When webhook processing fails
     */
    public function handleWebhook(ServiceRequest $request, string $gateway): array
    {
        try {
            $this->validator->validateGateway($gateway);

            $gatewayInstance = $this->getGateway($gateway);
            return $gatewayInstance->handleWebhook($request->all());
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get available payment gateways.
     * 
     * @return array List of available gateways
     */
    public function getAvailableGateways(): array
    {
        $available = [];
        
        foreach ($this->gateways as $name => $gateway) {
            if ($gateway->isConfigured()) {
                $available[] = $name;
            }
        }

        return $available;
    }

    /**
     * Check if gateway is available.
     * 
     * @param string $gateway Gateway name
     * @return bool True if gateway is available and configured
     */
    public function isGatewayAvailable(string $gateway): bool
    {
        if (!isset($this->gateways[$gateway])) {
            return false;
        }

        return $this->gateways[$gateway]->isConfigured();
    }

    /**
     * Initialize payment gateways.
     */
    private function initializeGateways(): void
    {
        $this->gateways = [
            'paypal' => app(PayPalGateway::class),
            'stripe' => app(StripeGateway::class),
        ];
    }

    /**
     * Get gateway instance.
     * 
     * @param string $gateway Gateway name
     * @return GatewayInterface Gateway instance
     * @throws \InvalidArgumentException When gateway is not supported
     */
    private function getGateway(string $gateway): GatewayInterface
    {
        if (!isset($this->gateways[$gateway])) {
            throw new \InvalidArgumentException("Unsupported payment gateway: {$gateway}");
        }

        return $this->gateways[$gateway];
    }
}
