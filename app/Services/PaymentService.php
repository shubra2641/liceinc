<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use App\Services\Request as ServiceRequest;
use Illuminate\Support\Facades\DB;
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
use Stripe\Checkout\Session;
use Stripe\Stripe;

/**
 * Simplified Payment Service with essential functionality.
 */
class PaymentService
{
    /**
     * Process payment with the specified gateway.
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        try {
            $this->validateOrderData($orderData);
            $this->validateGateway($gateway);

            return match ($gateway) {
                'paypal' => $this->processPayPalPayment($orderData),
                'stripe' => $this->processStripePayment($orderData),
                default => throw new InvalidArgumentException("Unsupported gateway: {$gateway}")
            };
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
     * Process PayPal payment.
     */
    protected function processPayPalPayment(array $orderData): array
    {
        try {
            $settings = $this->getPayPalSettings();
            $apiContext = $this->createPayPalApiContext($settings);
            $payment = $this->createPayPalPayment($orderData, $apiContext);
            
            return $this->handlePayPalPaymentResult($payment);
            
        } catch (\Exception $e) {
            Log::error('PayPal payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'PayPal payment processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get PayPal settings
     */
    private function getPayPalSettings(): PaymentSetting
    {
        $settings = PaymentSetting::getByGateway('paypal');
        if (!$settings) {
            throw new \Exception('PayPal settings not found');
        }
        return $settings;
    }

    /**
     * Create PayPal API context
     */
    private function createPayPalApiContext(PaymentSetting $settings): ApiContext
    {
        $credentials = $settings->credentials;
        $this->validatePayPalCredentials($credentials);

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $credentials['client_id'],
                $credentials['client_secret']
            )
        );

        $apiContext->setConfig([
            'mode' => $settings->is_sandbox ? 'sandbox' : 'live',
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'INFO',
        ]);

        return $apiContext;
    }

    /**
     * Create PayPal payment
     */
    private function createPayPalPayment(array $orderData, ApiContext $apiContext): Payment
    {
        $payer = $this->createPayPalPayer();
        $amount = $this->createPayPalAmount($orderData);
        $transaction = $this->createPayPalTransaction($orderData, $amount);
        $redirectUrls = $this->createPayPalRedirectUrls();

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->setRedirectUrls($redirectUrls);

        $payment->create($apiContext);
        return $payment;
    }

    /**
     * Handle PayPal payment result
     */
    private function handlePayPalPaymentResult(Payment $payment): array
    {
        $approvalUrl = $this->getPayPalApprovalUrl($payment);

        return [
            'success' => true,
            'redirect_url' => $approvalUrl,
            'payment_url' => $approvalUrl,
            'payment_id' => $payment->getId(),
        ];
    }

    /**
     * Create PayPal payer
     */
    private function createPayPalPayer(): Payer
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        return $payer;
    }

    /**
     * Create PayPal amount
     */
    private function createPayPalAmount(array $orderData): Amount
    {
        $amount = new Amount();
        $amount->setTotal(number_format($orderData['amount'], 2, '.', ''));
        $amount->setCurrency($orderData['currency'] ?? 'usd');
        return $amount;
    }

