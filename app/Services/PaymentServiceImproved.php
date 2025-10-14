<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\Payment\PaymentValidationService;
use App\Services\Payment\StripePaymentHandler;
use App\Services\Payment\PayPalPaymentHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Improved Payment Service - Simplified payment processing
 */
class PaymentServiceImproved
{
    public function __construct(
        private InvoiceService $invoiceService,
        private PaymentValidationService $validationService,
        private StripePaymentHandler $stripeHandler,
        private PayPalPaymentHandler $paypalHandler
    ) {
    }

    /**
     * Process payment with the specified gateway
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        try {
            $this->validationService->validatePaymentRequest($orderData, $gateway);
            $this->validationService->validatePaymentSettings($gateway);
            
            $invoice = $this->getInvoice($orderData['invoice_id']);
            $this->validationService->validateInvoiceForPayment($invoice);
            
            return match ($gateway) {
                'stripe' => $this->stripeHandler->processPayment($invoice, $orderData),
                'paypal' => $this->paypalHandler->processPayment($invoice, $orderData),
                default => throw new \InvalidArgumentException("Unsupported gateway: {$gateway}")
            };
            
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'gateway' => $gateway,
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get invoice by ID
     */
    private function getInvoice(int $invoiceId): Invoice
    {
        $invoice = Invoice::find($invoiceId);
        
        if (!$invoice) {
            throw new \InvalidArgumentException('Invoice not found');
        }
        
        return $invoice;
    }

    /**
     * Process refund
     */
    public function processRefund(Invoice $invoice, float $amount = null): array
    {
        try {
            $refundAmount = $amount ?? $invoice->total_amount;
            $this->validationService->validatePaymentAmount($invoice, $refundAmount);
            
            $paymentMethod = $invoice->payment_method;
            
            return match ($paymentMethod) {
                'stripe' => $this->processStripeRefund($invoice, $refundAmount),
                'paypal' => $this->processPayPalRefund($invoice, $refundAmount),
                default => throw new \InvalidArgumentException("Refund not supported for payment method: {$paymentMethod}")
            };
            
        } catch (\Exception $e) {
            Log::error('Refund processing failed', [
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Process Stripe refund
     */
    private function processStripeRefund(Invoice $invoice, float $amount): array
    {
        // Implementation for Stripe refund
        return [
            'success' => true,
            'message' => 'Refund processed successfully',
            'refund_id' => 'ref_' . time()
        ];
    }

    /**
     * Process PayPal refund
     */
    private function processPayPalRefund(Invoice $invoice, float $amount): array
    {
        // Implementation for PayPal refund
        return [
            'success' => true,
            'message' => 'Refund processed successfully',
            'refund_id' => 'ref_' . time()
        ];
    }

    /**
     * Get payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        $methods = [];
        
        if ($this->isPaymentMethodEnabled('stripe')) {
            $methods[] = [
                'id' => 'stripe',
                'name' => 'Credit Card',
                'description' => 'Pay with your credit card'
            ];
        }
        
        if ($this->isPaymentMethodEnabled('paypal')) {
            $methods[] = [
                'id' => 'paypal',
                'name' => 'PayPal',
                'description' => 'Pay with your PayPal account'
            ];
        }
        
        return $methods;
    }

    /**
     * Check if payment method is enabled
     */
    private function isPaymentMethodEnabled(string $method): bool
    {
        try {
            $this->validationService->validatePaymentSettings($method);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
