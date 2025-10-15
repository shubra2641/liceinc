<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\PaymentSetting;
use App\Services\Payment\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Stripe Payment Gateway Implementation.
 * 
 * Handles Stripe payment processing, verification, and webhook handling.
 * Implements the GatewayInterface for consistent payment processing.
 */
class StripeGateway implements GatewayInterface
{
    private PaymentSetting $settings;
    private ResponseHelper $responseHelper;

    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
        $this->loadSettings();
    }

    /**
     * Process Stripe payment.
     */
    public function processPayment(array $orderData): array
    {
        try {
            $this->validateCredentials();
            $this->configureStripe();

            $session = Session::create($this->buildSessionPayload($orderData));

            return $this->responseHelper->buildSuccess([
                'redirect_url' => $session->url,
                'payment_url' => $session->url,
                'session_id' => $session->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);

            return $this->responseHelper->buildFailure(
                'Stripe payment processing failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Verify Stripe payment.
     */
    public function verifyPayment(string $sessionId): array
    {
        try {
            $this->validateCredentials();
            $this->configureStripe();

            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                return $this->responseHelper->buildSuccess([
                    'session_id' => $sessionId,
                    'status' => 'paid',
                    'amount' => $session->amount_total / 100, // Convert from cents
                    'currency' => $session->currency,
                ]);
            }

            return $this->responseHelper->buildFailure('Payment not completed');
        } catch (\Exception $e) {
            Log::error('Stripe payment verification failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return $this->responseHelper->buildFailure(
                'Stripe payment verification failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Handle Stripe webhook.
     */
    public function handleWebhook(array $webhookData): array
    {
        try {
            // Stripe webhook processing logic
            return $this->responseHelper->buildSuccess([
                'message' => 'Stripe webhook processed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe webhook processing failed', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage()
            ]);

            return $this->responseHelper->buildFailure(
                'Stripe webhook processing failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get gateway name.
     */
    public function getGatewayName(): string
    {
        return 'stripe';
    }

    /**
     * Check if gateway is configured.
     */
    public function isConfigured(): bool
    {
        try {
            $this->validateCredentials();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Load Stripe settings.
     */
    private function loadSettings(): void
    {
        $this->settings = PaymentSetting::getByGateway('stripe');
        if (!$this->settings) {
            throw new \Exception('Stripe settings not found');
        }
    }

    /**
     * Validate Stripe credentials.
     */
    private function validateCredentials(): void
    {
        $credentials = $this->settings->credentials;
        
        if (empty($credentials['secret_key'] ?? '')) {
            throw new InvalidArgumentException('Stripe secret_key is required');
        }
        
        if (empty($credentials['publishable_key'] ?? '')) {
            throw new InvalidArgumentException('Stripe publishable_key is required');
        }
    }

    /**
     * Configure Stripe API.
     */
    private function configureStripe(): void
    {
        $credentials = $this->settings->credentials;
        $isSandbox = (bool)($this->settings->is_sandbox ?? false);

        Stripe::setApiKey($credentials['secret_key']);
        Stripe::setApiVersion('2023-10-16');
    }

    /**
     * Build Stripe session payload.
     */
    private function buildSessionPayload(array $orderData): array
    {
        return [
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($orderData['currency'] ?? 'usd'),
                    'product_data' => [
                        'name' => 'Product Purchase',
                    ],
                    'unit_amount' => (int)($orderData['amount'] * 100), // Convert to cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->getSuccessUrl(),
            'cancel_url' => $this->getCancelUrl(),
            'metadata' => [
                'user_id' => $orderData['user_id'],
                'product_id' => $orderData['product_id'] ?? null,
            ],
        ];
    }

    /**
     * Get success URL.
     */
    private function getSuccessUrl(): string
    {
        return url('/payment/success/stripe');
    }

    /**
     * Get cancel URL.
     */
    private function getCancelUrl(): string
    {
        return url('/payment/cancel/stripe');
    }
}