    /**
     * Create PayPal transaction
     */
    private function createPayPalTransaction(array $orderData, Amount $amount): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Product Purchase');
        $transaction->setCustom("user_id:{$orderData['user_id']}, product_id:{$orderData['product_id']}");
        return $transaction;
    }

    /**
     * Create PayPal redirect URLs
     */
    private function createPayPalRedirectUrls(): RedirectUrls
    {
        $redirectUrls = new RedirectUrls();
        $appUrl = config('app.url');
        $redirectUrls->setReturnUrl($appUrl . '/payment/success/paypal')
            ->setCancelUrl($appUrl . '/payment/cancel/paypal');
        return $redirectUrls;
    }

    /**
     * Get PayPal approval URL
     */
    private function getPayPalApprovalUrl(Payment $payment): string
    {
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        throw new \Exception('PayPal approval URL not found');
    }

    /**
     * Process Stripe payment.
     */
    protected function processStripePayment(array $orderData): array
    {
        try {
            $settings = $this->getStripeSettings();
            $this->configureStripe($settings);
            $session = $this->createStripeSession($orderData);
            
            return $this->handleStripePaymentResult($session);
            
        } catch (\Exception $e) {
            Log::error('Stripe payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Stripe payment processing failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get Stripe settings
     */
    private function getStripeSettings(): PaymentSetting
    {
        $settings = PaymentSetting::getByGateway('stripe');
        if (!$settings) {
            throw new \Exception('Stripe settings not found');
        }
        return $settings;
    }

    /**
     * Configure Stripe API
     */
    private function configureStripe(PaymentSetting $settings): void
    {
        $credentials = $settings->credentials;
        $this->validateStripeCredentials($credentials);
        Stripe::setApiKey($credentials['secret_key'] ?? '');
    }

    /**
     * Create Stripe session
     */
    private function createStripeSession(array $orderData): Session
    {
        $appUrl = config('app.url');
        
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $this->createStripeLineItems($orderData),
            'mode' => 'payment',
            'success_url' => $appUrl . '/payment/success/stripe',
            'cancel_url' => $appUrl . '/payment/cancel/stripe',
            'metadata' => $this->createStripeMetadata($orderData),
        ]);
    }

    /**
     * Handle Stripe payment result
     */
    private function handleStripePaymentResult(Session $session): array
    {
        return [
            'success' => true,
            'redirect_url' => $session->url,
            'payment_url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    /**
     * Create Stripe line items
     */
    private function createStripeLineItems(array $orderData): array
    {
        return [
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
        ];
    }

    /**
     * Create Stripe metadata
     */
    private function createStripeMetadata(array $orderData): array
    {
        return [
            'user_id' => $orderData['user_id'] ?? '',
            'product_id' => $orderData['product_id'] ?? '',
        ];
    }

    /**
     * Verify payment with gateway.
     */
    public function verifyPayment(string $gateway, string $transactionId): array
    {
        try {
            $this->validateGateway($gateway);
            $this->validateTransactionId($transactionId);

            return match ($gateway) {
                'paypal' => $this->verifyPayPalPayment($transactionId),
                'stripe' => $this->verifyStripePayment($transactionId),
                default => [
                    'success' => false,
                    'message' => 'Unsupported payment gateway',
                ]
            };
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Payment verification failed',
            ];
        }
    }

    /**
     * Verify PayPal payment.
     */
    protected function verifyPayPalPayment(string $paymentId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('paypal');
            if (!$settings) {
                throw new \Exception('PayPal settings not found');
            }

            $credentials = $settings->credentials;
            $this->validatePayPalCredentials($credentials);

            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $credentials['client_id'],
                    $credentials['client_secret']
                )
            );

            $apiContext->setConfig([
                'mode' => $settings->is_sandbox ? 'sandbox' : 'live',
            ]);

            $payment = Payment::get($paymentId, $apiContext);

            if ($payment->getState() === 'approved') {
                $execution = new PaymentExecution();
                $payerId = request()->get('PayerID');
                $execution->setPayerId($payerId ?? '');
                $result = $payment->execute($execution, $apiContext);

                if ($result->getState() === 'approved') {
                    return [
                        'success' => true,
                        'transaction_id' => $paymentId,
                        'message' => 'Payment verified successfully',
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Payment not approved',
            ];
        } catch (\Exception $e) {
            Log::error('PayPal verification failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'PayPal verification failed',
            ];
        }
    }

    /**
     * Verify Stripe payment.
     */
    protected function verifyStripePayment(string $transactionId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('stripe');
            if (!$settings) {
                throw new \Exception('Stripe settings not found');
            }

            $credentials = $settings->credentials;
            $this->validateStripeCredentials($credentials);

            Stripe::setApiKey($credentials['secret_key'] ?? '');

            $session = Session::retrieve($transactionId);

            if ($session->payment_status === 'paid') {
                return [
                    'success' => true,
                    'transaction_id' => $transactionId,
                    'message' => 'Payment verified successfully',
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment not completed',
            ];
        } catch (\Exception $e) {
            Log::error('Stripe verification failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Stripe verification failed',
            ];
        }
    }

    /**
     * Create license and invoice after successful payment.
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            $this->validateOrderData($orderData);
            $this->validateGateway($gateway);

            DB::beginTransaction();

            $user = User::find($orderData['user_id']);
            if (!$user) {
                throw new \Exception('User not found');
            }

            $product = isset($orderData['product_id']) ? Product::find($orderData['product_id']) : null;

            // Handle existing invoice
            if (isset($orderData['invoice_id']) && $orderData['invoice_id']) {
                $existingInvoice = Invoice::find($orderData['invoice_id']);
                if ($existingInvoice) {
                    $existingInvoice->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'notes' => "Payment via {$gateway}",
                        'metadata' => array_merge($existingInvoice->metadata ?? [], [
                            'gateway' => $gateway,
                            'transaction_id' => $transactionId,
                        ])
                    ]);

                    DB::commit();
                    return [
                        'success' => true,
                        'license' => $existingInvoice->license,
                        'invoice' => $existingInvoice,
                    ];
                }
            }

            // Handle custom invoice
            if (isset($orderData['is_custom']) && $orderData['is_custom']) {
                $invoice = Invoice::create([
                    'user_id' => $user->id,
                    'product_id' => null,
                    'license_id' => null,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'amount' => $orderData['amount'],
                    'currency' => $orderData['currency'],
                    'status' => 'paid',
                    'paid_at' => now(),
                    'due_date' => now()->addDays(30),
                    'notes' => "Custom service payment via {$gateway}",
                    'metadata' => [
                        'gateway' => $gateway,
                        'transaction_id' => $transactionId,
                        'is_custom' => true,
                    ],
                ]);

                DB::commit();
                return [
                    'success' => true,
                    'license' => null,
                    'invoice' => $invoice,
                ];
            }

            // Create license and invoice for product purchase
            if ($product) {
                $license = License::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'license_type' => $product->license_type ?? 'single',
                    'status' => 'active',
                    'max_domains' => $product->max_domains ?? 1,
                    'license_expires_at' => $this->calculateLicenseExpiry($product),
                    'support_expires_at' => $this->calculateSupportExpiry($product),
                    'notes' => "Purchased via {$gateway}",
                ]);

                $invoiceService = app(InvoiceService::class);
                $invoice = $invoiceService->createInvoice(
                    $user,
                    $license,
                    $product,
                    $orderData['amount'],
                    $orderData['currency'] ?? 'usd',
                    $gateway,
                    $transactionId
                );

                DB::commit();
                return [
                    'success' => true,
                    'license' => $license,
                    'invoice' => $invoice,
                ];
            }

            throw new \Exception('Product not found');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create license and invoice', [
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
     */
    public function handleWebhook(ServiceRequest $request, string $gateway): array
    {
        try {
            $this->validateGateway($gateway);

            return match ($gateway) {
                'stripe' => $this->handleStripeWebhook($request),
                'paypal' => $this->handlePayPalWebhook($request),
                default => ['success' => false, 'message' => 'Unsupported gateway']
            };
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Webhook processing failed'];
        }
    }

    /**
     * Handle Stripe webhook.
     */
    private function handleStripeWebhook(ServiceRequest $request): array
    {
        return ['success' => true, 'message' => 'Stripe webhook processed'];
    }

    /**
     * Handle PayPal webhook.
     */
    private function handlePayPalWebhook(ServiceRequest $request): array
    {
        return ['success' => true, 'message' => 'PayPal webhook processed'];
    }

    /**
     * Validate order data.
     */
    private function validateOrderData(array $orderData): void
    {
        if (empty($orderData)) {
            throw new InvalidArgumentException('Order data cannot be empty');
        }

        if (!isset($orderData['user_id']) || !is_numeric($orderData['user_id']) || $orderData['user_id'] < 1) {
            throw new InvalidArgumentException('Valid user_id is required');
        }

        if (!isset($orderData['amount']) || !is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new InvalidArgumentException('Valid amount is required');
        }

        if (!isset($orderData['currency']) || empty($orderData['currency'])) {
            throw new InvalidArgumentException('Currency is required');
        }

        if (strlen($orderData['currency']) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-character code');
        }

        if ($orderData['amount'] > 999999.99) {
            throw new InvalidArgumentException('Amount cannot exceed 999,999.99');
        }
    }

    /**
     * Validate payment gateway.
     */
    private function validateGateway(string $gateway): void
    {
        if (!in_array($gateway, ['paypal', 'stripe'])) {
            throw new InvalidArgumentException('Unsupported payment gateway');
        }
    }

    /**
     * Validate PayPal credentials.
     */
    private function validatePayPalCredentials(?array $credentials): void
    {
        if (empty($credentials['client_id'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_id is required');
        }
        if (empty($credentials['client_secret'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_secret is required');
        }
    }

    /**
     * Validate Stripe credentials.
     */
    private function validateStripeCredentials(?array $credentials): void
    {
        $secretKey = $credentials['secret_key'] ?? '';
        if (empty($secretKey)) {
            throw new InvalidArgumentException('Stripe secret_key is required');
        }
        if (!str_starts_with($secretKey, 'sk_')) {
            throw new InvalidArgumentException('Invalid Stripe secret key format');
        }
    }

    /**
     * Validate transaction ID.
     */
    private function validateTransactionId(string $transactionId): void
    {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }
        if (strlen($transactionId) < 5 || strlen($transactionId) > 100) {
            throw new InvalidArgumentException('Transaction ID must be between 5 and 100 characters');
        }
        if (!preg_match('/^[A-Za-z0-9\-_]+$/', $transactionId)) {
            throw new InvalidArgumentException('Transaction ID contains invalid characters');
        }
    }

    /**
     * Generate unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber = 'INV-' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }

    /**
     * Calculate license expiry date.
     */
    protected function calculateLicenseExpiry(Product $product): ?\DateTime
    {
        if ($product->license_type === 'lifetime' || $product->renewal_period === 'lifetime') {
            return null;
        }

        $days = $this->getRenewalPeriodInDays($product->renewal_period);
        if ($days === null) {
            $defaultDuration = \App\Helpers\ConfigHelper::getSetting('license_default_duration', 365);
            $days = is_numeric($defaultDuration) ? (int)$defaultDuration : 365;
        }

        return now()->addDays($days);
    }

    /**
     * Calculate support expiry date.
     */
    protected function calculateSupportExpiry(Product $product): \DateTime
    {
        $productSupportDays = $product->support_days ?? null;
        $defaultSupportDuration = \App\Helpers\ConfigHelper::getSetting('license_support_duration', 365);
        $supportDuration = is_numeric($productSupportDays)
            ? (int)$productSupportDays
            : (is_numeric($defaultSupportDuration) ? (int)$defaultSupportDuration : 365);

        return now()->addDays($supportDuration);
    }

    /**
     * Convert renewal period to days.
     */
    protected function getRenewalPeriodInDays(?string $renewalPeriod): ?int
    {
        return match ($renewalPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semi-annual' => 180,
            'annual' => 365,
            'three-years' => 1095,
            'lifetime' => null,
            default => null,
        };
    }
}
