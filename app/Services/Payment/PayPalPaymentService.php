<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\PaymentSetting;
use Illuminate\Support\Facades\Log;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

/**
 * PayPal Payment Service - Handles PayPal-specific payment operations.
 */
class PayPalPaymentService
{
    /**
     * Process PayPal payment.
     */
    public function processPayment(array $orderData): array
    {
        try {
            $settings = $this->getSettingsOrFail('paypal');
            $credentials = $settings->credentials;
            $this->validateCredentials($credentials);

            $apiContext = $this->createApiContext($credentials, (bool)($settings->is_sandbox ?? false));
            $payment = $this->createPayment($orderData, $apiContext);

            return $this->buildSuccessResponse($payment);
        } catch (\Exception $e) {
            Log::error('PayPal payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);

            return $this->buildFailureResponse('PayPal payment processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify PayPal payment.
     */
    public function verifyPayment(string $paymentId, string $payerId): array
    {
        try {
            $settings = $this->getSettingsOrFail('paypal');
            $credentials = $settings->credentials;
            $this->validateCredentials($credentials);

            $apiContext = $this->createApiContext($credentials, (bool)($settings->is_sandbox ?? false));
            $payment = Payment::get($paymentId, $apiContext);

            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $apiContext);

            if ($result->getState() === 'approved') {
                return $this->buildSuccessResponse([
                    'transaction_id' => $paymentId,
                    'status' => 'approved',
                    'amount' => $this->extractAmount($result),
                ]);
            }

            return $this->buildFailureResponse('Payment not approved');
        } catch (\Exception $e) {
            Log::error('PayPal payment verification failed', [
                'payment_id' => $paymentId,
                'payer_id' => $payerId,
                'error' => $e->getMessage()
            ]);

            return $this->buildFailureResponse('PayPal payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Create PayPal API context.
     */
    private function createApiContext(array $credentials, bool $isSandbox): ApiContext
    {
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
            'cache.enabled' => true,
            'cache.FileName' => storage_path('logs/paypal.cache'),
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
        $amount->setCurrency($orderData['currency'] ?? 'usd');

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
     * Build success response.
     */
    private function buildSuccessResponse(Payment $payment): array
    {
        $approvalUrl = $this->extractApprovalUrl($payment);

        return [
            'success' => true,
            'redirect_url' => $approvalUrl,
            'payment_url' => $approvalUrl,
            'payment_id' => $payment->getId(),
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
     * Extract approval URL from payment links.
     */
    private function extractApprovalUrl(Payment $payment): ?string
    {
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        return null;
    }

    /**
     * Extract amount from payment result.
     */
    private function extractAmount($result): float
    {
        $transactions = $result->getTransactions();
        if (!empty($transactions)) {
            $amount = $transactions[0]->getAmount();
            return (float) $amount->getTotal();
        }
        return 0.0;
    }

    /**
     * Get success URL.
     */
    private function getSuccessUrl(): string
    {
        return route('payment.success', ['gateway' => 'paypal']);
    }

    /**
     * Get cancel URL.
     */
    private function getCancelUrl(): string
    {
        return route('payment.cancel', ['gateway' => 'paypal']);
    }

    /**
     * Validate PayPal credentials.
     */
    private function validateCredentials(?array $credentials): void
    {
        if (empty($credentials['client_id'] ?? '')) {
            throw new \Exception('PayPal client_id is required');
        }

        if (empty($credentials['client_secret'] ?? '')) {
            throw new \Exception('PayPal client_secret is required');
        }
    }

    /**
     * Retrieve PaymentSetting for PayPal or fail.
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
