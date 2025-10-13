<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\SecurityHelper;
use Illuminate\Support\Str;

/**
 * Invoice Service with enhanced security and performance.
 *
 * This service provides comprehensive invoice management functionality including
 * invoice creation, payment processing, status management, and statistical
 * reporting with enhanced security measures and error handling.
 *
 * Features:
 * - Invoice creation for initial licenses and renewals
 * - Payment status management and tracking
 * - Invoice statistics and reporting
 * - Comprehensive error handling with database transactions
 * - Enhanced security measures for financial data
 * - Input validation and sanitization
 * - Performance optimization with efficient queries
 * - Proper logging for errors and warnings only
 *
 * @example
 * // Create initial invoice
 * $invoice = $invoiceService->createInitialInvoice($license, 'paid');
 *
 * // Create renewal invoice
 * $renewalInvoice = $invoiceService->createRenewalInvoice($license);
 *
 * // Get invoice statistics
 * $stats = $invoiceService->getInvoiceStats();
 */
class InvoiceService
{
    /**
     * Create initial invoice for a license with enhanced security.
     *
     * Creates the first invoice for a license with proper validation,
     * error handling, and database transactions for data integrity.
     *
     * @param  License  $license  The license to create invoice for
     * @param  string  $paymentStatus  The payment status (paid, pending, overdue)
     * @param  \DateTimeInterface|null  $dueDate  The due date for the invoice
     *
     * @return Invoice The created invoice
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     * @throws \Exception When database operations fail
     *
     * @example
     * $invoice = $invoiceService->createInitialInvoice($license, 'paid', now()->addDays(30));
     */
    public function createInitialInvoice(
        License $license,
        string $paymentStatus = 'paid',
        ?\DateTimeInterface $dueDate = null,
    ): Invoice {
        try {
            $this->validateInvoiceParameters($license, $paymentStatus);
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
                'trace' => $e->getTraceAsString(),
                'license_id' => $license->id,
                'user_id' => $license->user_id,
            ]);
            throw $e;
        }
    }
    /**
     * Generate unique invoice number with enhanced validation.
     *
     * Generates a unique invoice number with proper validation and
     * collision detection to ensure uniqueness.
     *
     * @return string The generated invoice number
     *
     * @throws \Exception When invoice number generation fails
     */
    protected function generateInvoiceNumber(): string
    {
        try {
            $maxAttempts = 10;
            $attempts = 0;
            do {
                $invoiceNumber = 'INV-' . strtoupper(Str::random(8));
                $attempts++;
                if ($attempts > $maxAttempts) {
                    throw new \Exception(
                        'Failed to generate unique invoice number after ' . $maxAttempts . ' attempts'
                    );
                }
            } while (Invoice::where('invoice_number', $invoiceNumber)->exists());
            return $invoiceNumber;
        } catch (\Exception $e) {
            Log::error('Invoice number generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Create renewal invoice with enhanced security.
     *
     * Creates a renewal invoice for an existing license with proper
     * validation and error handling.
     *
     * @param  License  $license  The license to create renewal invoice for
     * @param  array  $options  Additional options for the invoice
     *
     * @return Invoice The created renewal invoice
     *
     * @throws \InvalidArgumentException When invalid license is provided
     * @throws \Exception When database operations fail
     *
     * @example
     * $renewalInvoice = $invoiceService->createRenewalInvoice($license);
     * $customInvoice = $invoiceService->createRenewalInvoice($license, [
     *     'amount' => 99.99,
     *     'description' => 'Custom renewal description',
     *     'due_date' => now()->addDays(15),
     * ]);
     */
    /**
     * @param array<string, mixed> $options
     */
    public function createRenewalInvoice(License $license, array $options = []): Invoice
    {
        try {
            $this->validateLicense($license);
            DB::beginTransaction();

            // Use provided options or defaults
            $amount = $options['amount'] ?? $this->sanitizeAmount($license->product->price ?? 0);
            $description = $options['description'] ?? 'License renewal invoice';
            $dueDate = $options['due_date'] ?? now()->addDays(30);

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'user_id' => $license->user_id,
                'license_id' => $license->id,
                'amount' => $this->sanitizeAmount($amount),
                'currency' => 'USD',
                'status' => 'pending',
                'due_date' => $dueDate,
                'notes' => $description,
            ]);

            // Handle new expiry date if provided
            if (isset($options['new_expiry_date'])) {
                // You might want to store this in a custom field or handle it differently
                Log::info('New expiry date provided for renewal invoice', [
                    'invoice_id' => $invoice->id,
                    'new_expiry_date' => $options['new_expiry_date'],
                ]);
            }

            DB::commit();
            return $invoice;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create renewal invoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'license_id' => $license->id,
                'user_id' => $license->user_id,
            ]);
            throw $e;
        }
    }
    /**
     * Mark invoice as paid with enhanced validation.
     *
     * Updates invoice status to paid with proper validation and
     * error handling.
     *
     * @param  Invoice  $invoice  The invoice to mark as paid
     *
     * @throws \InvalidArgumentException When invalid invoice is provided
     * @throws \Exception When database operations fail
     *
     * @example
     * $invoiceService->markAsPaid($invoice);
     */
    public function markAsPaid(Invoice $invoice): void
    {
        try {
            $this->validateInvoice($invoice);
            DB::beginTransaction();
            $invoice->status = 'paid';
            $invoice->paid_at = now();
            $invoice->save();

            // Activate license when invoice is paid
            $this->activateLicenseOnPayment($invoice);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark invoice as paid', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);
            throw $e;
        }
    }
    /**
     * Mark invoice as overdue with enhanced validation.
     *
     * Updates invoice status to overdue with proper validation and
     * error handling.
     *
     * @param  Invoice  $invoice  The invoice to mark as overdue
     *
     * @throws \InvalidArgumentException When invalid invoice is provided
     * @throws \Exception When database operations fail
     *
     * @example
     * $invoiceService->markAsOverdue($invoice);
     */
    public function markAsOverdue(Invoice $invoice): void
    {
        try {
            $this->validateInvoice($invoice);
            DB::beginTransaction();
            $invoice->status = 'overdue';
            $invoice->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to mark invoice as overdue', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);
            throw $e;
        }
    }
    /**
     * Get invoice statistics with enhanced performance.
     *
     * Retrieves comprehensive invoice statistics with optimized queries
     * and proper error handling.
     *
     * @return array<string, mixed> Array of invoice statistics
     *
     * @throws \Exception When database operations fail
     *
     * @example
     * $stats = $invoiceService->getInvoiceStats();
     * echo "Total revenue: $" . $stats['total_revenue'];
     */
    public function getInvoiceStats(): array
    {
        try {
            return [
                'total_invoices' => Invoice::count(),
                'paid_invoices' => Invoice::where('status', 'paid')->count(),
                'pending_invoices' => Invoice::where('status', 'pending')->count(),
                'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
                'cancelled_invoices' => Invoice::where('status', 'cancelled')->count(),
                'total_revenue' => $this->sanitizeAmount((float)Invoice::where('status', 'paid')->sum('amount')),
                'pending_revenue' => $this->sanitizeAmount((float)Invoice::where('status', 'pending')->sum('amount')),
                'overdue_revenue' => $this->sanitizeAmount((float)Invoice::where('status', 'overdue')->sum('amount')),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get invoice statistics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    /**
     * Create invoice for payment system with enhanced security.
     *
     * Creates a comprehensive invoice for payment processing with proper
     * validation, error handling, and security measures.
     *
     * @param  User  $user  The user for the invoice
     * @param  License  $license  The license for the invoice
     * @param  Product  $product  The product for the invoice
     * @param  float  $amount  The invoice amount
     * @param  string  $currency  The currency code
     * @param  string  $gateway  The payment gateway used
     * @param  string|null  $transactionId  The transaction ID
     *
     * @return Invoice The created invoice
     *
     * @throws \InvalidArgumentException When invalid parameters are provided
     * @throws \Exception When database operations fail
     *
     * @example
     * $invoice = $invoiceService->createInvoice($user, $license, $product, 99.99, 'USD', 'stripe', 'txn_123');
     */
    public function createInvoice(
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
                'trace' => $e->getTraceAsString(),
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
     * Validate invoice parameters.
     *
     * @param  License  $license  The license to validate
     * @param  string  $paymentStatus  The payment status to validate
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validateInvoiceParameters(License $license, string $paymentStatus): void
    {
        $this->validateLicense($license);
        $validStatuses = ['paid', 'pending', 'overdue', 'cancelled'];
        if (! in_array($paymentStatus, $validStatuses)) {
            throw new \InvalidArgumentException(
                'Invalid payment status: ' . SecurityHelper::escapeVariable($paymentStatus)
            );
        }
    }
    /**
     * Validate license.
     *
     * @param  License  $license  The license to validate
     *
     * @throws \InvalidArgumentException When license is invalid
     */
    private function validateLicense(License $license): void
    {
        if (! $license->exists) {
            throw new \InvalidArgumentException('License does not exist');
        }
        if (! $license->user_id) {
            throw new \InvalidArgumentException('License must have a user');
        }
        if (! $license->product) {
            throw new \InvalidArgumentException('License must have a product');
        }
    }
    /**
     * Validate invoice.
     *
     * @param  Invoice  $invoice  The invoice to validate
     *
     * @throws \InvalidArgumentException When invoice is invalid
     */
    private function validateInvoice(Invoice $invoice): void
    {
        if (! $invoice->exists) {
            throw new \InvalidArgumentException('Invoice does not exist');
        }
    }
    /**
     * Validate payment invoice parameters.
     *
     * @param  User  $user  The user to validate
     * @param  License  $license  The license to validate
     * @param  Product  $product  The product to validate
     * @param  float  $amount  The amount to validate
     * @param  string  $currency  The currency to validate
     * @param  string  $gateway  The gateway to validate
     *
     * @throws \InvalidArgumentException When validation fails
     */
    private function validatePaymentInvoiceParameters(
        User $user,
        License $license,
        Product $product,
        float $amount,
        string $currency,
        string $gateway,
    ): void {
        if (! $user->exists) {
            throw new \InvalidArgumentException('User does not exist');
        }
        $this->validateLicense($license);
        if (! $product->exists) {
            throw new \InvalidArgumentException('Product does not exist');
        }
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }
        if (empty($currency)) {
            throw new \InvalidArgumentException('Currency cannot be empty');
        }
        if (empty($gateway)) {
            throw new \InvalidArgumentException('Gateway cannot be empty');
        }
    }
    /**
     * Sanitize amount for security.
     *
     * @param  mixed  $amount  The amount to sanitize
     *
     * @return float The sanitized amount
     */
    private function sanitizeAmount(mixed $amount): float
    {
        if (!is_numeric($amount)) {
            return 0.0;
        }
        return max(0, round((float)$amount, 2));
    }
    /**
     * Sanitize status for security.
     *
     * @param  string  $status  The status to sanitize
     *
     * @return string The sanitized status
     */
    private function sanitizeStatus(string $status): string
    {
        $validStatuses = ['paid', 'pending', 'overdue', 'cancelled'];
        return in_array($status, $validStatuses) ? $status : 'pending';
    }
    /**
     * Sanitize currency for security.
     *
     * @param  string  $currency  The currency to sanitize
     *
     * @return string The sanitized currency
     */
    private function sanitizeCurrency(string $currency): string
    {
        return strtoupper(trim($currency));
    }
    /**
     * Sanitize input to prevent XSS attacks.
     *
     * @param  mixed  $input  The input to sanitize
     *
     * @return string|null The sanitized input
     */
    private function sanitizeInput(mixed $input): ?string
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (!is_string($input)) {
            return null;
        }

        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Activate license when invoice is paid
     *
     * @param Invoice $invoice
     *
     * @return void
     */
    private function activateLicenseOnPayment(Invoice $invoice): void
    {
        try {
            if ($invoice->license) {
                $invoice->license->update(['status' => 'active']);
                
                Log::info('License activated due to invoice payment', [
                    'license_id' => $invoice->license->id,
                    'license_key' => $invoice->license->license_key,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to activate license on payment', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id,
                'license_id' => $invoice->license_id,
            ]);
        }
    }
}
