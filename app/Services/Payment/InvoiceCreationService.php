<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Invoice Creation Service - Handles invoice creation operations.
 */
class InvoiceCreationService
{
    /**
     * Create initial invoice for a license.
     */
    public function createInitialInvoice(
        License $license,
        string $paymentStatus = 'paid',
        ?\DateTimeInterface $dueDate = null,
    ): Invoice {
        try {
            DB::beginTransaction();
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $license->user_id,
                'license_id' => $license->id,
                'amount' => $this->sanitizeAmount($license->product->price ?? 0),
                'currency' => 'USD',
                'status' => $this->sanitizeStatus($paymentStatus),
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'due_date' => $dueDate ?? now()->addDays(30),
                'notes' => 'Initial license invoice',
            ]);
            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create initial invoice', [
                'error' => $e->getMessage(),
                'license_id' => $license->id,
                'user_id' => $license->user_id,
            ]);
            throw $e;
        }
    }

    /**
     * Create renewal invoice for a license.
     */
    public function createRenewalInvoice(License $license): Invoice
    {
        try {
            DB::beginTransaction();
            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $license->user_id,
                'license_id' => $license->id,
                'amount' => $this->sanitizeAmount($license->product->price ?? 0),
                'currency' => 'USD',
                'status' => 'pending',
                'due_date' => now()->addDays(30),
                'notes' => 'License renewal invoice',
            ]);
            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create renewal invoice', [
                'error' => $e->getMessage(),
                'license_id' => $license->id,
                'user_id' => $license->user_id,
            ]);
            throw $e;
        }
    }

    /**
     * Create invoice for payment system.
     */
    public function createPaymentInvoice(
        User $user,
        License $license,
        Product $product,
        float $amount,
        string $currency,
        string $gateway,
        ?string $transactionId = null,
    ): Invoice {
        try {
            $this->validatePaymentInvoiceParameters($user, $license, $product, $amount, $currency, $gateway);
            DB::beginTransaction();
            $invoice = Invoice::create([
                'user_id' => $user->id,
                'license_id' => $license->id,
                'product_id' => $product->id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'amount' => $this->sanitizeAmount($amount),
                'currency' => $this->sanitizeCurrency($currency),
                'status' => 'paid',
                'paid_at' => now(),
                'due_date' => now()->addDays(30),
                'billing_address' => $this->sanitizeInput($user->billing_address ?? null),
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $this->sanitizeAmount($amount),
                'notes' => "Payment via {$this->sanitizeInput($gateway)}",
                'metadata' => [
                    'gateway' => $this->sanitizeInput($gateway),
                    'transaction_id' => $transactionId ? $this->sanitizeInput($transactionId) : null,
                ],
            ]);
            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create payment invoice', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'license_id' => $license->id,
                'product_id' => $product->id,
                'amount' => $amount,
                'gateway' => $gateway,
            ]);
            throw $e;
        }
    }

    /**
     * Generate unique invoice number.
     */
    protected function generateInvoiceNumber(): string
    {
        try {
            $maxAttempts = 10;
            $attempts = 0;
            do {
                $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                $attempts++;
            } while (Invoice::where('invoice_number', $invoiceNumber)->exists() && $attempts < $maxAttempts);

            if ($attempts >= $maxAttempts) {
                throw new \Exception('Failed to generate unique invoice number after maximum attempts');
            }

            return $invoiceNumber;
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice number', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Validate payment invoice parameters.
     */
    private function validatePaymentInvoiceParameters(
        User $user,
        License $license,
        Product $product,
        float $amount,
        string $currency,
        string $gateway
    ): void {
        if (!$user || !$user->id) {
            throw new \InvalidArgumentException('Invalid user provided');
        }

        if (!$license || !$license->id) {
            throw new \InvalidArgumentException('Invalid license provided');
        }

        if (!$product || !$product->id) {
            throw new \InvalidArgumentException('Invalid product provided');
        }

        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        if (empty($currency)) {
            throw new \InvalidArgumentException('Currency is required');
        }

        if (empty($gateway)) {
            throw new \InvalidArgumentException('Payment gateway is required');
        }
    }

    /**
     * Sanitize amount.
     */
    private function sanitizeAmount(float $amount): float
    {
        return max(0, round($amount, 2));
    }

    /**
     * Sanitize status.
     */
    private function sanitizeStatus(string $status): string
    {
        $allowedStatuses = ['paid', 'pending', 'overdue', 'cancelled'];
        return in_array($status, $allowedStatuses) ? $status : 'pending';
    }

    /**
     * Sanitize currency.
     */
    private function sanitizeCurrency(string $currency): string
    {
        return strtoupper(trim($currency));
    }

    /**
     * Sanitize input.
     */
    private function sanitizeInput(?string $input): ?string
    {
        if (!$input) {
            return null;
        }
        return trim(strip_tags($input));
    }
}
