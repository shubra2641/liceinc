<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Simplified Payment Service
 */
class PaymentService
{
    /**
     * Process payment
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        try {
            $this->validateOrder($orderData);
            
            if ($gateway === 'paypal') {
                return $this->processPayPal($orderData);
            }
            
            if ($gateway === 'stripe') {
                return $this->processStripe($orderData);
            }
            
            return ['success' => false, 'message' => 'Unsupported gateway'];
        } catch (\Exception $e) {
            Log::error('Payment failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Process PayPal payment
     */
    private function processPayPal(array $orderData): array
    {
        try {
            $settings = PaymentSetting::getByGateway('paypal');
            if (!$settings) {
                throw new \Exception('PayPal settings not found');
            }

            $apiContext = $this->getPayPalContext($settings);
            $payment = $this->createPayPalPayment($orderData, $apiContext);
            $payment->create($apiContext);

            $approvalUrl = $this->getApprovalUrl($payment);

            return [
                'success' => true,
                'redirect_url' => $approvalUrl,
                'payment_id' => $payment->getId(),
            ];
        } catch (\Exception $e) {
            Log::error('PayPal payment failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'PayPal payment failed'];
        }
    }

    /**
     * Process Stripe payment
     */
    private function processStripe(array $orderData): array
    {
        try {
            $settings = PaymentSetting::getByGateway('stripe');
            if (!$settings) {
                throw new \Exception('Stripe settings not found');
            }

            Stripe::setApiKey($settings->credentials['secret_key']);
            $session = $this->createStripeSession($orderData);

            return [
                'success' => true,
                'redirect_url' => $session->url,
                'session_id' => $session->id,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Stripe payment failed'];
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment(string $gateway, string $transactionId): array
    {
        try {
            if ($gateway === 'paypal') {
                return $this->verifyPayPal($transactionId);
            }
            
            if ($gateway === 'stripe') {
                return $this->verifyStripe($transactionId);
            }
            
            return ['success' => false, 'message' => 'Unsupported gateway'];
        } catch (\Exception $e) {
            Log::error('Payment verification failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Verification failed'];
        }
    }

    /**
     * Verify PayPal payment
     */
    private function verifyPayPal(string $paymentId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('paypal');
            $apiContext = $this->getPayPalContext($settings);
            $payment = Payment::get($paymentId, $apiContext);

            if ($payment->getState() === 'approved') {
                $execution = new PaymentExecution();
                $execution->setPayerId(request()->get('PayerID'));
                $result = $payment->execute($execution, $apiContext);

                if ($result->getState() === 'approved') {
                    return ['success' => true, 'transaction_id' => $paymentId];
                }
            }

            return ['success' => false, 'message' => 'Payment not approved'];
        } catch (\Exception $e) {
            Log::error('PayPal verification failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'PayPal verification failed'];
        }
    }

    /**
     * Verify Stripe payment
     */
    private function verifyStripe(string $sessionId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('stripe');
            Stripe::setApiKey($settings->credentials['secret_key']);
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                return ['success' => true, 'transaction_id' => $sessionId];
            }

            return ['success' => false, 'message' => 'Payment not completed'];
        } catch (\Exception $e) {
            Log::error('Stripe verification failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Stripe verification failed'];
        }
    }

    /**
     * Create license and invoice
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            DB::beginTransaction();

            $user = User::find($orderData['user_id']);
            if (!$user) {
                throw new \Exception('User not found');
            }

            // Handle existing invoice
            if (isset($orderData['invoice_id'])) {
                $invoice = Invoice::find($orderData['invoice_id']);
                if ($invoice) {
                    $invoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'notes' => "Payment via {$gateway}",
                    ]);
                    DB::commit();
                    return ['success' => true, 'invoice' => $invoice];
                }
            }

            // Create new license and invoice
            if (isset($orderData['product_id'])) {
                $product = Product::find($orderData['product_id']);
                if (!$product) {
                    throw new \Exception('Product not found');
                }

                $license = $this->createLicense($user, $product, $gateway);
                $invoice = $this->createInvoice($user, $license, $product, $orderData, $gateway, $transactionId);

                DB::commit();
                return ['success' => true, 'license' => $license, 'invoice' => $invoice];
            }

            throw new \Exception('Product not found');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create license and invoice', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validate order data
     */
    private function validateOrder(array $orderData): void
    {
        if (empty($orderData['user_id']) || !is_numeric($orderData['user_id'])) {
            throw new \Exception('Valid user_id is required');
        }

        if (empty($orderData['amount']) || !is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new \Exception('Valid amount is required');
        }

        if (empty($orderData['currency']) || strlen($orderData['currency']) !== 3) {
            throw new \Exception('Valid currency is required');
        }
    }

    /**
     * Get PayPal API context
     */
    private function getPayPalContext($settings): ApiContext
    {
        $credentials = $settings->credentials;
        $apiContext = new ApiContext(
            new OAuthTokenCredential($credentials['client_id'], $credentials['client_secret'])
        );

        $apiContext->setConfig([
            'mode' => $settings->is_sandbox ? 'sandbox' : 'live',
        ]);

        return $apiContext;
    }

    /**
     * Create PayPal payment
     */
    private function createPayPalPayment(array $orderData, ApiContext $apiContext): Payment
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal(number_format($orderData['amount'], 2, '.', ''));
        $amount->setCurrency($orderData['currency']);

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Product Purchase');

        $redirectUrls = new RedirectUrls();
        $appUrl = config('app.url');
        $redirectUrls->setReturnUrl($appUrl . '/payment/success/paypal')
            ->setCancelUrl($appUrl . '/payment/cancel/paypal');

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->setRedirectUrls($redirectUrls);

        return $payment;
    }

    /**
     * Get PayPal approval URL
     */
    private function getApprovalUrl(Payment $payment): ?string
    {
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        return null;
    }

    /**
     * Create Stripe session
     */
    private function createStripeSession(array $orderData): Session
    {
        $appUrl = config('app.url');
        
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $orderData['currency'],
                        'product_data' => ['name' => 'Product Purchase'],
                        'unit_amount' => (int)($orderData['amount'] * 100),
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $appUrl . '/payment/success/stripe',
            'cancel_url' => $appUrl . '/payment/cancel/stripe',
        ]);
    }

    /**
     * Create license
     */
    private function createLicense(User $user, Product $product, string $gateway): License
    {
        return License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_type' => $product->license_type ?? 'single',
            'status' => 'active',
            'max_domains' => $product->max_domains ?? 1,
            'license_expires_at' => $this->calculateExpiry($product),
            'support_expires_at' => now()->addDays(365),
            'notes' => "Purchased via {$gateway}",
        ]);
    }

    /**
     * Create invoice
     */
    private function createInvoice(User $user, License $license, Product $product, array $orderData, string $gateway, ?string $transactionId): Invoice
    {
        return Invoice::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_id' => $license->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'],
            'status' => 'paid',
            'paid_at' => now(),
            'due_date' => now()->addDays(30),
            'notes' => "Payment via {$gateway}",
            'metadata' => [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
            ],
        ]);
    }

    /**
     * Generate invoice number
     */
    private function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber = 'INV-' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }

    /**
     * Calculate license expiry
     */
    private function calculateExpiry(Product $product): ?\DateTime
    {
        if ($product->license_type === 'lifetime') {
            return null;
        }

        $days = match ($product->renewal_period) {
            'monthly' => 30,
            'quarterly' => 90,
            'semi-annual' => 180,
            'annual' => 365,
            'three-years' => 1095,
            default => 365,
        };

        return now()->addDays($days);
    }
}