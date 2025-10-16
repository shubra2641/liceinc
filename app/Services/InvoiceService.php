<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\SecurityHelper;
use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Exception;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

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
    private DatabaseManager $databaseManager;
    private SecurityHelper $securityHelper;
    private InvoiceServiceContainer $container;

    public function __construct(
        DatabaseManager $databaseManager,
        SecurityHelper $securityHelper,
        InvoiceServiceContainer $container
    ) {
        $this->databaseManager = $databaseManager;
        $this->securityHelper = $securityHelper;
        $this->container = $container;
    }

    /**
     * Execute database operation with transaction and error handling.
     */
    private function executeWithTransaction(callable $operation): mixed
    {
        try {
            $this->databaseManager->beginTransaction();
            $result = $operation();
            $this->databaseManager->commit();
            return $result;
        } catch (Exception $exception) {
            $this->databaseManager->rollBack();
            $this->logError('Database operation failed', $exception);
            throw $exception;
        }
    }

    /**
     * Log error with context.
     */
    private function logError(string $message, Exception $exception, array $context = []): void
    {
        $this->container->getLoggingHelper()->logError($message, $exception, $context);
    }

    /**
     * Create invoice with common fields.
     */
    private function createInvoiceRecord(array $data): Invoice
    {
        return $this->container->getOperationsHelper()->createInvoiceRecord($data);
    }
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
        $this->container->getValidationHelper()->validateInvoiceParameters($license, $paymentStatus);
        
        return $this->executeWithTransaction(function () use ($license, $paymentStatus, $dueDate) {
            return $this->createInvoiceRecord([
                'user_id' => $license->user_id,
                'license_id' => $license->id,
                'amount' => $this->container->getSanitizationHelper()->sanitizeAmount($license->product->price ?? 0),
                'status' => $this->container->getSanitizationHelper()->sanitizeStatus($paymentStatus),
                'paid_at' => $paymentStatus === 'paid' ? now() : null,
                'due_date' => $dueDate ?? now()->addDays(30),
                'notes' => 'Initial license invoice',
            ]);
        });
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
        return $this->container->getOperationsHelper()->generateInvoiceNumber();
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
        $this->container->getValidationHelper()->validateLicense($license);
        
        return $this->executeWithTransaction(function () use ($license, $options) {
            $amount = $options['amount'] ?? $this->container->getSanitizationHelper()->sanitizeAmount($license->product->price ?? 0);
            $description = $options['description'] ?? 'License renewal invoice';
            $dueDate = $options['due_date'] ?? now()->addDays(30);

            $invoice = $this->createInvoiceRecord([
                'user_id' => $license->user_id,
                'license_id' => $license->id,
                'amount' => $this->container->getSanitizationHelper()->sanitizeAmount($amount),
                'due_date' => $dueDate,
                'notes' => $description,
            ]);

            $this->handleRenewalOptions($invoice, $options);
            
            return $invoice;
        });
    }

    /**
     * Handle renewal-specific options.
     */
    private function handleRenewalOptions(Invoice $invoice, array $options): void
    {
        if (isset($options['new_expiry_date'])) {
            $this->container->getLoggingHelper()->logInfo('New expiry date provided for renewal invoice', [
                'invoice_id' => $invoice->id,
                'new_expiry_date' => $options['new_expiry_date'],
            ]);
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
        $this->container->getValidationHelper()->validateInvoice($invoice);
        
        $this->executeWithTransaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $this->activateLicenseOnPayment($invoice);
        });
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
        $this->container->getValidationHelper()->validateInvoice($invoice);
        
        $this->executeWithTransaction(function () use ($invoice) {
            $invoice->update(['status' => 'overdue']);
        });
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
        $stats = $this->container->getOperationsHelper()->getInvoiceStats();
        
        // Sanitize revenue amounts
        foreach (['paid', 'pending', 'overdue', 'cancelled'] as $status) {
            if (isset($stats["{$status}_revenue"])) {
                $stats["{$status}_revenue"] = $this->container->getSanitizationHelper()->sanitizeAmount(
                    (float)$stats["{$status}_revenue"]
                );
            }
        }
        
        return $stats;
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
        $this->container->getValidationHelper()->validatePaymentInvoiceParameters($user, $license, $product, $amount, $currency, $gateway);
        
        return $this->executeWithTransaction(function () use ($user, $license, $product, $amount, $currency, $gateway, $transactionId) {
            return $this->createInvoiceRecord([
                'user_id' => $user->id,
                'license_id' => $license->id,
                'product_id' => $product->id,
                'amount' => $this->container->getSanitizationHelper()->sanitizeAmount($amount),
                'currency' => $this->container->getSanitizationHelper()->sanitizeCurrency($currency),
                'status' => 'paid',
                'paid_at' => now(),
                'billing_address' => $this->container->getSanitizationHelper()->sanitizeInput($user->billing_address ?? null),
                'tax_rate' => 0,
                'tax_amount' => 0,
                'total_amount' => $this->container->getSanitizationHelper()->sanitizeAmount($amount),
                'notes' => "Payment via {$this->container->getSanitizationHelper()->sanitizeInput($gateway)}",
                'metadata' => [
                    'gateway' => $this->container->getSanitizationHelper()->sanitizeInput($gateway),
                    'transaction_id' => $transactionId ? $this->container->getSanitizationHelper()->sanitizeInput($transactionId) : null,
                ],
            ]);
        });
    }



    /**
     * Activate license when invoice is paid.
     *
     * @param Invoice $invoice
     *
     * @return void
     */
    private function activateLicenseOnPayment(Invoice $invoice): void
    {
        if (!$invoice->license) {
            return;
        }

        try {
            $invoice->license->update(['status' => 'active']);

            $this->container->getLoggingHelper()->logInfo('License activated due to invoice payment', [
                'license_id' => $invoice->license->id,
                'license_key' => $invoice->license->license_key,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ]);
        } catch (Exception $exception) {
            $this->logError('Failed to activate license on payment', $exception, [
                'invoice_id' => $invoice->id,
                'license_id' => $invoice->license_id,
            ]);
        }
    }
}
