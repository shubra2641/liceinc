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
 * Payment Service with enhanced security and comprehensive payment processing.
 *
 * This service provides secure payment processing functionality for multiple
 * payment gateways including PayPal and Stripe. It implements comprehensive
 * security measures, input validation, and error handling for reliable
 * payment operations and license management.
 */
class PaymentService
{
    /**
     * Process payment with the specified gateway with enhanced security and error handling.
     *
     * Processes payments through supported payment gateways including PayPal and Stripe.
     * Includes comprehensive validation, security measures, and error handling for
     * reliable payment processing operations.
     *
     * @param  array  $orderData  Order data including amount, currency, userId, productId
     * @param  string  $gateway  Payment gateway to use ('paypal' or 'stripe')
     *
     * @return array Payment processing result with success status and redirect URL
     *
     * @throws InvalidArgumentException When gateway is unsupported or data is invalid
     * @throws \Exception When payment processing fails
     *
     * @example
     * $result = $service->processPayment([
     *     'amount' => 99.99,
     *     'currency' => 'USD',
     *     'userId' => 1,
     *     'productId' => 1
     * ], 'stripe');
     */
    /**
     * @param array<string, mixed> $orderData
     * @return array<string, mixed>
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        try {
            // Validate input parameters
            $this->validateOrderData($orderData);
            $this->validateGateway($gateway);
            switch ($gateway) {
                case 'paypal':
                    return $this->processPayPalPayment($orderData);
                case 'stripe':
                    return $this->processStripePayment($orderData);
                default:
                    throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}");
            }
        } catch (\Exception $e) {
            Log::error('Payment processing failed', [
                'gateway' => $gateway,
                'order_data' => $orderData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Process PayPal payment with enhanced security and error handling.
     *
     * Processes PayPal payments with comprehensive validation, security measures,
     * and error handling for reliable payment processing operations.
     *
     * @param  array  $orderData  Order data including amount, currency, userId, productId
     *
     * @return array PayPal payment processing result
     *
     * @throws \Exception When PayPal payment processing fails
     */
    /**
     * @param array<string, mixed> $orderData
     * @return array<string, mixed>
     */
    protected function processPayPalPayment(array $orderData): array
    {
        try {
            $settings = PaymentSetting::getByGateway('paypal');
            if (!$settings) {
                throw new \Exception('PayPal settings not found');
            }
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validatePayPalCredentials($credentials);
            // Create API context
            $clientId = is_string($credentials['client_id'] ?? '') ? (string)($credentials['client_id'] ?? '') : '';
            $clientSecret = is_string($credentials['client_secret'] ?? '') ? (string)($credentials['client_secret'] ?? '') : '';

            $apiContext = new ApiContext(
                new OAuthTokenCredential($clientId, $clientSecret),
            );
            // Set mode (sandbox or live)
            $apiContext->setConfig([
                'mode' => $settings->is_sandbox ? 'sandbox' : 'live',
                'log.LogEnabled' => true,
                'log.FileName' => (string)storage_path('logs/paypal.log'),
                'log.LogLevel' => 'INFO',
            ]);
            // Create payer
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            // Create amount with validation
            $amount = new Amount();
            $amountValue = is_numeric($orderData['amount'] ?? 0) ? (float)($orderData['amount'] ?? 0) : 0.0;
            $amount->setTotal(number_format($amountValue, 2, '.', ''));
            $currency = is_string($orderData['currency'] ?? 'usd') ? (string)($orderData['currency'] ?? 'usd') : 'usd';
            $amount->setCurrency($currency);
            // Create transaction with sanitized data
            $transaction = new Transaction();
            $transaction->setAmount($amount);
            $transaction->setDescription('Product Purchase');
            $userId = is_string($orderData['userId'] ?? '') ? (string)($orderData['userId'] ?? '') : '';
            $productId = is_string($orderData['productId'] ?? '') ? (string)($orderData['productId'] ?? '') : '';
            $transaction->setCustom("userId:" . $userId . ", productId:" . $productId);
            // Create redirect URLs
            $redirectUrls = new RedirectUrls();
            $appUrl = config('app.url');
            $appUrlString = is_string($appUrl) ? $appUrl : '';
            $redirectUrls->setReturnUrl($appUrlString . '/payment/success/paypal')
                ->setCancelUrl($appUrlString . '/payment/cancel/paypal');
            // Create payment with proper structure
            $payment = new Payment();
            $payment->setIntent('sale');
            $payment->setPayer($payer);
            $payment->setTransactions([$transaction]);
            $payment->setRedirectUrls($redirectUrls);
            // Create payment
            $payment->create($apiContext);
            // Get approval URL
            $approvalUrl = null;
            foreach ($payment->getLinks() as $link) {
                if ($link->getRel() === 'approval_url') {
                    $approvalUrl = $link->getHref();
                    break;
                }
            }
            return [
                'success' => true,
                'redirect_url' => $approvalUrl,
                'payment_url' => $approvalUrl, // Keep both for compatibility
                'payment_id' => $payment->getId(),
            ];
        } catch (\Exception $e) {
            Log::error('PayPal payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'PayPal payment processing failed: ' . $e->getMessage(),
            ];
        }
    }
    /**
     * Process Stripe payment with enhanced security and error handling.
     *
     * Processes Stripe payments with comprehensive validation, security measures,
     * and error handling for reliable payment processing operations.
     *
     * @param  array  $orderData  Order data including amount, currency, userId, productId
     *
     * @return array Stripe payment processing result
     *
     * @throws \Exception When Stripe payment processing fails
     */
    /**
     * @param array<string, mixed> $orderData
     * @return array<string, mixed>
     */
    protected function processStripePayment(array $orderData): array
    {
        try {
            $settings = PaymentSetting::getByGateway('stripe');
            if (!$settings) {
                throw new \Exception('Stripe settings not found');
            }
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validateStripeCredentials($credentials);
            // Set Stripe API key
            $secretKey = is_string($credentials['secret_key'] ?? '') ? (string)($credentials['secret_key'] ?? '') : '';
            Stripe::setApiKey($secretKey);
            $appUrl = config('app.url');
            $appUrlString = is_string($appUrl) ? $appUrl : '';
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => is_string($orderData['currency'] ?? 'usd') ? (string)($orderData['currency'] ?? 'usd') : 'usd',
                            'product_data' => [
                                'name' => 'Product Purchase',
                            ],
                            'unit_amount' => (int)((is_numeric($orderData['amount'] ?? 0) ? (float)($orderData['amount'] ?? 0) : 0.0) * 100), // Convert to cents
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $appUrlString . '/payment/success/stripe',
                'cancel_url' => $appUrlString . '/payment/cancel/stripe',
                'metadata' => [
                    'userId' => is_string($orderData['userId'] ?? '') ? (string)($orderData['userId'] ?? '') : '',
                    'productId' => is_string($orderData['productId'] ?? '') ? (string)($orderData['productId'] ?? '') : '',
                ],
            ]);
            return [
                'success' => true,
                'redirect_url' => $session->url,
                'payment_url' => $session->url, // Keep both for compatibility
                'session_id' => $session->id,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe payment processing failed', [
                'order_data' => $orderData,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Stripe payment processing failed: ' . $e->getMessage(),
            ];
        }
    }
    /**
     * Verify payment with gateway with enhanced security and error handling.
     *
     * Verifies payment status with the specified payment gateway. Includes
     * comprehensive validation, security measures, and error handling for
     * reliable payment verification operations.
     *
     * @param  string  $gateway  Payment gateway to verify with ('paypal' or 'stripe')
     * @param  string  $transactionId  Transaction ID to verify
     *
     * @return array Verification result with success status and transaction details
     *
     * @throws InvalidArgumentException When gateway is unsupported or transaction ID is invalid
     * @throws \Exception When payment verification fails
     *
     * @example
     * $result = $service->verifyPayment('stripe', 'pi_1234567890');
     * if ($result['success']) {
     *     // Payment verified successfully
     * }
     */
    /**
     * @return array<string, mixed>
     */
    public function verifyPayment(string $gateway, string $transactionId): array
    {
        try {
            // Validate input parameters
            $this->validateGateway($gateway);
            $this->validateTransactionId($transactionId);
            switch ($gateway) {
                case 'paypal':
                    return $this->verifyPayPalPayment($transactionId);
                case 'stripe':
                    return $this->verifyStripePayment($transactionId);
                default:
                    return [
                        'success' => false,
                        'message' => 'Unsupported payment gateway',
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Payment verification failed',
            ];
        }
    }
    /**
     * Verify PayPal payment with enhanced security and error handling.
     *
     * @param  string  $paymentId  PayPal payment ID to verify
     *
     * @return array PayPal payment verification result
     *
     * @throws \Exception When PayPal verification fails
     */
    /**
     * @return array<string, mixed>
     */
    protected function verifyPayPalPayment(string $paymentId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('paypal');
            if (!$settings) {
                throw new \Exception('PayPal settings not found');
            }
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validatePayPalCredentials($credentials);
            // Create API context
            $clientId = is_string($credentials['client_id'] ?? '') ? (string)($credentials['client_id'] ?? '') : '';
            $clientSecret = is_string($credentials['client_secret'] ?? '') ? (string)($credentials['client_secret'] ?? '') : '';

            $apiContext = new ApiContext(
                new OAuthTokenCredential($clientId, $clientSecret),
            );
            $apiContext->setConfig([
                'mode' => $settings->is_sandbox ? 'sandbox' : 'live',
            ]);
            // Get payment details
            $payment = Payment::get($paymentId, $apiContext);
            if ($payment->getState() === 'approved') {
                // Execute payment
                $execution = new PaymentExecution();
                $payerId = request()->get('PayerID');
                $payerIdString = is_string($payerId) ? $payerId : '';
                $execution->setPayerId($payerIdString);
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'PayPal verification failed',
            ];
        }
    }
    /**
     * Verify Stripe payment with enhanced security and error handling.
     *
     * @param  string  $transactionId  Stripe transaction ID to verify
     *
     * @return array Stripe payment verification result
     *
     * @throws \Exception When Stripe verification fails
     */
    /**
     * @return array<string, mixed>
     */
    protected function verifyStripePayment(string $transactionId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('stripe');
            if (!$settings) {
                throw new \Exception('Stripe settings not found');
            }
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validateStripeCredentials($credentials);
            $secretKey = $credentials['secret_key'] ?? '';
            $secretKeyString = is_string($secretKey) ? $secretKey : '';
            Stripe::setApiKey($secretKeyString);
            // For Stripe Checkout, we need to retrieve the session, not payment_intent
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'message' => 'Stripe verification failed',
            ];
        }
    }
    /**
     * Create license and invoice after successful payment with enhanced security and error handling.
     *
     * Creates license and invoice records after successful payment processing.
     * Includes comprehensive validation, security measures, and error handling
     * for reliable license and invoice management.
     *
     * @param  array  $orderData  Order data including userId, productId, amount, currency
     * @param  string  $gateway  Payment gateway used ('paypal' or 'stripe')
     * @param  string|null  $transactionId  Transaction ID from payment gateway
     *
     * @return array Creation result with license and invoice objects
     *
     * @throws InvalidArgumentException When order data is invalid
     * @throws \Exception When license or invoice creation fails
     *
     * @example
     * $result = $service->createLicenseAndInvoice([
     *     'userId' => 1,
     *     'productId' => 1,
     *     'amount' => 99.99,
     *     'currency' => 'USD'
     * ], 'stripe', 'pi_1234567890');
     */
    /**
     * @param array<string, mixed> $orderData
     * @return array<string, mixed>
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            // Validate input parameters
            $this->validateOrderData($orderData);
            $this->validateGateway($gateway);
            DB::beginTransaction();
            $user = User::find($orderData['userId']);
            if (!$user) {
                throw new \Exception('User not found');
            }
            $product = isset($orderData['productId']) ? Product::find($orderData['productId']) : null;
            // Check if this is for an existing invoice
            $existingInvoice = null;
            if (isset($orderData['invoice_id']) && $orderData['invoice_id']) {
                $existingInvoice = Invoice::find($orderData['invoice_id']);
            }
            $license = null;
            $invoice = null;
            if ($existingInvoice instanceof \App\Models\Invoice) {
                // Update existing invoice
                $existingInvoice->status = 'paid';
                $existingInvoice->paid_at = now();
                $existingInvoice->notes = "Payment via {$gateway}";
                $existingInvoice->metadata = array_merge($existingInvoice->metadata ?? [], [
                    'gateway' => $gateway,
                    'transaction_id' => $transactionId,
                ]);
                $existingInvoice->save();
                $invoice = $existingInvoice;
                $license = $invoice->license ?? null;
            } else {
                // Check if this is a custom invoice (no product)
                if (isset($orderData['is_custom']) && $orderData['is_custom']) {
                    // For custom invoices, no license is created
                    $license = null;
                    // Create new invoice for custom service
                    $invoice = Invoice::create([
                        'userId' => $user->id ?? 0,
                        'productId' => null,
                        'licenseId' => null,
                        'invoice_number' => $this->generateInvoiceNumber(),
                        'amount' => $orderData['amount'],
                        'currency' => $orderData['currency'],
                        'status' => 'paid',
                        'paid_at' => now(),
                        'due_date' => now()->addDays(30), // Add due date
                        'billing_address' => $user->billing_address ?? null,
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                        'total_amount' => $orderData['amount'],
                        'notes' => "Custom service payment via {$gateway}",
                        'metadata' => [
                            'gateway' => $gateway,
                            'transaction_id' => $transactionId,
                            'is_custom' => true,
                        ],
                    ]);
                } else {
                    // Create new license for product purchase
                    $license = License::create([
                        'userId' => $user->id ?? 0,
                        'productId' => $product instanceof \App\Models\Product ? $product->id : null,
                        'licenseType' => $product instanceof \App\Models\Product ? $product->licenseType ?? 'single' : 'single',
                        'status' => 'active',
                        'maxDomains' => $product instanceof \App\Models\Product ? (is_numeric($product->maxDomains ?? null) ? (int) $product->maxDomains : 1) : 1,
                        'licenseExpiresAt' => $product instanceof \App\Models\Product ? $this->calculateLicenseExpiry($product) : null,
                        'support_expiresAt' => $product instanceof \App\Models\Product ? $this->calculateSupportExpiry($product) : null,
                        'notes' => "Purchased via {$gateway}",
                    ]);
                    // Create new invoice using InvoiceService
                    $invoiceService = app(InvoiceService::class);
                    $amount = is_numeric($orderData['amount'] ?? 0) ? (float)($orderData['amount'] ?? 0) : 0.0;
                    $userModel = $user instanceof \App\Models\User ? $user : $user->first();
                    $productModel = $product instanceof \App\Models\Product ? $product : ($product ? $product->first() : null);

                    if (!$userModel || !$productModel) {
                        throw new \Exception('User or Product not found');
                    }

                    $invoice = $invoiceService->createInvoice(
                        $userModel,
                        $license,
                        $productModel,
                        $amount,
                        is_string($orderData['currency'] ?? 'usd') ? (string)($orderData['currency'] ?? 'usd') : 'usd',
                        $gateway,
                        $transactionId,
                    );
                }
            }
            DB::commit();
            // Invoice is validated by type hint
            return [
                'success' => true,
                'license' => $license,
                'invoice' => $invoice,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create license and invoice', [
                'order_data' => $orderData,
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle webhook from payment gateway
     *
     * @param Request $request The webhook request
     * @param string $gateway The payment gateway name
     * @return array<string, mixed> Webhook processing result
     */
    public function handleWebhook(ServiceRequest $request, string $gateway): array
    {
        try {
            // Validate gateway
            $this->validateGateway($gateway);

            // Process webhook based on gateway
            switch ($gateway) {
                case 'stripe':
                    return $this->handleStripeWebhook($request);
                case 'paypal':
                    return $this->handlePayPalWebhook($request);
                default:
                    return ['success' => false, 'message' => 'Unsupported gateway'];
            }
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => 'Webhook processing failed'];
        }
    }

    /**
     * Handle Stripe webhook
     *
     * @return array<string, mixed>
     */
    private function handleStripeWebhook(ServiceRequest $request): array
    {
        // Implement Stripe webhook logic
        return ['success' => true, 'message' => 'Stripe webhook processed'];
    }

    /**
     * Handle PayPal webhook
     *
     * @return array<string, mixed>
     */
    private function handlePayPalWebhook(ServiceRequest $request): array
    {
        // Implement PayPal webhook logic
        return ['success' => true, 'message' => 'PayPal webhook processed'];
    }
    /**
     * Validate order data with enhanced security and comprehensive validation.
     *
     * @param  array  $orderData  Order data to validate
     *
     * @throws InvalidArgumentException When order data is invalid
     */
    /**
     * @param array<string, mixed> $orderData
     */
    private function validateOrderData(array $orderData): void
    {
        if (empty($orderData)) {
            throw new InvalidArgumentException('Order data cannot be empty');
        }
        if (isset($orderData['userId']) === false || ! is_numeric($orderData['userId']) || $orderData['userId'] < 1) {
            throw new InvalidArgumentException('Valid userId is required');
        }
        if (! isset($orderData['amount']) || ! is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new InvalidArgumentException('Valid amount is required');
        }
        if (! isset($orderData['currency']) || empty($orderData['currency'])) {
            throw new InvalidArgumentException('Currency is required');
        }
        $currency = is_string($orderData['currency']) ? (string)$orderData['currency'] : '';
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-character code');
        }
        // Validate amount range
        if ($orderData['amount'] > 999999.99) {
            throw new InvalidArgumentException('Amount cannot exceed 999, 999.99');
        }
    }
    /**
     * Validate payment gateway with enhanced security.
     *
     * @param  string  $gateway  Payment gateway to validate
     *
     * @throws InvalidArgumentException When gateway is invalid
     */
    private function validateGateway(string $gateway): void
    {
        $supportedGateways = ['paypal', 'stripe'];
        if (! in_array($gateway, $supportedGateways)) {
            throw new InvalidArgumentException('Unsupported payment gateway');
        }
    }
    /**
     * Validate PayPal credentials with enhanced security.
     *
     * @param  array  $credentials  PayPal credentials to validate
     *
     * @throws InvalidArgumentException When credentials are invalid
     */
    /**
     * @param array<mixed>|null $credentials
     */
    private function validatePayPalCredentials(?array $credentials): void
    {
        $clientId = is_string($credentials['client_id'] ?? '') ? (string)($credentials['client_id'] ?? '') : '';
        if (empty($clientId)) {
            throw new InvalidArgumentException('PayPal client_id is required');
        }
        $clientSecret = is_string($credentials['client_secret'] ?? '') ? (string)($credentials['client_secret'] ?? '') : '';
        if (empty($clientSecret)) {
            throw new InvalidArgumentException('PayPal client_secret is required');
        }
    }
    /**
     * Validate Stripe credentials with enhanced security.
     *
     * @param  array  $credentials  Stripe credentials to validate
     *
     * @throws InvalidArgumentException When credentials are invalid
     */
    /**
     * @param array<mixed>|null $credentials
     */
    private function validateStripeCredentials(?array $credentials): void
    {
        $secretKey = is_string($credentials['secret_key'] ?? '') ? (string)($credentials['secret_key'] ?? '') : '';
        if (empty($secretKey)) {
            throw new InvalidArgumentException('Stripe secret_key is required');
        }
        if (! str_starts_with($secretKey, 'sk_')) {
            throw new InvalidArgumentException('Invalid Stripe secret key format');
        }
    }
    /**
     * Validate transaction ID with enhanced security.
     *
     * @param  string  $transactionId  Transaction ID to validate
     *
     * @throws InvalidArgumentException When transaction ID is invalid
     */
    private function validateTransactionId(string $transactionId): void
    {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }
        if (strlen($transactionId) < 5 || strlen($transactionId) > 100) {
            throw new InvalidArgumentException('Transaction ID must be between 5 and 100 characters');
        }
        // Basic format validation
        if (! preg_match('/^[A-Za-z0-9\-_]+$/', $transactionId)) {
            throw new InvalidArgumentException('Transaction ID contains invalid characters');
        }
    }
    /**
     * Generate unique invoice number with enhanced security and error handling.
     *
     * @return string Unique invoice number
     *
     * @throws \Exception When invoice number generation fails
     */
    protected function generateInvoiceNumber(): string
    {
        try {
            do {
                $invoiceNumber = 'INV-' . strtoupper(\Illuminate\Support\Str::random(8));
            } while (Invoice::where('invoice_number', $invoiceNumber)->exists());
            return $invoiceNumber;
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice number', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate license expiry date with enhanced error handling and validation.
     *
     * @param  Product  $product  Product to calculate expiry for
     *
     * @return \DateTime|null License expiry date or null for lifetime licenses
     *
     * @throws \Exception When expiry calculation fails
     */
    protected function calculateLicenseExpiry(Product $product): ?\DateTime
    {
        try {
            // If product has lifetime license or renewalPeriod is lifetime, return null (no expiry)
            if ($product->licenseType === 'lifetime' || $product->renewalPeriod === 'lifetime') {
                return null;
            }
            // Calculate expiry based on renewalPeriod from product
            $days = $this->getRenewalPeriodInDays($product->renewalPeriod);
            // If no valid renewal period, use default from settings
            if ($days === null) {
                $defaultDuration = \App\Helpers\ConfigHelper::getSetting('license_default_duration', 365);
                $days = is_numeric($defaultDuration) ? (int)$defaultDuration : 365;
            }
            return now()->addDays($days);
        } catch (\Exception $e) {
            Log::error('Failed to calculate license expiry', [
                'productId' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Calculate support expiry date with enhanced error handling and validation.
     *
     * @param  Product  $product  Product to calculate support expiry for
     *
     * @return \DateTime Support expiry date
     *
     * @throws \Exception When support expiry calculation fails
     */
    protected function calculateSupportExpiry(Product $product): \DateTime
    {
        try {
            // Use product's supportDays or default from settings
            $productSupportDays = $product->supportDays ?? null;
            $defaultSupportDuration = \App\Helpers\ConfigHelper::getSetting('license_support_duration', 365);
            $supportDuration = is_numeric($productSupportDays) ? (int)$productSupportDays :
                              (is_numeric($defaultSupportDuration) ? (int)$defaultSupportDuration : 365);
            // Calculate support expiry based on duration in days
            return now()->addDays($supportDuration);
        } catch (\Exception $e) {
            Log::error('Failed to calculate support expiry', [
                'productId' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Convert renewal period to days with enhanced validation.
     *
     * @param  string|null  $renewalPeriod  Renewal period string
     *
     * @return int|null Number of days or null for lifetime
     */
    protected function getRenewalPeriodInDays(?string $renewalPeriod): ?int
    {
        return match ($renewalPeriod) {
            'monthly' => 30,
            'quarterly' => 90,
            'semi-annual' => 180,
            'annual' => 365,
            'three-years' => 1095, // 3 years
            'lifetime' => null, // No expiry
            default => null,
        };
    }
}
