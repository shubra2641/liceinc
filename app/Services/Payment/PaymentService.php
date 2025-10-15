<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * Simplified Payment Service with essential functionality.
 */
class PaymentService
{
    public function __construct(
        private PayPalPaymentService $paypalService,
        private StripePaymentService $stripeService,
        private PaymentValidationService $validationService
    ) {
    }

    /**
     * Process payment with the specified gateway.
     */
    public function processPayment(array $orderData, string $gateway): array
    {
        try {
            $this->validationService->validateOrderData($orderData);
            $this->validationService->validateGateway($gateway);

            return match ($gateway) {
                'paypal' => $this->paypalService->processPayment($orderData),
                'stripe' => $this->stripeService->processPayment($orderData),
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
     * Verify payment with gateway.
     */
    public function verifyPayment(string $gateway, string $transactionId, ?string $payerId = null): array
    {
        try {
            $this->validationService->validateGateway($gateway);
            $this->validationService->validateTransactionId($transactionId);

            return match ($gateway) {
                'paypal' => $this->paypalService->verifyPayment($transactionId, $payerId ?? ''),
                'stripe' => $this->stripeService->verifyPayment($transactionId),
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
     * Create license and invoice after successful payment.
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            $this->validationService->validateOrderData($orderData);
            $this->validationService->validateGateway($gateway);

            DB::beginTransaction();

            $user = User::find($orderData['user_id']);
            if (!$user) {
                throw new \Exception('User not found');
            }

            $product = isset($orderData['product_id']) ? Product::find($orderData['product_id']) : null;

            // Handle existing invoice
            $invoiceId = $orderData['invoice_id'] ?? null;
            if ($invoiceId) {
                $existingInvoice = Invoice::find($invoiceId);
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
            if (!empty($orderData['is_custom'])) {
                $invoice = $this->createCustomInvoice($user, $orderData, $gateway, $transactionId);
            } else {
                $invoice = $this->createProductInvoice($user, $product, $orderData, $gateway, $transactionId);
            }

            DB::commit();

            return [
                'success' => true,
                'license' => $invoice->license,
                'invoice' => $invoice,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('License and invoice creation failed', [
                'order_data' => $orderData,
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create license and invoice',
            ];
        }
    }

    /**
     * Create custom invoice.
     */
    private function createCustomInvoice(User $user, array $orderData, string $gateway, ?string $transactionId): Invoice
    {
        $invoice = Invoice::create([
            'user_id' => $user->id,
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'],
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => "Custom payment via {$gateway}",
            'metadata' => [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
                'custom' => true,
            ]
        ]);

        return $invoice;
    }

    /**
     * Create product invoice with license.
     */
    private function createProductInvoice(User $user, ?Product $product, array $orderData, string $gateway, ?string $transactionId): Invoice
    {
        if (!$product) {
            throw new \Exception('Product not found');
        }

        $license = License::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_key' => $this->generateLicenseKey(),
            'status' => 'active',
            'expires_at' => now()->addYear(),
        ]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'license_id' => $license->id,
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'],
            'status' => 'paid',
            'paid_at' => now(),
            'notes' => "Product purchase via {$gateway}",
            'metadata' => [
                'gateway' => $gateway,
                'transaction_id' => $transactionId,
            ]
        ]);

        return $invoice;
    }

    /**
     * Generate unique license key.
     */
    private function generateLicenseKey(): string
    {
        return strtoupper(substr(md5(uniqid()), 0, 8) . '-' . substr(md5(uniqid()), 0, 8) . '-' . substr(md5(uniqid()), 0, 8));
    }
}
