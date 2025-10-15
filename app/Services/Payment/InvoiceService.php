<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Invoice Service - Provides comprehensive invoice management functionality.
 */
class InvoiceService
{
    public function __construct(
        private InvoiceCreationService $creationService,
        private InvoiceStatisticsService $statisticsService,
        private InvoiceValidationService $validationService
    ) {
    }

    /**
     * Create initial invoice for a license.
     */
    public function createInitialInvoice(
        License $license,
        string $paymentStatus = 'paid',
        ?\DateTimeInterface $dueDate = null,
    ): Invoice {
        return $this->creationService->createInitialInvoice($license, $paymentStatus, $dueDate);
    }

    /**
     * Create renewal invoice for a license.
     */
    public function createRenewalInvoice(License $license): Invoice
    {
        return $this->creationService->createRenewalInvoice($license);
    }

    /**
     * Create invoice for payment system.
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
        return $this->creationService->createPaymentInvoice(
            $user,
            $license,
            $product,
            $amount,
            $currency,
            $gateway,
            $transactionId
        );
    }

    /**
     * Get comprehensive invoice statistics.
     */
    public function getInvoiceStats(): array
    {
        return $this->statisticsService->getInvoiceStats();
    }

    /**
     * Get revenue statistics by period.
     */
    public function getRevenueByPeriod(string $period = 'month'): array
    {
        return $this->statisticsService->getRevenueByPeriod($period);
    }

    /**
     * Get invoice status distribution.
     */
    public function getStatusDistribution(): array
    {
        return $this->statisticsService->getStatusDistribution();
    }

    /**
     * Get top customers by revenue.
     */
    public function getTopCustomersByRevenue(int $limit = 10): array
    {
        return $this->statisticsService->getTopCustomersByRevenue($limit);
    }

    /**
     * Get invoice trends.
     */
    public function getInvoiceTrends(int $months = 12): array
    {
        return $this->statisticsService->getInvoiceTrends($months);
    }

    /**
     * Get overdue invoices.
     */
    public function getOverdueInvoices(): array
    {
        return $this->statisticsService->getOverdueInvoices();
    }

    /**
     * Validate invoice parameters.
     */
    public function validateInvoiceParameters(
        User $user,
        License $license,
        Product $product,
        float $amount,
        string $currency,
        string $gateway
    ): void {
        $this->validationService->validateInvoiceParameters(
            $user,
            $license,
            $product,
            $amount,
            $currency,
            $gateway
        );
    }

    /**
     * Validate invoice status.
     */
    public function validateInvoiceStatus(string $status): bool
    {
        return $this->validationService->validateInvoiceStatus($status);
    }

    /**
     * Validate invoice amount.
     */
    public function validateInvoiceAmount(float $amount): bool
    {
        return $this->validationService->validateInvoiceAmount($amount);
    }

    /**
     * Validate invoice currency.
     */
    public function validateInvoiceCurrency(string $currency): bool
    {
        return $this->validationService->validateInvoiceCurrency($currency);
    }

    /**
     * Validate invoice due date.
     */
    public function validateInvoiceDueDate(\DateTimeInterface $dueDate): bool
    {
        return $this->validationService->validateInvoiceDueDate($dueDate);
    }

    /**
     * Validate invoice number format.
     */
    public function validateInvoiceNumber(string $invoiceNumber): bool
    {
        return $this->validationService->validateInvoiceNumber($invoiceNumber);
    }

    /**
     * Check if invoice exists.
     */
    public function invoiceExists(int $invoiceId): bool
    {
        return $this->validationService->invoiceExists($invoiceId);
    }

    /**
     * Check if invoice belongs to user.
     */
    public function invoiceBelongsToUser(int $invoiceId, int $userId): bool
    {
        return $this->validationService->invoiceBelongsToUser($invoiceId, $userId);
    }

    /**
     * Check if invoice is paid.
     */
    public function isInvoicePaid(int $invoiceId): bool
    {
        return $this->validationService->isInvoicePaid($invoiceId);
    }

    /**
     * Check if invoice is overdue.
     */
    public function isInvoiceOverdue(int $invoiceId): bool
    {
        return $this->validationService->isInvoiceOverdue($invoiceId);
    }

    /**
     * Validate invoice creation data.
     */
    public function validateInvoiceCreationData(array $data): array
    {
        return $this->validationService->validateInvoiceCreationData($data);
    }

    /**
     * Validate invoice update data.
     */
    public function validateInvoiceUpdateData(array $data): array
    {
        return $this->validationService->validateInvoiceUpdateData($data);
    }

    /**
     * Validate invoice payment.
     */
    public function validateInvoicePayment(int $invoiceId, float $amount): array
    {
        return $this->validationService->validateInvoicePayment($invoiceId, $amount);
    }

    /**
     * Validate invoice cancellation.
     */
    public function validateInvoiceCancellation(int $invoiceId): array
    {
        return $this->validationService->validateInvoiceCancellation($invoiceId);
    }

    /**
     * Update invoice status.
     */
    public function updateInvoiceStatus(int $invoiceId, string $status): bool
    {
        try {
            if (!$this->validateInvoiceStatus($status)) {
                throw new \InvalidArgumentException('Invalid invoice status');
            }

            $invoice = Invoice::find($invoiceId);
            if (!$invoice) {
                throw new \Exception('Invoice not found');
            }

            $invoice->status = $status;
            if ($status === 'paid') {
                $invoice->paid_at = now();
            }
            $invoice->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update invoice status', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId,
                'status' => $status,
            ]);
            throw $e;
        }
    }

    /**
     * Cancel invoice.
     */
    public function cancelInvoice(int $invoiceId): bool
    {
        try {
            $errors = $this->validateInvoiceCancellation($invoiceId);
            if (!empty($errors)) {
                throw new \InvalidArgumentException('Cannot cancel invoice: ' . implode(', ', $errors));
            }

            return $this->updateInvoiceStatus($invoiceId, 'cancelled');
        } catch (\Exception $e) {
            Log::error('Failed to cancel invoice', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId,
            ]);
            throw $e;
        }
    }

    /**
     * Mark invoice as paid.
     */
    public function markInvoiceAsPaid(int $invoiceId): bool
    {
        try {
            return $this->updateInvoiceStatus($invoiceId, 'paid');
        } catch (\Exception $e) {
            Log::error('Failed to mark invoice as paid', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId,
            ]);
            throw $e;
        }
    }

    /**
     * Get invoice by ID.
     */
    public function getInvoice(int $invoiceId): ?Invoice
    {
        try {
            return Invoice::find($invoiceId);
        } catch (\Exception $e) {
            Log::error('Failed to get invoice', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoiceId,
            ]);
            throw $e;
        }
    }

    /**
     * Get invoices by user.
     */
    public function getInvoicesByUser(int $userId, int $limit = 50): array
    {
        try {
            return Invoice::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get invoices by user', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            throw $e;
        }
    }

    /**
     * Get invoices by status.
     */
    public function getInvoicesByStatus(string $status, int $limit = 50): array
    {
        try {
            if (!$this->validateInvoiceStatus($status)) {
                throw new \InvalidArgumentException('Invalid invoice status');
            }

            return Invoice::where('status', $status)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('Failed to get invoices by status', [
                'error' => $e->getMessage(),
                'status' => $status,
            ]);
            throw $e;
        }
    }
}
