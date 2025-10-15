<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Invoice Validation Service - Handles invoice validation operations.
 */
class InvoiceValidationService
{
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
     * Validate invoice status.
     */
    public function validateInvoiceStatus(string $status): bool
    {
        $allowedStatuses = ['paid', 'pending', 'overdue', 'cancelled'];
        return in_array($status, $allowedStatuses);
    }

    /**
     * Validate invoice amount.
     */
    public function validateInvoiceAmount(float $amount): bool
    {
        return $amount > 0 && $amount <= 999999.99;
    }

    /**
     * Validate invoice currency.
     */
    public function validateInvoiceCurrency(string $currency): bool
    {
        $allowedCurrencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];
        return in_array(strtoupper($currency), $allowedCurrencies);
    }

    /**
     * Validate invoice due date.
     */
    public function validateInvoiceDueDate(\DateTimeInterface $dueDate): bool
    {
        return $dueDate > now();
    }

    /**
     * Validate invoice number format.
     */
    public function validateInvoiceNumber(string $invoiceNumber): bool
    {
        return preg_match('/^INV-[A-Z0-9]{8}$/', $invoiceNumber);
    }

    /**
     * Check if invoice exists.
     */
    public function invoiceExists(int $invoiceId): bool
    {
        return Invoice::where('id', $invoiceId)->exists();
    }

    /**
     * Check if invoice belongs to user.
     */
    public function invoiceBelongsToUser(int $invoiceId, int $userId): bool
    {
        return Invoice::where('id', $invoiceId)
                     ->where('user_id', $userId)
                     ->exists();
    }

    /**
     * Check if invoice is paid.
     */
    public function isInvoicePaid(int $invoiceId): bool
    {
        return Invoice::where('id', $invoiceId)
                     ->where('status', 'paid')
                     ->exists();
    }

    /**
     * Check if invoice is overdue.
     */
    public function isInvoiceOverdue(int $invoiceId): bool
    {
        return Invoice::where('id', $invoiceId)
                     ->where(function ($query) {
                         $query->where('status', 'overdue')
                               ->orWhere(function ($q) {
                                   $q->where('status', 'pending')
                                     ->where('due_date', '<', now());
                               });
                     })
                     ->exists();
    }

    /**
     * Validate invoice creation data.
     */
    public function validateInvoiceCreationData(array $data): array
    {
        $errors = [];

        if (!isset($data['user_id']) || !$data['user_id']) {
            $errors['user_id'] = 'User ID is required';
        }

        if (!isset($data['license_id']) || !$data['license_id']) {
            $errors['license_id'] = 'License ID is required';
        }

        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'Valid amount is required';
        }

        if (!isset($data['currency']) || empty($data['currency'])) {
            $errors['currency'] = 'Currency is required';
        }

        if (!isset($data['status']) || !$this->validateInvoiceStatus($data['status'])) {
            $errors['status'] = 'Valid status is required';
        }

        return $errors;
    }

    /**
     * Validate invoice update data.
     */
    public function validateInvoiceUpdateData(array $data): array
    {
        $errors = [];

        if (isset($data['amount']) && (!$this->validateInvoiceAmount($data['amount']))) {
            $errors['amount'] = 'Invalid amount';
        }

        if (isset($data['currency']) && (!$this->validateInvoiceCurrency($data['currency']))) {
            $errors['currency'] = 'Invalid currency';
        }

        if (isset($data['status']) && (!$this->validateInvoiceStatus($data['status']))) {
            $errors['status'] = 'Invalid status';
        }

        if (isset($data['due_date']) && (!$this->validateInvoiceDueDate($data['due_date']))) {
            $errors['due_date'] = 'Invalid due date';
        }

        return $errors;
    }

    /**
     * Validate invoice payment.
     */
    public function validateInvoicePayment(int $invoiceId, float $amount): array
    {
        $errors = [];

        if (!$this->invoiceExists($invoiceId)) {
            $errors['invoice'] = 'Invoice not found';
            return $errors;
        }

        $invoice = Invoice::find($invoiceId);

        if ($this->isInvoicePaid($invoiceId)) {
            $errors['status'] = 'Invoice is already paid';
        }

        if ($amount !== $invoice->amount) {
            $errors['amount'] = 'Payment amount does not match invoice amount';
        }

        return $errors;
    }

    /**
     * Validate invoice cancellation.
     */
    public function validateInvoiceCancellation(int $invoiceId): array
    {
        $errors = [];

        if (!$this->invoiceExists($invoiceId)) {
            $errors['invoice'] = 'Invoice not found';
            return $errors;
        }

        if ($this->isInvoicePaid($invoiceId)) {
            $errors['status'] = 'Cannot cancel paid invoice';
        }

        return $errors;
    }
}
