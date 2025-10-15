<?php

declare(strict_types=1);

namespace App\Services\Payment\Processors;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\Payment\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Payment Processor.
 * 
 * Handles payment processing logic including license creation,
 * invoice management, and transaction handling.
 */
class PaymentProcessor
{
    private ResponseHelper $responseHelper;
    private InvoiceService $invoiceService;

    public function __construct(ResponseHelper $responseHelper, InvoiceService $invoiceService)
    {
        $this->responseHelper = $responseHelper;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Create license and invoice for payment.
     */
    public function createLicenseAndInvoice(array $orderData, string $gateway, ?string $transactionId = null): array
    {
        try {
            DB::beginTransaction();
            
            $result = $this->processOrder($orderData, $gateway, $transactionId);
            DB::commit();
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, $orderData, $gateway, $transactionId);
            return $this->responseHelper->buildFailure('License and invoice creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Process order logic.
     */
    private function processOrder(array $orderData, string $gateway, ?string $transactionId): array
    {
        $user = $this->getUser($orderData['user_id']);
        $product = $this->getProduct($orderData['product_id'] ?? null);

        // Check existing invoice
        $existing = $this->checkExistingInvoice($orderData, $gateway, $transactionId);
        if ($existing) {
            return $existing;
        }

        // Handle custom invoice
        if (!empty($orderData['is_custom'])) {
            return $this->handleCustomInvoice($user, $orderData, $gateway, $transactionId);
        }

        // Create product license and invoice
        if ($product) {
            return $this->createProductLicenseAndInvoice($user, $product, $orderData, $gateway, $transactionId);
        }

        throw new \Exception('Product not found');
    }

    /**
     * Check for existing invoice.
     */
    private function checkExistingInvoice(array $orderData, string $gateway, ?string $transactionId): ?array
    {
        if (!$transactionId) {
            return null;
        }

        $existingInvoice = Invoice::where('metadata->transaction_id', $transactionId)
            ->where('metadata->gateway', $gateway)
            ->first();

        if ($existingInvoice) {
            return $this->responseHelper->buildSuccess([
                'invoice_id' => $existingInvoice->id,
                'license_id' => $existingInvoice->license_id,
                'message' => 'Invoice already exists'
            ]);
        }

        return null;
    }

    /**
     * Log error details.
     */
    private function logError(\Exception $e, array $orderData, string $gateway, ?string $transactionId): void
    {
        Log::error('License and invoice creation failed', [
            'order_data' => $orderData,
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'error' => $e->getMessage()
        ]);
    }

    /**
     * Get user by ID.
     * 
     * @param int $userId User ID
     * @return User User instance
     * @throws \Exception When user not found
     */
    private function getUser(int $userId): User
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('User not found');
        }
        return $user;
    }

    /**
     * Get product by ID.
     * 
     * @param int|null $productId Product ID
     * @return Product|null Product instance or null
     */
    private function getProduct(?int $productId): ?Product
    {
        if (!$productId) {
            return null;
        }
        return Product::find($productId);
    }


    /**
     * Handle custom invoice creation.
     * 
     * @param User $user User instance
     * @param array $orderData Order data
     * @param string $gateway Payment gateway
     * @param string|null $transactionId Transaction ID
     * @return array Processing result
     */
    private function handleCustomInvoice(User $user, array $orderData, string $gateway, ?string $transactionId): array
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
            'metadata' => $this->buildInvoiceMetadata($gateway, $transactionId, true),
        ]);

        return $this->commitAndReturn(null, $invoice);
    }

    /**
     * Create product license and invoice.
     * 
     * @param User $user User instance
     * @param Product $product Product instance
     * @param array $orderData Order data
     * @param string $gateway Payment gateway
     * @param string|null $transactionId Transaction ID
     * @return array Processing result
     */
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

        $invoice = $this->invoiceService->createInvoice(
            $user,
            $license,
            $product,
            $orderData['amount'],
            $orderData['currency'] ?? 'usd',
            $gateway,
            $transactionId
        );

        return $this->commitAndReturn($license, $invoice);
    }

    /**
     * Commit transaction and return result.
     * 
     * @param License|null $license License instance
     * @param Invoice $invoice Invoice instance
     * @return array Processing result
     */
    private function commitAndReturn(?License $license, Invoice $invoice): array
    {
        DB::commit();

        return $this->responseHelper->buildSuccess([
            'invoice_id' => $invoice->id,
            'license_id' => $license?->id,
            'invoice_number' => $invoice->invoice_number,
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
        ]);
    }

    /**
     * Generate invoice number.
     * 
     * @return string Generated invoice number
     */
    private function generateInvoiceNumber(): string
    {
        return 'INV-' . date('Y') . '-' . strtoupper(\Illuminate\Support\Str::random(8));
    }

    /**
     * Build invoice metadata.
     * 
     * @param string $gateway Payment gateway
     * @param string|null $transactionId Transaction ID
     * @param bool $isCustom Whether it's a custom invoice
     * @return array Metadata array
     */
    private function buildInvoiceMetadata(string $gateway, ?string $transactionId, bool $isCustom = false): array
    {
        $metadata = [
            'gateway' => $gateway,
            'created_at' => now()->toISOString(),
        ];

        if ($transactionId) {
            $metadata['transaction_id'] = $transactionId;
        }

        if ($isCustom) {
            $metadata['type'] = 'custom';
        }

        return $metadata;
    }

    /**
     * Calculate license expiry date.
     * 
     * @param Product $product Product instance
     * @return \DateTimeInterface License expiry date
     */
    private function calculateLicenseExpiry(Product $product): \DateTimeInterface
    {
        $duration = $product->license_duration ?? 365;
        return now()->addDays($duration);
    }

    /**
     * Calculate support expiry date.
     * 
     * @param Product $product Product instance
     * @return \DateTimeInterface Support expiry date
     */
    private function calculateSupportExpiry(Product $product): \DateTimeInterface
    {
        $defaultSupportDuration = $product->support_duration ?? 365;
        return now()->addDays($defaultSupportDuration);
    }
}
