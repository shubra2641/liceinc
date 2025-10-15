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

class PaymentService
{
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
            $this->logError('Payment processing failed', $gateway, $orderData, $e);
            throw $e;
        }
    }

    protected function processPayPalPayment(array $orderData): array
    {
        try {
            $apiContext = $this->getPayPalApiContext();
            $payment = $this->createPayPalPayment($orderData, $apiContext);
            $approvalUrl = $this->getPayPalApprovalUrl($payment);

            return $this->createSuccessResponse($approvalUrl, $payment->getId());
        } catch (\Exception $e) {
            $this->logError('PayPal payment processing failed', 'paypal', $orderData, $e);
            return $this->createErrorResponse('PayPal payment processing failed: ' . $e->getMessage());
        }
    }

    protected function processStripePayment(array $orderData): array
    {
        try {
            $this->setupStripeApi();
            $session = $this->createStripeSession($orderData);

            return $this->createSuccessResponse($session->url, $session->id);
        } catch (\Exception $e) {
            $this->logError('Stripe payment processing failed', 'stripe', $orderData, $e);
            return $this->createErrorResponse('Stripe payment processing failed: ' . $e->getMessage());
        }
    }

    public function verifyPayment(string $gateway, string $transactionId): array
    {
        try {
            $this->validateGateway($gateway);
            $this->validateTransactionId($transactionId);

            return match ($gateway) {
                'paypal' => $this->verifyPayPalPayment($transactionId),
                'stripe' => $this->verifyStripePayment($transactionId),
                default => $this->createErrorResponse('Unsupported payment gateway')
            };
        } catch (\Exception $e) {
            $this->logError('Payment verification failed', $gateway, ['transaction_id' => $transactionId], $e);
            return $this->createErrorResponse('Payment verification failed');
        }
    }

    protected function verifyPayPalPayment(string $paymentId): array
    {
        try {
            $apiContext = $this->getPayPalApiContext();
            $payment = Payment::get($paymentId, $apiContext);

            if ($payment->getState() === 'approved') {
                $execution = new PaymentExecution();
                $execution->setPayerId(request()->get('PayerID', ''));
                $result = $payment->execute($execution, $apiContext);

                if ($result->getState() === 'approved') {
                    return $this->createSuccessResponse('Payment verified successfully', $paymentId);
                }
            }

            return $this->createErrorResponse('Payment not approved');
        } catch (\Exception $e) {
            $this->logError('PayPal verification failed', 'paypal', ['payment_id' => $paymentId], $e);
            return $this->createErrorResponse('PayPal verification failed');
        }
    }

    protected function verifyStripePayment(string $transactionId): array
    {
        try {
            $this->setupStripeApi();
            $session = Session::retrieve($transactionId);

            if ($session->payment_status === 'paid') {
                return $this->createSuccessResponse('Payment verified successfully', $transactionId);
            }

            return $this->createErrorResponse('Payment not completed');
        } catch (\Exception $e) {
            $this->logError('Stripe verification failed', 'stripe', ['transaction_id' => $transactionId], $e);
            return $this->createErrorResponse('Stripe verification failed');
        }
    }

    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            $this->validateOrderData($orderData);
            $this->validateGateway($gateway);

            DB::beginTransaction();

            $user = $this->getUser($orderData['user_id']);
            $product = $this->getProduct($orderData['product_id'] ?? null);

            // Handle existing invoice
            if (isset($orderData['invoice_id']) && $orderData['invoice_id']) {
                return $this->handleExistingInvoice($orderData['invoice_id'], $gateway, $transactionId);
            }

            // Handle custom invoice
            if (isset($orderData['is_custom']) && $orderData['is_custom']) {
                return $this->createCustomInvoice($user, $orderData, $gateway, $transactionId);
            }

            // Create license and invoice for product purchase
            if ($product) {
                return $this->createProductLicenseAndInvoice($user, $product, $orderData, $gateway, $transactionId);
            }

            throw new \Exception('Product not found');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Failed to create license and invoice', $gateway, $orderData, $e);
            throw $e;
        }
    }

    public function handleWebhook(ServiceRequest $request, string $gateway): array
    {
        try {
            $this->validateGateway($gateway);

            return match ($gateway) {
                'stripe' => $this->handleStripeWebhook($request),
                'paypal' => $this->handlePayPalWebhook($request),
                default => $this->createErrorResponse('Unsupported gateway')
            };
        } catch (\Exception $e) {
            $this->logError('Webhook processing failed', $gateway, [], $e);
            return $this->createErrorResponse('Webhook processing failed');
        }
    }

    private function handleStripeWebhook(ServiceRequest $request): array
    {
        return $this->createSuccessResponse('Stripe webhook processed');
    }

    private function handlePayPalWebhook(ServiceRequest $request): array
    {
        return $this->createSuccessResponse('PayPal webhook processed');
    }

    private function getUser(int $userId): User
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }
        return $user;
    }

    private function getProduct(?int $productId): ?Product
    {
        return $productId ? Product::find($productId) : null;
    }

    private function handleExistingInvoice(int $invoiceId, string $gateway, ?string $transactionId): array
    {
        $existingInvoice = Invoice::find($invoiceId);
        if (!$existingInvoice) {
            throw new \Exception('Invoice not found');
        }

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

    private function createCustomInvoice(User $user, array $orderData, string $gateway, ?string $transactionId): array
    {
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

    private function createProductLicenseAndInvoice(User $user, Product $product, array $orderData, string $gateway, ?string $transactionId): array
    {
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

    private function getPayPalApiContext(): ApiContext
    {
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
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'INFO',
        ]);

        return $apiContext;
    }

    private function createPayPalPayment(array $orderData, ApiContext $apiContext): Payment
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
        $appUrl = config('app.url');
        $redirectUrls->setReturnUrl($appUrl . '/payment/success/paypal')
            ->setCancelUrl($appUrl . '/payment/cancel/paypal');

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions([$transaction]);
        $payment->setRedirectUrls($redirectUrls);

        $payment->create($apiContext);
        return $payment;
    }

    private function getPayPalApprovalUrl(Payment $payment): ?string
    {
        foreach ($payment->getLinks() as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        return null;
    }

    private function setupStripeApi(): void
    {
        $settings = PaymentSetting::getByGateway('stripe');
        if (!$settings) {
            throw new \Exception('Stripe settings not found');
        }

        $credentials = $settings->credentials;
        $this->validateStripeCredentials($credentials);

        Stripe::setApiKey($credentials['secret_key'] ?? '');
    }

    private function createStripeSession(array $orderData): Session
    {
        $appUrl = config('app.url');
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
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
            ],
            'mode' => 'payment',
            'success_url' => $appUrl . '/payment/success/stripe',
            'cancel_url' => $appUrl . '/payment/cancel/stripe',
            'metadata' => [
                'user_id' => $orderData['user_id'] ?? '',
                'product_id' => $orderData['product_id'] ?? '',
            ],
        ]);
    }

    private function createSuccessResponse(string $message, ?string $id = null): array
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($id) {
            $response['transaction_id'] = $id;
        }

        return $response;
    }

    private function createErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message,
        ];
    }

    private function logError(string $message, string $gateway, array $data, \Exception $e): void
    {
        Log::error($message, [
            'gateway' => $gateway,
            'data' => $data,
            'error' => $e->getMessage()
        ]);
    }

    private function validateOrderData(array $orderData): void
    {
        if (empty($orderData)) {
            throw new InvalidArgumentException('Order data cannot be empty');
        }

        $this->validateUserId($orderData['user_id'] ?? null);
        $this->validateAmount($orderData['amount'] ?? null);
        $this->validateCurrency($orderData['currency'] ?? null);
    }

    private function validateUserId($userId): void
    {
        if (!isset($userId) || !is_numeric($userId) || $userId < 1) {
            throw new InvalidArgumentException('Valid user_id is required');
        }
    }

    private function validateAmount($amount): void
    {
        if (!isset($amount) || !is_numeric($amount) || $amount <= 0) {
            throw new InvalidArgumentException('Valid amount is required');
        }
        if ($amount > 999999.99) {
            throw new InvalidArgumentException('Amount cannot exceed 999,999.99');
        }
    }

    private function validateCurrency($currency): void
    {
        if (!isset($currency) || empty($currency)) {
            throw new InvalidArgumentException('Currency is required');
        }
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-character code');
        }
    }

    private function validateGateway(string $gateway): void
    {
        if (!in_array($gateway, ['paypal', 'stripe'])) {
            throw new InvalidArgumentException('Unsupported payment gateway');
        }
    }

    private function validatePayPalCredentials(?array $credentials): void
    {
        if (empty($credentials['client_id'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_id is required');
        }
        if (empty($credentials['client_secret'] ?? '')) {
            throw new InvalidArgumentException('PayPal client_secret is required');
        }
    }

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

    protected function generateInvoiceNumber(): string
    {
        do {
            $invoiceNumber = 'INV-' . strtoupper(\Illuminate\Support\Str::random(8));
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }

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

    protected function calculateSupportExpiry(Product $product): \DateTime
    {
        $productSupportDays = $product->support_days ?? null;
        $defaultSupportDuration = \App\Helpers\ConfigHelper::getSetting('license_support_duration', 365);
        $supportDuration = is_numeric($productSupportDays)
            ? (int)$productSupportDays
            : (is_numeric($defaultSupportDuration) ? (int)$defaultSupportDuration : 365);

        return now()->addDays($supportDuration);
    }

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
