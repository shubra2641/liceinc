<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Log;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

/**
 * PayPal Payment Handler
 * 
 * Handles all PayPal payment processing logic
 */
class PayPalPaymentHandler
{
    private ApiContext $apiContext;

    public function __construct(
        private InvoiceService $invoiceService
    ) {
        $this->initializeApiContext();
    }

    /**
     * Process PayPal payment
     */
    public function processPayment(Invoice $invoice, array $paymentData): array
    {
        try {
            $this->validatePayPalData($paymentData);
            
            if (isset($paymentData['payment_id']) && isset($paymentData['payer_id'])) {
                return $this->executePayment($invoice, $paymentData);
            }
            
            return $this->createPayment($invoice, $paymentData);
            
        } catch (\Exception $e) {
            Log::error('PayPal payment error: ' . $e->getMessage(), [
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
     * Create PayPal payment
     */
    private function createPayment(Invoice $invoice, array $paymentData): array
    {
        $payer = $this->createPayer();
        $amount = $this->createAmount($invoice);
        $transaction = $this->createTransaction($invoice, $amount);
        $redirectUrls = $this->createRedirectUrls($invoice);
        
        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        $payment->create($this->apiContext);
        
        return [
            'success' => true,
            'redirect_url' => $this->getApprovalUrl($payment),
            'payment_id' => $payment->getId()
        ];
    }

    /**
     * Execute PayPal payment
     */
    private function executePayment(Invoice $invoice, array $paymentData): array
    {
        $payment = Payment::get($paymentData['payment_id'], $this->apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($paymentData['payer_id']);
        
        $result = $payment->execute($execution, $this->apiContext);
        
        if ($result->getState() === 'approved') {
            $this->invoiceService->markAsPaid($invoice, [
                'transaction_id' => $result->getId(),
                'payment_method' => 'paypal',
                'raw_response' => $result->toArray()
            ]);
            
            return [
                'success' => true,
                'message' => 'Payment processed successfully',
                'transaction_id' => $result->getId()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Payment not approved'
        ];
    }

    /**
     * Initialize PayPal API context
     */
    private function initializeApiContext(): void
    {
        $clientId = config('services.paypal.client_id');
        $clientSecret = config('services.paypal.client_secret');
        $mode = config('services.paypal.mode', 'sandbox');
        
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($clientId, $clientSecret)
        );
        
        $this->apiContext->setConfig([
            'mode' => $mode,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'INFO',
        ]);
    }

    /**
     * Create PayPal payer
     */
    private function createPayer(): Payer
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        return $payer;
    }

    /**
     * Create amount object
     */
    private function createAmount(Invoice $invoice): Amount
    {
        $amount = new Amount();
        $amount->setCurrency(strtoupper($invoice->currency ?? 'USD'))
            ->setTotal(number_format($invoice->total_amount, 2, '.', ''));
        return $amount;
    }

    /**
     * Create transaction
     */
    private function createTransaction(Invoice $invoice, Amount $amount): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription("Payment for Invoice #{$invoice->invoice_number}")
            ->setInvoiceNumber($invoice->invoice_number);
        return $transaction;
    }

    /**
     * Create redirect URLs
     */
    private function createRedirectUrls(Invoice $invoice): RedirectUrls
    {
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('payment.paypal.success', $invoice->id))
            ->setCancelUrl(route('payment.paypal.cancel', $invoice->id));
        return $redirectUrls;
    }

    /**
     * Get approval URL from payment
     */
    private function getApprovalUrl(Payment $payment): string
    {
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        throw new \Exception('Approval URL not found');
    }

    /**
     * Validate PayPal data
     */
    private function validatePayPalData(array $data): void
    {
        if (empty($data['payment_id']) && empty($data['payer_id'])) {
            // This is a new payment request, no validation needed
            return;
        }
        
        if (empty($data['payment_id']) || empty($data['payer_id'])) {
            throw new \InvalidArgumentException('Payment ID and Payer ID are required for payment execution');
        }
    }
}
