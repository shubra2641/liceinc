<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\PaymentSetting;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

/**
 * Stripe Payment Handler
 * 
 * Handles all Stripe payment processing logic
 */
class StripePaymentHandler
{
    public function __construct(
        private InvoiceService $invoiceService,
        private StripeClient $stripeClient
    ) {
    }

    /**
     * Process Stripe payment
     */
    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        try {
            $this->validateStripeData($paymentData);
            
            $stripeData = $this->prepareStripeData($invoice, $paymentData);
            $paymentIntent = $this->createPaymentIntent($stripeData);
            
            return $this->handlePaymentResult($paymentIntent, $invoice);
            
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate Stripe payment data
     */
    private function validateStripeData(array $data): void
    {
        if (empty($data['stripe_token'])) {
            throw new \InvalidArgumentException('Stripe token is required');
        }
    }

    /**
     * Prepare data for Stripe API
     */
    private function prepareStripeData(Invoice $invoice, array $paymentData): array
    {
        return [
            'amount' => $this->convertToCents($invoice->total_amount),
            'currency' => strtolower($invoice->currency ?? 'usd'),
            'payment_method' => $paymentData['stripe_token'],
            'confirmation_method' => 'manual',
            'confirm' => true,
            'metadata' => [
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
            ]
        ];
    }

    /**
     * Create Stripe payment intent
     */
    private function createPaymentIntent(array $data): \Stripe\PaymentIntent
    {
        return $this->stripeClient->paymentIntents->create($data);
    }

    /**
     * Handle payment result
     */
    private function handlePaymentResult(\Stripe\PaymentIntent $paymentIntent, Invoice $invoice): array
    {
        if ($paymentIntent->status === 'succeeded') {
            $this->invoiceService->markAsPaid($invoice, [
                'transaction_id' => $paymentIntent->id,
                'payment_method' => 'stripe',
                'raw_response' => $paymentIntent->toArray()
            ]);
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $paymentIntent->id
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Payment not completed',
            'requires_action' => $paymentIntent->status === 'requires_action'
        ];
    }

    /**
     * Convert amount to cents
     */
    private function convertToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
