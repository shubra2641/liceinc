<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Str;

/**
 * Invoice Operations Helper
 * 
 * Handles all invoice operations for InvoiceService.
 */
class InvoiceOperationsHelper
{
    /**
     * Generate unique invoice number with enhanced validation.
     */
    public function generateInvoiceNumber(): string
    {
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
    }

    /**
     * Create invoice with common fields.
     */
    public function createInvoiceRecord(array $data): Invoice
    {
        return Invoice::create(array_merge([
            'invoice_number' => $this->generateInvoiceNumber(),
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ], $data));
    }

    /**
     * Get invoice statistics.
     */
    public function getInvoiceStats(): array
    {
        $statuses = ['paid', 'pending', 'overdue', 'cancelled'];
        $stats = ['total_invoices' => Invoice::count()];
        
        foreach ($statuses as $status) {
            $stats["{$status}_invoices"] = Invoice::where('status', $status)->count();
            $stats["{$status}_revenue"] = (float)Invoice::where('status', $status)->sum('amount');
        }
        
        return $stats;
    }
}
