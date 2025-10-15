<?php

declare(strict_types=1);

namespace App\Services\Payment\Gateways;

use App\Models\PaymentSetting;
use App\Services\Payment\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

/**
 * PayPal Payment Gateway Implementation.
 * 
 * Handles PayPal payment processing, verification, and webhook handling.
 * Implements the GatewayInterface for consistent payment processing.
 */
class PayPalGateway implements GatewayInterface
{
    private PaymentSetting $settings;
    private ResponseHelper $responseHelper;

    public function __construct(ResponseHelper $responseHelper)
    {
        $this->responseHelper = $responseHelper;
        $this->loadSettings();
    }

    /**
     * Process PayPal payment.
     */
    public function processPayment(array $orderData): array
    {
        try {
            $this->validateCredentials();
            
            $apiContext = $this->createApiContext();
            $payment = $this->createPayment($orderData, $apiContext);
            $approvalUrl = $this->getApprovalUrl($payment);

            return $this->responseHelper->buildSuccess([
                'redirect_url' => $approvalUrl,
                'payment_url' => $approvalUrl,
                'payment_id' => $payment->getId(),
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);

            return $this->responseHelper->buildFailure(
                'PayPal payment processing failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Verify PayPal payment.
     */
    public function verifyPayment(string $paymentId): array
    {
        try {
            $this->validateCredentials();
            
            $apiContext = $this->createApiContext();
            $payment = Payment::get($paymentId, $apiContext);

            if ($payment->getState() === 'approved') {
                return $this->responseHelper->buildSuccess([
                    'payment_id' => $paymentId,
                    'status' => 'approved',
                    'amount' => $this->extractAmount($payment),
                    'currency' => $this->extractCurrency($payment),
                ]);
            }

            return $this->responseHelper->buildFailure('Payment not approved');
        } catch (\Exception $e) {
            Log::error('PayPal payment verification failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return $this->responseHelper->buildFailure(
                'PayPal payment verification failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Handle PayPal webhook.
     */
    public function handleWebhook(array $webhookData): array
    {
        try {
            // PayPal webhook processing logic
            return $this->responseHelper->buildSuccess([
                'message' => 'PayPal webhook processed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal webhook processing failed', [
                'webhook_data' => $webhookData,
                'error' => $e->getMessage()
            ]);

            return $this->responseHelper->buildFailure(
                'PayPal webhook processing failed: ' . $e->getMessage()
            );
        }
    }

    /**
     * Get gateway name.
     */
    public function getGatewayName(): string
    {
        return 'paypal';
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
     * Load PayPal settings.
     */
    private function loadSettings(): void
    {
        $this->settings = PaymentSetting::getByGateway('paypal');
        if (!$this->settings) {
            throw new \Exception('PayPal settings not found');
        }
    }

    /**
     * Validate PayPal credentials.
     */
    private function validateCredentials(): void
    {
        $credentials = $this->settings->credentials;
        
        if (empty($credentials['client_id'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_id is required');
        }
        
        if (empty($credentials['client_secret'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_secret is required');
        }
    }

    /**
     * Create PayPal API context.
     */
    private function createApiContext(): ApiContext
    {
        $credentials = $this->settings->credentials;
        $isSandbox = (bool)($this->settings->is_sandbox ?? false);

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $credentials['client_id'],
                $credentials['client_secret']
            )
        );

        $apiContext->setConfig([
            'mode' => $isSandbox ? 'sandbox' : 'live',
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'INFO',
        ]);

        return $apiContext;
    }

    /**
     * Create PayPal payment object.
     */
    private function createPayment(array $orderData, ApiContext $apiContext): Payment
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal(number_format($orderData['amount'], 2, '.', ''));
        $amount->setCurrency($orderData['currency'] ?? 'USD');

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Product Purchase');
        $transaction->setCustom("user_id:{$orderData['user_id']}, product_id:{$orderData['product_id']}");

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($this->getSuccessUrl())
            ->setCancelUrl($this->getCancelUrl());

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->setRedirectUrls($redirectUrls);

        return $payment->create($apiContext);
    }

    /**
     * Get PayPal approval URL.
     */
    private function getApprovalUrl(Payment $payment): string
    {
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        
        throw new \Exception('PayPal approval URL not found');
    }

    /**
     * Extract amount from payment.
     */
    private function extractAmount(Payment $payment): float
    {
        $transactions = $payment->getTransactions();
        if (empty($transactions)) {
            return 0.0;
        }

        $amount = $transactions[0]->getAmount();
        return (float)$amount->getTotal();
    }

    /**
     * Extract currency from payment.
     */
    private function extractCurrency(Payment $payment): string
    {
        $transactions = $payment->getTransactions();
        if (empty($transactions)) {
            return 'USD';
        }

        $amount = $transactions[0]->getAmount();
        return $amount->getCurrency();
    }

    /**
     * Get success URL.
     */
    private function getSuccessUrl(): string
    {
        return url('/payment/success/paypal');
    }

    /**
     * Get cancel URL.
     */
    private function getCancelUrl(): string
    {
        return url('/payment/cancel/paypal');
    }
}
