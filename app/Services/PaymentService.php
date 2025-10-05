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
     * @param  array  $orderData  Order data including amount, currency, user_id, product_id
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
     *     'user_id' => 1,
     *     'product_id' => 1
     * ], 'stripe');
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
     * @param  array  $orderData  Order data including amount, currency, user_id, product_id
     *
     * @return array PayPal payment processing result
     *
     * @throws \Exception When PayPal payment processing fails
     */
    protected function processPayPalPayment(array $orderData): array
    {
        try {
            $settings = PaymentSetting::getByGateway('paypal');
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validatePayPalCredentials($credentials);
            // Create API context
            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $credentials['client_id'],
                    $credentials['client_secret'],
                ),
            );
            // Set mode (sandbox or live)
            $apiContext->setConfig([
                'mode' => $settings->is_sandbox ? 'sandbox' : 'live',
                'log.LogEnabled' => true,
                'log.FileName' => storage_path('logs/paypal.log'),
                'log.LogLevel' => 'INFO',
            ]);
            // Create payer
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');
            // Create amount with validation
            $amount = new Amount();
            $amount->setTotal(number_format($orderData['amount'], 2, '.', ''));
            $amount->setCurrency($orderData['currency']);
            // Create transaction with sanitized data
            $transaction = new Transaction();
            $transaction->setAmount($amount);
            $transaction->setDescription('Product Purchase');
            $transaction->setCustom("user_id:{$orderData['user_id']}, product_id:{$orderData['product_id']}");
            // Create redirect URLs
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(config('app.url').'/payment/success/paypal')
                ->setCancelUrl(config('app.url').'/payment/cancel/paypal');
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
                'message' => 'PayPal payment processing failed: '.$e->getMessage(),
            ];
        }
    }
    /**
     * Process Stripe payment with enhanced security and error handling.
     *
     * Processes Stripe payments with comprehensive validation, security measures,
     * and error handling for reliable payment processing operations.
     *
     * @param  array  $orderData  Order data including amount, currency, user_id, product_id
     *
     * @return array Stripe payment processing result
     *
     * @throws \Exception When Stripe payment processing fails
     */
    protected function processStripePayment(array $orderData): array
    {
        try {
            $settings = PaymentSetting::getByGateway('stripe');
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validateStripeCredentials($credentials);
            // Set Stripe API key
            Stripe::setApiKey($credentials['secret_key']);
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => $orderData['currency'],
                            'product_data' => [
                                'name' => 'Product Purchase',
                            ],
                            'unit_amount' => $orderData['amount'] * 100, // Convert to cents
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => config('app.url').'/payment/success/stripe',
                'cancel_url' => config('app.url').'/payment/cancel/stripe',
                'metadata' => [
                    'user_id' => $orderData['user_id'],
                    'product_id' => $orderData['product_id'],
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
                'message' => 'Stripe payment processing failed: '.$e->getMessage(),
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
    protected function verifyPayPalPayment(string $paymentId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('paypal');
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validatePayPalCredentials($credentials);
            // Create API context
            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $credentials['client_id'],
                    $credentials['client_secret'],
                ),
            );
            $apiContext->setConfig([
                'mode' => $settings->is_sandbox ? 'sandbox' : 'live',
            ]);
            // Get payment details
            $payment = Payment::get($paymentId, $apiContext);
            if ($payment->getState() === 'approved') {
                // Execute payment
                $execution = new PaymentExecution();
                $execution->setPayerId(request()->get('PayerID'));
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
    protected function verifyStripePayment(string $transactionId): array
    {
        try {
            $settings = PaymentSetting::getByGateway('stripe');
            $credentials = $settings->credentials;
            // Validate credentials
            $this->validateStripeCredentials($credentials);
            Stripe::setApiKey($credentials['secret_key']);
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
     * @param  array  $orderData  Order data including user_id, product_id, amount, currency
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
     *     'user_id' => 1,
     *     'product_id' => 1,
     *     'amount' => 99.99,
     *     'currency' => 'USD'
     * ], 'stripe', 'pi_1234567890');
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            // Validate input parameters
            $this->validateOrderData($orderData);
            $this->validateGateway($gateway);
            DB::beginTransaction();
            $user = User::find($orderData['user_id']);
            $product = isset($orderData['product_id']) ? Product::find($orderData['product_id']) : null;
            // Check if this is for an existing invoice
            $existingInvoice = null;
            if (isset($orderData['invoice_id']) && $orderData['invoice_id']) {
                $existingInvoice = Invoice::find($orderData['invoice_id']);
            }
            $license = null;
            $invoice = null;
            if ($existingInvoice) {
                // Update existing invoice
                $existingInvoice->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'notes' => "Payment via {$gateway}",
                    'metadata' => array_merge($existingInvoice->metadata ?? [], [
                        'gateway' => $gateway,
                        'transaction_id' => $transactionId,
                    ]),
                ]);
                $invoice = $existingInvoice;
                $license = $invoice->license;
            } else {
                // Check if this is a custom invoice (no product)
                if (isset($orderData['is_custom']) && $orderData['is_custom']) {
                    // For custom invoices, no license is created
                    $license = null;
                    // Create new invoice for custom service
                    $invoice = Invoice::create([
                        'user_id' => $user->id,
                        'product_id' => null,
                        'license_id' => null,
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
                        'user_id' => $user->id,
                        'product_id' => $product->id,
                        'license_type' => $product->license_type ?? 'single',
                        'status' => 'active',
                        'max_domains' => $product->max_domains ?? 1,
                        'license_expires_at' => $this->calculateLicenseExpiry($product),
                        'support_expires_at' => $this->calculateSupportExpiry($product),
                        'notes' => "Purchased via {$gateway}",
                    ]);
                    // Create new invoice using InvoiceService
                    $invoiceService = app(InvoiceService::class);
                    $invoice = $invoiceService->createInvoice(
                        $user,
                        $license,
                        $product,
                        $orderData['amount'],
                        $orderData['currency'],
                        $gateway,
                        $transactionId,
                    );
                }
            }
            DB::commit();
            if (! $invoice) {
                throw new \Exception('Failed to create invoice');
            }
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
     * Validate order data with enhanced security and comprehensive validation.
     *
     * @param  array  $orderData  Order data to validate
     *
     * @throws InvalidArgumentException When order data is invalid
     */
    private function validateOrderData(array $orderData): void
    {
        if (empty($orderData)) {
            throw new InvalidArgumentException('Order data cannot be empty');
        }
        if (isset($orderData['user_id']) === false || ! is_numeric($orderData['user_id']) || $orderData['user_id'] < 1) {
            throw new InvalidArgumentException('Valid user_id is required');
        }
        if (! isset($orderData['amount']) || ! is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new InvalidArgumentException('Valid amount is required');
        }
        if (! isset($orderData['currency']) || empty($orderData['currency'])) {
            throw new InvalidArgumentException('Currency is required');
        }
        if (strlen($orderData['currency']) !== 3) {
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
    private function validatePayPalCredentials(array $credentials): void
    {
        if (empty($credentials['client_id'])) {
            throw new InvalidArgumentException('PayPal client_id is required');
        }
        if (empty($credentials['client_secret'])) {
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
    private function validateStripeCredentials(array $credentials): void
    {
        if (empty($credentials['secret_key'])) {
            throw new InvalidArgumentException('Stripe secret_key is required');
        }
        if (! str_starts_with($credentials['secret_key'], 'sk_')) {
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
                $invoiceNumber = 'INV-'.strtoupper(\Illuminate\Support\Str::random(8));
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
            // If product has lifetime license or renewal_period is lifetime, return null (no expiry)
            if ($product->license_type === 'lifetime' || $product->renewal_period === 'lifetime') {
                return null;
            }
            // Calculate expiry based on renewal_period from product
            $days = $this->getRenewalPeriodInDays($product->renewal_period);
            // If no valid renewal period, use default from settings
            if ($days === null) {
                $days = \App\Helpers\ConfigHelper::getSetting('license_default_duration', 365);
            }
            return now()->addDays($days);
        } catch (\Exception $e) {
            Log::error('Failed to calculate license expiry', [
                'product_id' => $product->id,
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
            // Use product's support_days or default from settings
            $supportDuration = $product->support_days
                ?? \App\Helpers\ConfigHelper::getSetting('license_support_duration', 365);
            // Calculate support expiry based on duration in days
            return now()->addDays($supportDuration);
        } catch (\Exception $e) {
            Log::error('Failed to calculate support expiry', [
                'product_id' => $product->id,
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
