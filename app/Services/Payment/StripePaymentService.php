<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Stripe Payment Service - Handles Stripe-specific payment operations.
 */
class StripePaymentService
{
    /**
     * Process Stripe payment.
     */
    public function processPayment(array $orderData): array
    {
        try {
            $settings = $this->getSettingsOrFail('stripe');
            $credentials = $settings->credentials;
            $this->validateCredentials($credentials);

            Stripe::setApiKey($credentials['secret_key'] ?? '');
            $session = $this->createCheckoutSession($orderData);

            return $this->buildSuccessResponse($session);
        } catch (\Exception $e) {
            Log::error('Stripe payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);

            return $this->buildFailureResponse('Stripe payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify Stripe payment.
     */
    public function verifyPayment(string $sessionId): array
    {
        try {
            $settings = $this->getSettingsOrFail('stripe');
            $credentials = $settings->credentials;
            $this->validateCredentials($credentials);

            Stripe::setApiKey($credentials['secret_key'] ?? '');
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                return $this->buildSuccessResponse([
                    'transaction_id' => $sessionId,
                    'status' => 'paid',
                    'amount' => $session->amount_total / 100,
                ]);
            }

            return $this->buildFailureResponse('Payment not completed');
        } catch (\Exception $e) {
            Log::error('Stripe payment verification failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);

            return $this->buildFailureResponse('Stripe payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Create Stripe checkout session.
     */
    private function createCheckoutSession(array $orderData): Session
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $orderData['currency'] ?? 'usd',
                        'product_data' => [
                            'name' => 'Product Purchase',
                        ],
                        'unit_amount' => (int)(($orderData['amount'] ?? 0) * 100),
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $this->getSuccessUrl(),
            'cancel_url' => $this->getCancelUrl(),
            'metadata' => [
                'user_id' => $orderData['user_id'],
                'product_id' => $orderData['product_id'] ?? null,
            ],
        ]);
    }

    /**
     * Build success response.
     */
    private function buildSuccessResponse(Session $session): array
    {
        return [
            'success' => true,
            'redirect_url' => $session->url,
            'payment_url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    /**
     * Build failure response.
     */
    private function buildFailureResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    /**
     * Get success URL.
     */
    private function getSuccessUrl(): string
    {
        return route('payment.success', ['gateway' => 'stripe']);
    }

    /**
     * Get cancel URL.
     */
    private function getCancelUrl(): string
    {
        return route('payment.cancel', ['gateway' => 'stripe']);
    }

    /**
     * Validate Stripe credentials.
     */
    private function validateCredentials(?array $credentials): void
    {
        if (empty($credentials['secret_key'] ?? '')) {
            throw new \Exception('Stripe secret_key is required');
        }

        if (empty($credentials['publishable_key'] ?? '')) {
            throw new \Exception('Stripe publishable_key is required');
        }
    }

    /**
     * Retrieve PaymentSetting for Stripe or fail.
     */
    private function getSettingsOrFail(string $gateway): PaymentSetting
    {
        $settings = PaymentSetting::getByGateway($gateway);
        if (!$settings) {
            throw new \Exception(ucfirst($gateway) . ' settings not found');
        }
        return $settings;
    }
}
