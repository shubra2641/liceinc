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
    private const SUPPORTED_GATEWAYS = ['paypal', 'stripe'];
    private const MAX_AMOUNT = 999999.99;
    private const TRANSACTION_ID_MIN_LENGTH = 5;
    private const TRANSACTION_ID_MAX_LENGTH = 100;
    /**
     * Process payment with the specified gateway.
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        $this->validateOrderData($orderData);
        $this->validateGateway($gateway);

        return $this->executePaymentProcessing($orderData, $gateway);
    }

    /**
     * Execute payment processing based on gateway.
     */
    private function executePaymentProcessing(array $orderData, string $gateway): array
    {
        try {
            return match ($gateway) {
                'paypal' => $this->processPayPalPayment($orderData),
                'stripe' => $this->processStripePayment($orderData),
                default => throw new InvalidArgumentException("Unsupported gateway: {$gateway}")
            };
        } catch (\Exception $e) {
            $this->logPaymentError($e, $gateway, $orderData);
            throw $e;
        }
    }

    /**
     * Log payment processing errors.
     */
    private function logPaymentError(\Exception $e, string $gateway, array $orderData): void
    {
        Log::error('Payment processing failed', [
            'gateway' => $gateway,
            'order_data' => $orderData,
            'error' => $e->getMessage()
        ]);
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
            $approvalUrl = $this->extractApprovalUrl($payment);

            return $this->buildSuccessResponse($approvalUrl, $payment->getId());
        } catch (\Exception $e) {
            return $this->buildErrorResponse($e, $orderData, 'PayPal payment processing failed');
        }
    }

    /**
     * Get PayPal settings.
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
     * Create PayPal API context.
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
     * Create PayPal payment object.
     */
    private function createPayPalPayment(array $orderData, ApiContext $apiContext): Payment
    {
        $payer = $this->createPayPalPayer();
        $amount = $this->createPayPalAmount($orderData);
        $transaction = $this->createPayPalTransaction($amount, $orderData);
        $redirectUrls = $this->createPayPalRedirectUrls();

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->setRedirectUrls($redirectUrls);

        return $payment->create($apiContext);
    }

    /**
     * Create PayPal payer.
     */
    private function createPayPalPayer(): Payer
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        return $payer;
    }

    /**
     * Create PayPal amount.
     */
    private function createPayPalAmount(array $orderData): Amount
    {
        $amount = new Amount();
        $amount->setTotal(number_format($orderData['amount'], 2, '.', ''));
        $amount->setCurrency($orderData['currency'] ?? 'usd');
        return $amount;
    }

    /**
     * Create PayPal transaction.
     */
    private function createPayPalTransaction(Amount $amount, array $orderData): Transaction
    {
        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setDescription('Product Purchase');
        $transaction->setCustom("user_id:{$orderData['user_id']}, product_id:{$orderData['product_id']}");
        return $transaction;
    }

    /**
     * Create PayPal redirect URLs.
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
     * Extract approval URL from PayPal payment.
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
     * Build success response.
     */
    private function buildSuccessResponse(?string $approvalUrl, string $paymentId): array
    {
        return [
            'success' => true,
            'redirect_url' => $approvalUrl,
            'payment_url' => $approvalUrl,
            'payment_id' => $paymentId,
        ];
    }

    /**
     * Build error response.
     */
    private function buildErrorResponse(\Exception $e, array $orderData, string $message): array
    {
        Log::error($message, [
            'order_data' => $orderData,
            'error' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'message' => $message . ': ' . $e->getMessage(),
        ];
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

            return $this->buildStripeSuccessResponse($session);
        } catch (\Exception $e) {
            return $this->buildErrorResponse($e, $orderData, 'Stripe payment processing failed');
        }
    }

    /**
     * Get Stripe settings.
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
     * Configure Stripe API.
     */
    private function configureStripe(PaymentSetting $settings): void
    {
        $credentials = $settings->credentials;
        $this->validateStripeCredentials($credentials);
        Stripe::setApiKey($credentials['secret_key'] ?? '');
    }

    /**
     * Create Stripe session.
     */
    private function createStripeSession(array $orderData): Session
    {
        $appUrl = config('app.url');
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $this->buildStripeLineItems($orderData),
            'mode' => 'payment',
            'success_url' => $appUrl . '/payment/success/stripe',
            'cancel_url' => $appUrl . '/payment/cancel/stripe',
            'metadata' => $this->buildStripeMetadata($orderData),
        ]);
    }

    /**
     * Build Stripe line items.
     */
    private function buildStripeLineItems(array $orderData): array
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
     * Build Stripe metadata.
     */
    private function buildStripeMetadata(array $orderData): array
    {
        return [
            'user_id' => $orderData['user_id'] ?? '',
            'product_id' => $orderData['product_id'] ?? '',
        ];
    }

    /**
     * Build Stripe success response.
     */
    private function buildStripeSuccessResponse(Session $session): array
    {
        return [
            'success' => true,
            'redirect_url' => $session->url,
            'payment_url' => $session->url,
            'session_id' => $session->id,
        ];
    }

    /**
     * Verify payment with gateway.
     */
    public function verifyPayment(string $gateway, string $transactionId): array
    {
        $this->validateGateway($gateway);
        $this->validateTransactionId($transactionId);

        return $this->executePaymentVerification($gateway, $transactionId);
    }

    /**
     * Execute payment verification based on gateway.
     */
    private function executePaymentVerification(string $gateway, string $transactionId): array
    {
        try {
            return match ($gateway) {
                'paypal' => $this->verifyPayPalPayment($transactionId),
                'stripe' => $this->verifyStripePayment($transactionId),
                default => $this->buildUnsupportedGatewayResponse()
            };
        } catch (\Exception $e) {
            $this->logVerificationError($e, $gateway, $transactionId);
            return $this->buildVerificationErrorResponse();
        }
    }

    /**
     * Build unsupported gateway response.
     */
    private function buildUnsupportedGatewayResponse(): array
    {
        return [
            'success' => false,
            'message' => 'Unsupported payment gateway',
        ];
    }

    /**
     * Log verification error.
     */
    private function logVerificationError(\Exception $e, string $gateway, string $transactionId): void
    {
        Log::error('Payment verification failed', [
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);
    }

    /**
     * Build verification error response.
     */
    private function buildVerificationErrorResponse(): array
    {
        return [
            'success' => false,
            'message' => 'Payment verification failed',
        ];
    }

    /**
     * Verify PayPal payment.
     */
    protected function verifyPayPalPayment(string $paymentId): array
    {
        try {
            $settings = $this->getPayPalSettings();
            $apiContext = $this->createPayPalApiContext($settings);
            $payment = Payment::get($paymentId, $apiContext);

            if ($this->isPayPalPaymentApproved($payment)) {
                return $this->executePayPalPayment($payment, $apiContext, $paymentId);
            }

            return $this->buildPayPalNotApprovedResponse();
        } catch (\Exception $e) {
            return $this->buildPayPalVerificationErrorResponse($e, $paymentId);
        }
    }

    /**
     * Check if PayPal payment is approved.
     */
    private function isPayPalPaymentApproved(Payment $payment): bool
    {
        return $payment->getState() === 'approved';
    }

    /**
     * Execute PayPal payment.
     */
    private function executePayPalPayment(Payment $payment, ApiContext $apiContext, string $paymentId): array
    {
        $execution = new PaymentExecution();
        $payerId = request()->get('PayerID');
        $execution->setPayerId($payerId ?? '');
        $result = $payment->execute($execution, $apiContext);

        if ($result->getState() === 'approved') {
            return $this->buildPayPalSuccessResponse($paymentId);
        }

        return $this->buildPayPalNotApprovedResponse();
    }

    /**
     * Build PayPal success response.
     */
    private function buildPayPalSuccessResponse(string $paymentId): array
    {
        return [
            'success' => true,
            'transaction_id' => $paymentId,
            'message' => 'Payment verified successfully',
        ];
    }

    /**
     * Build PayPal not approved response.
     */
    private function buildPayPalNotApprovedResponse(): array
    {
        return [
            'success' => false,
            'message' => 'Payment not approved',
        ];
    }

    /**
     * Build PayPal verification error response.
     */
    private function buildPayPalVerificationErrorResponse(\Exception $e, string $paymentId): array
    {
        Log::error('PayPal verification failed', [
            'payment_id' => $paymentId,
            'error' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'message' => 'PayPal verification failed',
        ];
    }

    /**
     * Verify Stripe payment.
     */
    protected function verifyStripePayment(string $transactionId): array
    {
        try {
            $settings = $this->getStripeSettings();
            $this->configureStripe($settings);
            $session = Session::retrieve($transactionId);

            if ($this->isStripePaymentPaid($session)) {
                return $this->buildStripeSuccessResponse($transactionId);
            }

            return $this->buildStripeNotCompletedResponse();
        } catch (\Exception $e) {
            return $this->buildStripeVerificationErrorResponse($e, $transactionId);
        }
    }

    /**
     * Check if Stripe payment is paid.
     */
    private function isStripePaymentPaid(Session $session): bool
    {
        return $session->payment_status === 'paid';
    }

    /**
     * Build Stripe success response.
     */
    private function buildStripeSuccessResponse(string $transactionId): array
    {
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Payment verified successfully',
        ];
    }

    /**
     * Build Stripe not completed response.
     */
    private function buildStripeNotCompletedResponse(): array
    {
        return [
            'success' => false,
            'message' => 'Payment not completed',
        ];
    }

    /**
     * Build Stripe verification error response.
     */
    private function buildStripeVerificationErrorResponse(\Exception $e, string $transactionId): array
    {
        Log::error('Stripe verification failed', [
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'message' => 'Stripe verification failed',
        ];
    }

    /**
     * Create license and invoice after successful payment.
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        $this->validateOrderData($orderData);
        $this->validateGateway($gateway);

        return $this->executeLicenseAndInvoiceCreation($orderData, $gateway, $transactionId);
    }

    /**
     * Execute license and invoice creation.
     */
    private function executeLicenseAndInvoiceCreation(array $orderData, string $gateway, ?string $transactionId): array
    {
        try {
            DB::beginTransaction();

            $user = $this->findUser($orderData['user_id']);
            $product = $this->findProduct($orderData);

            if ($this->hasExistingInvoice($orderData)) {
                return $this->handleExistingInvoice($orderData, $gateway, $transactionId);
            }

            if ($this->isCustomInvoice($orderData)) {
                return $this->handleCustomInvoice($user, $orderData, $gateway, $transactionId);
            }

            if ($product) {
                return $this->handleProductPurchase($user, $product, $orderData, $gateway, $transactionId);
            }

            throw new \Exception('Product not found');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logLicenseCreationError($e, $orderData, $gateway, $transactionId);
            throw $e;
        }
    }

    /**
     * Find user by ID.
     */
    private function findUser(int $userId): User
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }
        return $user;
    }

    /**
     * Find product by ID.
     */
    private function findProduct(array $orderData): ?Product
    {
        return isset($orderData['product_id']) ? Product::find($orderData['product_id']) : null;
    }

    /**
     * Check if order has existing invoice.
     */
    private function hasExistingInvoice(array $orderData): bool
    {
        return isset($orderData['invoice_id']) && $orderData['invoice_id'];
    }

    /**
     * Handle existing invoice.
     */
    private function handleExistingInvoice(array $orderData, string $gateway, ?string $transactionId): array
    {
        $existingInvoice = Invoice::find($orderData['invoice_id']);
        if ($existingInvoice) {
            $this->updateExistingInvoice($existingInvoice, $gateway, $transactionId);
            DB::commit();
            return $this->buildExistingInvoiceResponse($existingInvoice);
        }
        throw new \Exception('Invoice not found');
    }

    /**
     * Update existing invoice.
     */
    private function updateExistingInvoice(Invoice $invoice, string $gateway, ?string $transactionId): void
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => "Payment via {$gateway}",
            'metadata' => array_merge($invoice->metadata ?? [], [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
            ])
        ]);
    }

    /**
     * Build existing invoice response.
     */
    private function buildExistingInvoiceResponse(Invoice $invoice): array
    {
        return [
            'success' => true,
            'license' => $invoice->license,
            'invoice' => $invoice,
        ];
    }

    /**
     * Check if order is custom invoice.
     */
    private function isCustomInvoice(array $orderData): bool
    {
        return isset($orderData['is_custom']) && $orderData['is_custom'];
    }

    /**
     * Handle custom invoice.
     */
    private function handleCustomInvoice(User $user, array $orderData, string $gateway, ?string $transactionId): array
    {
        $invoice = $this->createCustomInvoice($user, $orderData, $gateway, $transactionId);
        DB::commit();
        return $this->buildCustomInvoiceResponse($invoice);
    }

    /**
     * Create custom invoice.
     */
    private function createCustomInvoice(User $user, array $orderData, string $gateway, ?string $transactionId): Invoice
    {
        return Invoice::create([
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
    }

    /**
     * Build custom invoice response.
     */
    private function buildCustomInvoiceResponse(Invoice $invoice): array
    {
        return [
            'success' => true,
            'license' => null,
            'invoice' => $invoice,
        ];
    }

    /**
     * Handle product purchase.
     */
    private function handleProductPurchase(User $user, Product $product, array $orderData, string $gateway, ?string $transactionId): array
    {
        $license = $this->createLicense($user, $product, $gateway);
        $invoice = $this->createProductInvoice($user, $license, $product, $orderData, $gateway, $transactionId);
        DB::commit();
        return $this->buildProductPurchaseResponse($license, $invoice);
    }

    /**
     * Create license for product.
     */
    private function createLicense(User $user, Product $product, string $gateway): License
    {
        return License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_type' => $product->license_type ?? 'single',
            'status' => 'active',
            'max_domains' => $product->max_domains ?? 1,
            'license_expires_at' => $this->calculateLicenseExpiry($product),
            'support_expires_at' => $this->calculateSupportExpiry($product),
            'notes' => "Purchased via {$gateway}",
        ]);
    }

    /**
     * Create product invoice.
     */
    private function createProductInvoice(User $user, License $license, Product $product, array $orderData, string $gateway, ?string $transactionId): Invoice
    {
        $invoiceService = app(InvoiceService::class);
        return $invoiceService->createInvoice(
            $user,
            $license,
            $product,
            $orderData['amount'],
            $orderData['currency'] ?? 'usd',
            $gateway,
            $transactionId
        );
    }

    /**
     * Build product purchase response.
     */
    private function buildProductPurchaseResponse(License $license, Invoice $invoice): array
    {
        return [
            'success' => true,
            'license' => $license,
            'invoice' => $invoice,
        ];
    }

    /**
     * Log license creation error.
     */
    private function logLicenseCreationError(\Exception $e, array $orderData, string $gateway, ?string $transactionId): void
    {
        Log::error('Failed to create license and invoice', [
            'order_data' => $orderData,
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);
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
        $this->validateOrderDataNotEmpty($orderData);
        $this->validateUserId($orderData);
        $this->validateAmount($orderData);
        $this->validateCurrency($orderData);
    }

    /**
     * Validate order data is not empty.
     */
    private function validateOrderDataNotEmpty(array $orderData): void
    {
        if (empty($orderData)) {
            throw new InvalidArgumentException('Order data cannot be empty');
        }
    }

    /**
     * Validate user ID.
     */
    private function validateUserId(array $orderData): void
    {
        if (!isset($orderData['user_id']) || !is_numeric($orderData['user_id']) || $orderData['user_id'] < 1) {
            throw new InvalidArgumentException('Valid user_id is required');
        }
    }

    /**
     * Validate amount.
     */
    private function validateAmount(array $orderData): void
    {
        if (!isset($orderData['amount']) || !is_numeric($orderData['amount']) || $orderData['amount'] <= 0) {
            throw new InvalidArgumentException('Valid amount is required');
        }

        if ($orderData['amount'] > self::MAX_AMOUNT) {
            throw new InvalidArgumentException('Amount cannot exceed ' . self::MAX_AMOUNT);
        }
    }

    /**
     * Validate currency.
     */
    private function validateCurrency(array $orderData): void
    {
        if (!isset($orderData['currency']) || empty($orderData['currency'])) {
            throw new InvalidArgumentException('Currency is required');
        }

        if (strlen($orderData['currency']) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-character code');
        }
    }

    /**
     * Validate payment gateway.
     */
    private function validateGateway(string $gateway): void
    {
        if (!in_array($gateway, self::SUPPORTED_GATEWAYS)) {
            throw new InvalidArgumentException('Unsupported payment gateway');
        }
    }

    /**
     * Validate PayPal credentials.
     */
    private function validatePayPalCredentials(?array $credentials): void
    {
        $this->validatePayPalClientId($credentials);
        $this->validatePayPalClientSecret($credentials);
    }

    /**
     * Validate PayPal client ID.
     */
    private function validatePayPalClientId(?array $credentials): void
    {
        if (empty($credentials['client_id'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_id is required');
        }
    }

    /**
     * Validate PayPal client secret.
     */
    private function validatePayPalClientSecret(?array $credentials): void
    {
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
        $this->validateStripeSecretKeyNotEmpty($secretKey);
        $this->validateStripeSecretKeyFormat($secretKey);
    }

    /**
     * Validate Stripe secret key is not empty.
     */
    private function validateStripeSecretKeyNotEmpty(string $secretKey): void
    {
        if (empty($secretKey)) {
            throw new InvalidArgumentException('Stripe secret_key is required');
        }
    }

    /**
     * Validate Stripe secret key format.
     */
    private function validateStripeSecretKeyFormat(string $secretKey): void
    {
        if (!str_starts_with($secretKey, 'sk_')) {
            throw new InvalidArgumentException('Invalid Stripe secret key format');
        }
    }

    /**
     * Validate transaction ID.
     */
    private function validateTransactionId(string $transactionId): void
    {
        $this->validateTransactionIdNotEmpty($transactionId);
        $this->validateTransactionIdLength($transactionId);
        $this->validateTransactionIdFormat($transactionId);
    }

    /**
     * Validate transaction ID is not empty.
     */
    private function validateTransactionIdNotEmpty(string $transactionId): void
    {
        if (empty($transactionId)) {
            throw new InvalidArgumentException('Transaction ID cannot be empty');
        }
    }

    /**
     * Validate transaction ID length.
     */
    private function validateTransactionIdLength(string $transactionId): void
    {
        $length = strlen($transactionId);
        if ($length < self::TRANSACTION_ID_MIN_LENGTH || $length > self::TRANSACTION_ID_MAX_LENGTH) {
            throw new InvalidArgumentException('Transaction ID must be between ' . self::TRANSACTION_ID_MIN_LENGTH . ' and ' . self::TRANSACTION_ID_MAX_LENGTH . ' characters');
        }
    }

    /**
     * Validate transaction ID format.
     */
    private function validateTransactionIdFormat(string $transactionId): void
    {
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
